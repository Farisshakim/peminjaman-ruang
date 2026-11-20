<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless(
            in_array($user->role, ['admin', 'petugas', 'mahasiswa'], true),
            403
        );

        // Statistik mahasiswa hanya berasal dari pengajuan miliknya.
        $query = Booking::query();

        if ($user->role === 'mahasiswa') {
            $query->where('user_id', $user->id);
        }

        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        if ($user->role === 'admin') {
            $title = 'Dashboard Admin';
            $description = 'Kelola akun, ruang, fasilitas, dan laporan.';

            $stats = [
                'Total Ruang' => Room::count(),
                'Ruang Aktif' => Room::where('is_active', true)->count(),
                'Mahasiswa Aktif' => User::where('role', 'mahasiswa')
                    ->where('is_active', true)
                    ->count(),
                'Seluruh Pengajuan' => $counts->sum(),
            ];
        } elseif ($user->role === 'petugas') {
            $title = 'Dashboard Petugas';
            $description = 'Periksa pengajuan dan pantau penggunaan ruang.';

            $stats = [
                'Menunggu Keputusan' => $counts->get('menunggu', 0),
                'Disetujui' => $counts->get('disetujui', 0),
                'Penggunaan Hari Ini' => Booking::whereDate(
                    'booking_date',
                    today()->toDateString()
                )
                    ->whereIn('status', ['disetujui', 'selesai'])
                    ->count(),
                'Selesai' => $counts->get('selesai', 0),
            ];
        } else {
            $title = 'Dashboard Mahasiswa';
            $description = 'Cari ruang dan pantau pengajuan peminjamanmu.';

            $stats = [
                'Menunggu' => $counts->get('menunggu', 0),
                'Disetujui' => $counts->get('disetujui', 0),
                'Dibatalkan' => $counts->get('dibatalkan', 0),
                'Selesai' => $counts->get('selesai', 0),
            ];
        }

        $recentBookings = (clone $query)
            ->with(['room', 'user'])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'title',
            'description',
            'stats',
            'recentBookings'
        ));
    }
}