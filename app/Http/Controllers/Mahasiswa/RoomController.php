<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $search = $filters['search'] ?? '';
        $capacity = $filters['capacity'] ?? null;

        $rooms = Room::with('facilities')
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($capacity, function ($query) use ($capacity) {
                $query->where('capacity', '>=', $capacity);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view(
            'mahasiswa.rooms.index',
            compact('rooms', 'search', 'capacity')
        );
    }

    public function show(Request $request, Room $room)
    {
        abort_unless($room->is_active, 404);

        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $date = $data['date'] ?? now()->toDateString();

        $room->load('facilities');

        // Jadwal umum tidak menampilkan identitas peminjam.
        $schedules = $room->bookings()
            ->whereDate('booking_date', $date)
            ->whereIn('status', ['disetujui', 'selesai'])
            ->orderBy('start_time')
            ->get(['start_time', 'end_time', 'status']);

        return view(
            'mahasiswa.rooms.show',
            compact('room', 'date', 'schedules')
        );
    }
}