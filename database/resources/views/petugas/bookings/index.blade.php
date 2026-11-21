@extends('layouts.app')

@section('title', 'Kelola Peminjaman | SIPERU')

@section('content')
    <section class="card">
        <div class="toolbar">
            <h1>Kelola Peminjaman</h1>

            <a class="btn btn-secondary"
               href="{{ route('petugas.dashboard') }}">
                Dashboard
            </a>
        </div>

        <form method="GET"
              action="{{ route('petugas.bookings.index') }}"
              class="filter-form">
            <div>
                <label for="status">Status peminjaman</label>

                <select name="status" id="status">
                    <option value="">Semua status</option>

                    @foreach ($statuses as $option)
                        <option value="{{ $option }}"
                                @selected($status === $option)>
                            {{ ucfirst($option) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn">Tampilkan</button>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Kegiatan</th>
                        <th>Ruang</th>
                        <th>Tanggal</th>
                        <th>Waktu WIB</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>
                                {{ $booking->user->name }}<br>
                                <span class="muted">
                                    {{ $booking->user->nim }}
                                </span>
                            </td>
                            <td>{{ $booking->activity_name }}</td>
                            <td>{{ $booking->room->name }}</td>
                            <td>
                                {{ $booking->booking_date->format('d-m-Y') }}
                            </td>
                            <td>
                                {{ substr($booking->start_time, 0, 5) }}
                                –
                                {{ substr($booking->end_time, 0, 5) }}
                            </td>
                            <td>{{ ucfirst($booking->status) }}</td>
                            <td>
                                <a class="btn"
                                   href="{{ route('petugas.bookings.show', $booking) }}">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Belum ada pengajuan yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['items' => $bookings])
    </section>
@endsection
