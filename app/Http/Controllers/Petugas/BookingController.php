<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $statuses = [
            'menunggu',
            'disetujui',
            'ditolak',
            'dibatalkan',
            'selesai',
        ];

        $data = $request->validate([
            'status' => ['nullable', Rule::in($statuses)],
        ]);

        $status = $data['status'] ?? '';

        $bookings = Booking::with(['user', 'room'])
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('petugas.bookings.index', compact(
            'bookings',
            'statuses',
            'status'
        ));
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'user',
            'room',
            'histories' => fn ($query) => $query->orderBy('id'),
            'histories.changedBy',
        ]);

        return view('petugas.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'action' => [
                'required',
                Rule::in(['approve', 'reject', 'complete']),
            ],
            'notes' => [
                'required_if:action,reject',
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'notes.required_if' => 'Alasan penolakan wajib diisi.',
        ]);

        DB::transaction(function () use ($request, $booking, $data) {
            // Selalu kunci ruang terlebih dahulu agar persetujuan
            // pada ruang yang sama diproses secara bergantian.
            $room = Room::whereKey($booking->room_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Baca ulang status terbaru setelah memperoleh kunci.
            $current = Booking::whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousStatus = $current->status;
            $date = $current->booking_date->format('Y-m-d');

            if ($data['action'] === 'approve') {
                if ($current->status !== 'menunggu') {
                    $this->fail('Hanya pengajuan menunggu yang bisa disetujui.');
                }

                if (! $room->is_active) {
                    $this->fail('Ruang sudah dinonaktifkan.');
                }

                $start = Carbon::parse($date.' '.$current->start_time);

                if ($start->lessThanOrEqualTo(now())) {
                    $this->fail(
                        'Waktu mulai sudah lewat. Pengajuan tidak dapat disetujui.'
                    );
                }

                if ($current->participant_count > $room->capacity) {
                    $this->fail('Jumlah peserta melebihi kapasitas ruang saat ini.');
                }

                if (
                    $current->start_time < $room->opening_time
                    || $current->end_time > $room->closing_time
                ) {
                    $this->fail(
                        'Jadwal berada di luar jam operasional ruang saat ini.'
                    );
                }

                // Locking read mengambil data terbaru, termasuk persetujuan
                // yang baru selesai diproses oleh petugas lain.
                $conflict = Booking::where('room_id', $room->id)
                    ->whereDate('booking_date', $date)
                    ->where('status', 'disetujui')
                    ->where('id', '!=', $current->id)
                    ->where('start_time', '<', $current->end_time)
                    ->where('end_time', '>', $current->start_time)
                    ->lockForUpdate()
                    ->first();

                if ($conflict) {
                    $this->fail(
                        'Jadwal bertabrakan dengan peminjaman yang sudah disetujui.'
                    );
                }

                $newStatus = 'disetujui';
                $notes = $data['notes'] ?? 'Pengajuan disetujui oleh petugas.';
            } elseif ($data['action'] === 'reject') {
                if ($current->status !== 'menunggu') {
                    $this->fail('Hanya pengajuan menunggu yang bisa ditolak.');
                }

                $newStatus = 'ditolak';
                $notes = $data['notes'];
            } else {
                if ($current->status !== 'disetujui') {
                    $this->fail(
                        'Hanya peminjaman yang disetujui yang bisa diselesaikan.'
                    );
                }

                $end = Carbon::parse($date.' '.$current->end_time);

                if ($end->isFuture()) {
                    $this->fail(
                        'Peminjaman baru dapat diselesaikan setelah jam berakhir.'
                    );
                }

                $newStatus = 'selesai';
                $notes = $data['notes'] ?? 'Peminjaman selesai.';
            }

            $current->status = $newStatus;
            $current->save();

            $current->histories()->create([
                'changed_by' => $request->user()->id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'notes' => $notes,
            ]);
        }, 3);

        return redirect()
            ->route('petugas.bookings.show', $booking)
            ->with('success', 'Status peminjaman berhasil diperbarui.');
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'booking' => $message,
        ]);
    }
}