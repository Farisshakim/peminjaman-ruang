@extends('layouts.app')

@section('title', 'Detail Pengajuan | SIPERU')

@section('content')
    <section class="card">
        <h1>Detail Pengajuan</h1>

        @if ($errors->any())
            <div class="alert error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <span class="badge">{{ ucfirst($booking->status) }}</span>

        <p style="overflow-wrap: anywhere;">
            <strong>Kode:</strong> {{ $booking->booking_code }}<br>
            <strong>Kegiatan:</strong> {{ $booking->activity_name }}<br>
            <strong>Ruang:</strong> {{ $booking->room->name }}<br>
            <strong>Tanggal:</strong>
            {{ $booking->booking_date->format('d-m-Y') }}<br>
            <strong>Waktu:</strong>
            {{ substr($booking->start_time, 0, 5) }}
            –
            {{ substr($booking->end_time, 0, 5) }} WIB<br>
            <strong>Peserta:</strong> {{ $booking->participant_count }} orang
        </p>

        <h2>Tujuan kegiatan</h2>
        <p style="white-space: pre-wrap;">{{ $booking->purpose }}</p>

        <h2>Riwayat status</h2>

        @php
        $startAt = \Illuminate\Support\Carbon::parse(
            $booking->booking_date->format('Y-m-d')
            .' '.$booking->start_time
        );

        $canCancel = in_array(
            $booking->status,
            ['menunggu', 'disetujui'],
            true
        ) && $startAt->isFuture();
    @endphp

    @if ($canCancel)
        <div style="margin: 24px 0;">
            <h2>Batalkan Peminjaman</h2>

            <p class="muted">
                Pembatalan tidak dapat dibatalkan kembali.
                Jika masih membutuhkan ruang, buat pengajuan baru.
            </p>

            <form method="POST"
                action="{{ route('mahasiswa.bookings.cancel', $booking->id) }}"
                onsubmit="return confirm('Yakin ingin membatalkan peminjaman ini?');">
                @csrf
                @method('PATCH')

                <label for="reason">Alasan pembatalan</label>

                <textarea
                    id="reason"
                    name="reason"
                    rows="3"
                    maxlength="2000"
                    required
                >{{ old('reason') }}</textarea>

                <div class="form-actions">
                    <button type="submit" class="btn btn-danger">
                        Batalkan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    @endif

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pelaku</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($booking->histories as $history)
                        <tr>
                            <td>{{ $history->created_at->format('d-m-Y H:i') }}</td>
                            <td>{{ $history->changedBy->name }}</td>
                            <td>{{ ucfirst($history->new_status) }}</td>
                            <td>{{ $history->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Belum ada riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('mahasiswa.bookings.index') }}">
                Kembali ke Peminjaman Saya
            </a>
        </div>
    </section>
@endsection