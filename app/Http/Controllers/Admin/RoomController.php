<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = $filters['search'] ?? '';

        $rooms = Room::with('facilities')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.rooms.index', compact('rooms', 'search'));
    }

    public function create()
    {
        return view('admin.rooms.form', [
            'room' => new Room(),
            'facilities' => Facility::orderBy('name')->get(),
            'quantities' => [],
        ]);
    }

    public function store(Request $request)
    {
        return $this->saveRoom($request, new Room());
    }

    public function edit(Room $room)
    {
        $room->load('facilities');

        $quantities = $room->facilities
            ->pluck('pivot.quantity', 'id')
            ->all();

        return view('admin.rooms.form', [
            'room' => $room,
            'facilities' => Facility::orderBy('name')->get(),
            'quantities' => $quantities,
        ]);
    }

    public function update(Request $request, Room $room)
    {
        return $this->saveRoom($request, $room);
    }

    private function saveRoom(Request $request, Room $room)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:150'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => [
                'required',
                'date_format:H:i',
                'after:opening_time',
            ],
            'is_active' => ['required', 'boolean'],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'facility_quantities' => ['sometimes', 'array'],
            'facility_quantities.*' => [
                'required',
                'integer',
                'min:0',
                'max:100000',
            ],
            'new_facility_name' => ['prohibited'],
            'new_facility_quantity' => ['prohibited'],
        ], [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute minimal :min.',
            'closing_time.after' => 'Jam tutup harus setelah jam buka.',
            'photo.image' => 'Foto harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ], [
            'name' => 'Nama ruang',
            'location' => 'Lokasi',
            'capacity' => 'Kapasitas',
            'opening_time' => 'Jam buka',
            'closing_time' => 'Jam tutup',
            'is_active' => 'Status ruang',
            'facility_quantities.*' => 'Jumlah fasilitas',
            'new_facility_name' => 'Nama fasilitas baru',
            'new_facility_quantity' => 'Jumlah fasilitas baru',
        ]);

        $quantities = $data['facility_quantities'] ?? [];
        $validIds = Facility::pluck('id')->map(
            fn ($id) => (string) $id
        )->all();

        // Periksa ID fasilitas yang dikirim dari formulir.
        foreach (array_keys($quantities) as $id) {
            if (! in_array((string) $id, $validIds, true)) {
                throw ValidationException::withMessages([
                    'facility_quantities' => 'Ada fasilitas yang tidak valid. Muat ulang halaman.',
                ]);
            }
        }

        $pivotData = [];

        foreach ($quantities as $id => $quantity) {
            if ((int) $quantity > 0) {
                $pivotData[$id] = ['quantity' => (int) $quantity];
            }
        }


        unset(
            $data['facility_quantities'],
            $data['photo'],
            $data['new_facility_name'],
            $data['new_facility_quantity']
        );
        $data['is_active'] = $request->boolean('is_active');

        $isNew = ! $room->exists;
        $newPhoto = null;
        $oldPhoto = null;

        try {
            if ($request->hasFile('photo')) {
                $newPhoto = $request->file('photo')
                    ->store('rooms', 'public');

                if (! $newPhoto) {
                    throw ValidationException::withMessages([
                        'photo' => 'Foto gagal disimpan. Silakan coba lagi.',
                    ]);
                }

                $data['photo'] = $newPhoto;
            }

            DB::transaction(function () use (
                $room,
                $isNew,
                $data,
                $pivotData,
                &$oldPhoto
            ) {
                // Mengunci ruang selama pembaruan data.
                $target = $isNew
                    ? $room
                    : Room::whereKey($room->id)->lockForUpdate()->firstOrFail();

                $oldPhoto = $target->photo;

if (! $isNew) {
    $now = now();
    $today = $now->toDateString();
    $currentTime = $now->format('H:i:s');

    // Hanya peminjaman disetujui yang belum berakhir.
    // Termasuk peminjaman yang sedang berlangsung.
    $approvedBookings = Booking::where('room_id', $target->id)
        ->where('status', 'disetujui')
        ->where(function ($query) use ($today, $currentTime) {
            $query->where('booking_date', '>', $today)
                ->orWhere(function ($query) use ($today, $currentTime) {
                    $query->where('booking_date', $today)
                        ->where('end_time', '>', $currentTime);
                });
        })
        ->lockForUpdate()
        ->get();

    if ($approvedBookings->isNotEmpty()) {
        if (! $data['is_active']) {
            throw ValidationException::withMessages([
                'is_active' =>
                    'Ruang tidak dapat dinonaktifkan karena masih memiliki '
                    .'peminjaman disetujui yang belum berakhir.',
            ]);
        }

        $newOpeningTime = $data['opening_time'].':00';
        $newClosingTime = $data['closing_time'].':00';

        foreach ($approvedBookings as $booking) {
            $reference = $booking->booking_code
                .' tanggal '
                .$booking->booking_date->format('d-m-Y');

            if ((int) $data['capacity'] < $booking->participant_count) {
                throw ValidationException::withMessages([
                    'capacity' =>
                        'Kapasitas tidak boleh kurang dari '
                        .$booking->participant_count
                        .' orang karena ada peminjaman '
                        .$reference.'.',
                ]);
            }

            if ($newOpeningTime > $booking->start_time) {
                throw ValidationException::withMessages([
                    'opening_time' =>
                        'Jam buka tidak boleh melewati '
                        .substr($booking->start_time, 0, 5)
                        .' WIB karena ada peminjaman '
                        .$reference.'.',
                ]);
            }

            if ($newClosingTime < $booking->end_time) {
                throw ValidationException::withMessages([
                    'closing_time' =>
                        'Jam tutup tidak boleh sebelum '
                        .substr($booking->end_time, 0, 5)
                        .' WIB karena ada peminjaman '
                        .$reference.'.',
                ]);
            }
        }
    }
}

$lockedFacilities = Facility::orderBy('id')
    ->lockForUpdate()
    ->get()
    ->keyBy('id');

$allocations = DB::table('room_facilities')
    ->orderBy('facility_id')
    ->orderBy('room_id')
    ->lockForUpdate()
    ->get();

foreach ($pivotData as $facilityId => $pivot) {
    $facility = $lockedFacilities->get($facilityId);

    if (! $facility) {
        throw ValidationException::withMessages([
            'facility_quantities' =>
                'Fasilitas sudah tidak tersedia. Muat ulang halaman.',
        ]);
    }

    // Alokasi ruang yang sedang diedit tidak dihitung sebagai
    // pemakaian ruang lain, karena akan diganti oleh nilai baru.
    $usedElsewhere = $allocations
        ->where('facility_id', $facilityId)
        ->filter(function ($allocation) use ($target) {
            return ! $target->exists
                || (int) $allocation->room_id !== (int) $target->id;
        })
        ->sum('quantity');

    $maximum = (int) $facility->total_stock - $usedElsewhere;

    if ((int) $pivot['quantity'] > $maximum) {
        throw ValidationException::withMessages([
            'facility_quantities.'.$facilityId =>
                "Stok {$facility->name} tidak cukup. "
                ."Maksimal untuk ruang ini adalah {$maximum} unit.",
        ]);
    }
}

$target->fill($data);
$target->save();
$target->facilities()->sync($pivotData);
            });
        } catch (Throwable $exception) {
            // Hapus foto baru jika penyimpanan database gagal.
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        // Foto lama dihapus setelah perubahan berhasil tersimpan.
        if ($newPhoto && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()
            ->route('admin.rooms.index')
            ->with(
                'success',
                $isNew
                    ? 'Ruang berhasil ditambahkan.'
                    : 'Ruang berhasil diperbarui.'
            );
    }
}