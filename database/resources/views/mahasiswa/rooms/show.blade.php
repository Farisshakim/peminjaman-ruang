@extends('layouts.app')

@section('title', $room->name . ' | SIPERU')

@section('content')
    <section class="card">
        <h1>{{ $room->name }}</h1>

        @if ($room->photo)
            <img
                class="photo-preview"
                src="{{ asset('storage/' . $room->photo) }}"
                alt="Foto {{ $room->name }}"
            >
        @endif

        <p>
            <strong>Lokasi:</strong> {{ $room->location }}<br>
            <strong>Kapasitas:</strong> {{ $room->capacity }} orang<br>
            <strong>Jam operasional:</strong>
            {{ substr($room->opening_time, 0, 5) }}
            –
            {{ substr($room->closing_time, 0, 5) }} WIB
        </p>

        <p>{{ $room->description }}</p>

        <h2>Fasilitas</h2>
        @forelse ($room->facilities as $facility)
            <div>
                {{ $facility->name }}: {{ $facility->pivot->quantity }}
            </div>
        @empty
            <p class="muted">Belum ada fasilitas yang dicatat.</p>
        @endforelse

        @if ($errors->any())
            <div class="alert error" role="alert" style="margin-top: 20px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <h2 style="margin-top: 28px;">Jadwal penggunaan</h2>

        <form
            class="filter-form"
            method="GET"
            action="{{ route('mahasiswa.rooms.show', $room) }}"
        >
            <input
                type="date"
                name="date"
                value="{{ $date }}"
                aria-label="Tanggal jadwal"
                required
            >
            <button class="btn" type="submit">Lihat Jadwal</button>
        </form>

        <p class="muted">
            Jadwal untuk tanggal {{ $date }}.
            Pengajuan yang masih menunggu belum ditampilkan sebagai jadwal terpakai.
        </p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Jam mulai</th>
                        <th>Jam selesai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td>{{ substr($schedule->start_time, 0, 5) }}</td>
                            <td>{{ substr($schedule->end_time, 0, 5) }}</td>
                            <td>{{ ucfirst($schedule->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                Belum ada jadwal penggunaan pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h2 style="margin-top: 28px;">Ajukan peminjaman</h2>

        <p class="muted">
            Pengajuan memerlukan persetujuan petugas.
            Seluruh waktu menggunakan WIB.
        </p>

        <form method="POST" action="{{ route('mahasiswa.bookings.store', $room) }}">
            @csrf

            <label for="activity_name">Nama kegiatan</label>
            <input
                id="activity_name"
                name="activity_name"
                value="{{ old('activity_name') }}"
                maxlength="150"
                required
            >

            <label for="purpose">Tujuan kegiatan</label>
            <textarea
                id="purpose"
                name="purpose"
                rows="3"
                maxlength="5000"
                required
            >{{ old('purpose') }}</textarea>

            <div class="form-grid">
                <div>
                    <label for="booking_date">Tanggal peminjaman</label>
                    <input
                        id="booking_date"
                        name="booking_date"
                        type="date"
                        value="{{ old('booking_date', $date) }}"
                        min="{{ now()->toDateString() }}"
                        required
                    >
                </div>

                <div>
                    <label for="participant_count">Jumlah peserta</label>
                    <input
                        id="participant_count"
                        name="participant_count"
                        type="number"
                        value="{{ old('participant_count') }}"
                        min="1"
                        max="{{ $room->capacity }}"
                        required
                    >
                </div>

                <div>
                    <label for="start_time">Jam mulai</label>
                    <input
                        id="start_time"
                        name="start_time"
                        type="time"
                        value="{{ old('start_time') }}"
                        required
                    >
                </div>

                <div>
                    <label for="end_time">Jam selesai</label>
                    <input
                        id="end_time"
                        name="end_time"
                        type="time"
                        value="{{ old('end_time') }}"
                        required
                    >
                </div>
            </div>

            <div class="form-actions">
                <button class="btn" type="submit">Kirim Pengajuan</button>
                <a class="btn btn-secondary" href="{{ route('mahasiswa.rooms.index') }}">
                    Kembali
                </a>
            </div>
        </form>
    </section>
@endsection