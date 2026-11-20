<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderReport($request, false);
    }

    public function print(Request $request)
    {
        return $this->renderReport($request, true);
    }

    private function renderReport(Request $request, bool $printing)
    {
        $statuses = [
            'menunggu',
            'disetujui',
            'ditolak',
            'dibatalkan',
            'selesai',
        ];

        // Default periode: bulan berjalan.
        $filters = array_merge([
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'room_id' => '',
            'status' => '',
        ], $request->only([
            'start_date',
            'end_date',
            'room_id',
            'status',
        ]));

        $filters = validator($filters, [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'status' => ['nullable', Rule::in($statuses)],
        ], [
            'start_date.required' => 'Tanggal awal wajib diisi.',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.after_or_equal' =>
                'Tanggal akhir harus sama atau setelah tanggal awal.',
        ])->validate();

        $query = Booking::query()
            ->whereBetween('booking_date', [
                $filters['start_date'],
                $filters['end_date'],
            ])
            ->when($filters['room_id'] ?? null, function ($query, $roomId) {
                $query->where('room_id', $roomId);
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            });

        // Ringkasan dihitung dari seluruh hasil filter,
        // bukan hanya data pada halaman yang sedang dibuka.
        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = $counts->sum();

        $query->with(['user', 'room'])
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->orderBy('id');

        $bookings = $printing
            ? $query->get()
            : $query->paginate(15)->appends($filters);

        // Ruang nonaktif tetap tersedia untuk laporan historis.
        $rooms = Room::orderBy('name')->get();

        return view('admin.reports.index', compact(
            'bookings',
            'rooms',
            'statuses',
            'filters',
            'counts',
            'total',
            'printing'
        ));
    }
}