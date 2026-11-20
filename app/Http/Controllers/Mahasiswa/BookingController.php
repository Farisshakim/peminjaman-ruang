<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with('room')
            ->latest('id')
            ->paginate(10);

        return view('mahasiswa.bookings.index', compact('bookings'));
    }

    public function show(Request $request, int $booking)
    {
        // Cari melalui relasi pengguna agar tidak bisa membuka
        // pengajuan milik mahasiswa lain.
        $booking = $request->user()
            ->bookings()
            ->with([
                'room',
                'histories' => fn ($query) => $query->orderBy('id'),
                'histories.changedBy',
            ])
            ->findOrFail($booking);

        return view('mahasiswa.bookings.show', compact('booking'));
    }

    public function store(Request $request, Room $room)
    {
        $data = $request->validate([
            'activity_name' => ['required', 'string', 'max:150'],
            'purpose' => ['required', 'string', 'max:5000'],
            'booking_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'participant_count' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],
        ], [
            'required' => ':attribute wajib diisi.',
            'booking_date.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'participant_count.min' => 'Jumlah peserta minimal 1 orang.',
        ], [
            'activity_name' => 'Nama kegiatan',
            'purpose' => 'Tujuan kegiatan',
            'booking_date' => 'Tanggal',
            'start_time' => 'Jam mulai',
            'end_time' => 'Jam selesai',
            'participant_count' => 'Jumlah peserta',
        ]);

        $booking = DB::transaction(function () use ($request, $room, $data) {
            // Ambil data terbaru dan kunci ruang selama penyimpanan.
            $lockedRoom = Room::whereKey($room->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRoom->is_active) {
                throw ValidationException::withMessages([
                    'room' => 'Ruang sudah dinonaktifkan. Pilih ruang lain.',
                ]);
            }

            $start = Carbon::createFromFormat(
                'Y-m-d H:i',
                $data['booking_date'].' '.$data['start_time']
            )->startOfMinute();

            if ($start->lessThanOrEqualTo(now())) {
                throw ValidationException::withMessages([
                    'start_time' => 'Waktu mulai harus berada di masa depan.',
                ]);
            }

            if ($data['participant_count'] > $lockedRoom->capacity) {
                throw ValidationException::withMessages([
                    'participant_count' => 'Jumlah peserta melebihi kapasitas ruang.',
                ]);
            }

            $startTime = $data['start_time'].':00';
            $endTime = $data['end_time'].':00';

            if (
                $startTime < $lockedRoom->opening_time
                || $endTime > $lockedRoom->closing_time
            ) {
                throw ValidationException::withMessages([
                    'start_time' => 'Waktu kegiatan harus berada dalam jam operasional ruang.',
                ]);
            }

            $conflict = Booking::where('room_id', $lockedRoom->id)
                ->whereDate('booking_date', $data['booking_date'])
                ->where('status', 'disetujui')
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'start_time' => 'Jadwal bertabrakan dengan peminjaman yang sudah disetujui.',
                ]);
            }

            $booking = new Booking();
            $booking->fill($data);
            $booking->booking_code = 'SPR-'.Str::ulid();
            $booking->user_id = $request->user()->id;
            $booking->room_id = $lockedRoom->id;
            $booking->status = 'menunggu';
            $booking->save();

            $booking->histories()->create([
                'changed_by' => $request->user()->id,
                'previous_status' => null,
                'new_status' => 'menunggu',
                'notes' => 'Pengajuan dibuat oleh mahasiswa.',
            ]);

            return $booking;
        });

        return redirect()
            ->route('mahasiswa.bookings.show', $booking->id)
            ->with('success', 'Pengajuan berhasil dikirim. Tunggu keputusan petugas.');
    }

    public function cancel(Request $request, int $booking)
{
    // Cari lewat relasi pengguna agar hanya pemilik yang bisa mengakses.
    $ownedBooking = $request->user()
        ->bookings()
        ->findOrFail($booking);

    $data = $request->validate([
        'reason' => ['required', 'string', 'max:2000'],
    ], [
        'reason.required' => 'Alasan pembatalan wajib diisi.',
        'reason.max' => 'Alasan pembatalan maksimal 2000 karakter.',
    ]);

    DB::transaction(function () use ($request, $ownedBooking, $data) {
        // Urutan penguncian sama dengan proses persetujuan petugas:
        // ruang terlebih dahulu, lalu peminjaman.
        Room::whereKey($ownedBooking->room_id)
            ->lockForUpdate()
            ->firstOrFail();

        $current = Booking::whereKey($ownedBooking->id)
            ->where('user_id', $request->user()->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (! in_array($current->status, ['menunggu', 'disetujui'], true)) {
            throw ValidationException::withMessages([
                'booking' => 'Peminjaman dengan status ini tidak dapat dibatalkan.',
            ]);
        }

        $start = Carbon::parse(
            $current->booking_date->format('Y-m-d')
            .' '.$current->start_time
        );

        if ($start->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages([
                'booking' => 'Pembatalan hanya dapat dilakukan sebelum waktu mulai.',
            ]);
        }

        $previousStatus = $current->status;

        $current->status = 'dibatalkan';
        $current->save();

        $current->histories()->create([
            'changed_by' => $request->user()->id,
            'previous_status' => $previousStatus,
            'new_status' => 'dibatalkan',
            'notes' => 'Dibatalkan oleh mahasiswa. Alasan: '.$data['reason'],
        ]);
    }, 3);

    return redirect()
        ->route('mahasiswa.bookings.show', $ownedBooking->id)
        ->with('success', 'Peminjaman berhasil dibatalkan.');
}
}