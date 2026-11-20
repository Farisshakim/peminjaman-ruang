@extends('layouts.app')

@section('title', 'Daftar Ruang | SIPERU')

@section('content')
    <section class="card">
        <div class="toolbar">
            <h1>Daftar Ruang</h1>
            <a class="btn btn-secondary" href="{{ route('mahasiswa.bookings.index') }}">
                Peminjaman Saya
            </a>
        </div>

        @if ($errors->any())
            <div class="alert error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form class="filter-form" method="GET" action="{{ route('mahasiswa.rooms.index') }}">
            <input
                name="search"
                value="{{ $search }}"
                placeholder="Nama ruang"
                aria-label="Nama ruang"
                maxlength="100"
            >

            <input
                name="capacity"
                type="number"
                value="{{ $capacity }}"
                min="1"
                max="100000"
                placeholder="Jumlah peserta"
                aria-label="Jumlah peserta"
            >

            <button class="btn" type="submit">Cari</button>
            <a class="btn btn-secondary" href="{{ route('mahasiswa.rooms.index') }}">
                Reset
            </a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Ruang</th>
                        <th>Kapasitas</th>
                        <th>Jam operasional</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                        <tr>
                            <td>
                                @if ($room->photo)
                                    <img
                                        class="room-photo"
                                        src="{{ asset('storage/' . $room->photo) }}"
                                        alt="Foto {{ $room->name }}"
                                    >
                                    <br>
                                @endif
                                <strong>{{ $room->name }}</strong><br>
                                {{ $room->location }}
                            </td>
                            <td>{{ $room->capacity }} orang</td>
                            <td>
                                {{ substr($room->opening_time, 0, 5) }}
                                –
                                {{ substr($room->closing_time, 0, 5) }}
                            </td>
                            <td>
                                <a class="btn" href="{{ route('mahasiswa.rooms.show', $room) }}">
                                    Detail & Pengajuan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Tidak ada ruang yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['items' => $rooms])
    </section>
@endsection