@extends('layouts.app')

@section('title', 'Peminjaman Saya | SIPERU')

@section('content')
    <section class="card">
        <div class="toolbar">
            <h1>Peminjaman Saya</h1>
            <a class="btn" href="{{ route('mahasiswa.rooms.index') }}">
                Cari Ruang
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kegiatan</th>
                        <th>Ruang</th>
                        <th>Tanggal & waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>{{ $booking->activity_name }}</td>
                            <td>{{ $booking->room->name }}</td>
                            <td>
                                {{ $booking->booking_date->format('d-m-Y') }}<br>
                                {{ substr($booking->start_time, 0, 5) }}
                                –
                                {{ substr($booking->end_time, 0, 5) }}
                            </td>
                            <td>
                                <span class="badge">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>
                                <a
                                    class="btn btn-secondary"
                                    href="{{ route('mahasiswa.bookings.show', $booking->id) }}"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Kamu belum memiliki pengajuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['items' => $bookings])
    </section>
@endsection
