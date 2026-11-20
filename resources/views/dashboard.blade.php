@extends('layouts.app')

@section('title', $title . ' | SIPERU')

@section('content')
    <section class="card">
        <span class="badge">
            {{ ucfirst(auth()->user()->role) }}
        </span>

        <h1 style="margin-top: 16px;">{{ $title }}</h1>

        <p>
            Selamat datang, <strong>{{ auth()->user()->name }}</strong>.
        </p>

        <p class="muted">{{ $description }}</p>

        <div class="info">
            <div>
                <strong>Email</strong><br>
                {{ auth()->user()->email }}
            </div>

            <div>
                <strong>Status akun</strong><br>
                Aktif
            </div>

            @if (auth()->user()->role === 'mahasiswa')
                <div>
                    <strong>NIM</strong><br>
                    {{ auth()->user()->nim }}
                </div>
            @endif
        </div>
    </section>

        @if (auth()->user()->role === 'admin')
            <div class="actions" style="margin-top: 28px;">
                <a class="btn" href="{{ route('admin.rooms.index') }}">
                    Kelola Ruang
                </a>

                <a class="btn" href="{{ route('admin.facilities.index') }}">
                    Kelola Fasilitas
                </a>

                <a class="btn" href="{{ route('admin.users.index') }}">
                    Kelola Akun
                </a>
                <a class="btn" href="{{ route('admin.reports.index') }}">
                    Laporan Peminjaman
                </a>
            </div>
        @endif

        @if (auth()->user()->role === 'mahasiswa')
            <div class="actions" style="margin-top: 28px;">
                <a class="btn" href="{{ route('mahasiswa.rooms.index') }}">
                    Cari Ruang
                </a>

                <a class="btn" href="{{ route('mahasiswa.bookings.index') }}">
                    Peminjaman Saya
                </a>
            </div>
        @endif

        @if (auth()->user()->role === 'petugas')
            <div class="actions" style="margin-top: 28px;">
                <a class="btn" href="{{ route('petugas.bookings.index') }}">
                    Kelola Peminjaman
                </a>
            </div>
        @endif

        <section class="card" style="margin-top: 24px;">
        <h2>Ringkasan</h2>

        <div style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
        ">
            @foreach ($stats as $label => $value)
                <div style="
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                ">
                    <div class="muted">{{ $label }}</div>

                    <strong style="display: block; font-size: 30px; margin-top: 8px;">
                        {{ number_format($value, 0, ',', '.') }}
                    </strong>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card" style="margin-top: 24px;">
        <h2>Lima Pengajuan Terbaru</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        @if (auth()->user()->role !== 'mahasiswa')
                            <th>Mahasiswa</th>
                        @endif

                        <th>Kegiatan</th>
                        <th>Ruang</th>
                        <th>Tanggal Penggunaan</th>
                        <th>Status</th>

                        @if (auth()->user()->role !== 'admin')
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse ($recentBookings as $booking)
                        <tr>
                            @if (auth()->user()->role !== 'mahasiswa')
                                <td>{{ $booking->user->name }}</td>
                            @endif

                            <td>{{ $booking->activity_name }}</td>
                            <td>{{ $booking->room->name }}</td>
                            <td>
                                {{ $booking->booking_date->format('d-m-Y') }}
                            </td>
                            <td>{{ ucfirst($booking->status) }}</td>

                            @if (auth()->user()->role === 'mahasiswa')
                                <td>
                                    <a class="btn"
                                    href="{{ route('mahasiswa.bookings.show', $booking->id) }}">
                                        Detail
                                    </a>
                                </td>
                            @elseif (auth()->user()->role === 'petugas')
                                <td>
                                    <a class="btn"
                                    href="{{ route('petugas.bookings.show', $booking->id) }}">
                                        Detail
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'petugas' ? 6 : 5 }}">
                                Belum ada pengajuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
