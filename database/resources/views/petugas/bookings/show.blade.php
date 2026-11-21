@extends('layouts.app')

@section('title', 'Detail Peminjaman | SIPERU')

@section('content')
    <section class="card">
        <h1>Detail Peminjaman</h1>
        <p class="muted">{{ $booking->booking_code }}</p>

        @if ($errors->any())
            <div class="alert error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p><strong>Mahasiswa:</strong> {{ $booking->user->name }}</p>
        <p><strong>NIM:</strong> {{ $booking->user->nim }}</p>
        <p><strong>Email:</strong> {{ $booking->user->email }}</p>
        <p><strong>Kegiatan:</strong> {{ $booking->activity_name }}</p>
        <p><strong>Ruang:</strong> {{ $booking->room->name }}</p>
        <p><strong>Lokasi:</strong> {{ $booking->room->location }}</p>

        <p>
            <strong>Tanggal:</strong>
            {{ $booking->booking_date->format('d-m-Y') }}
        </p>

        <p>
            <strong>Waktu:</strong>
            {{ substr($booking->start_time, 0, 5) }}
            –
            {{ substr($booking->end_time, 0, 5) }} WIB
        </p>

        <p>
            <strong>Jumlah peserta:</strong>
            {{ $booking->participant_count }} orang
        </p>

        <p><strong>Status:</strong> {{ ucfirst($booking->status) }}</p>

        <p><strong>Tujuan kegiatan:</strong></p>
        <p style="white-space: pre-wrap;">{{ $booking->purpose }}</p>

        @if ($booking->status === 'menunggu')
            <hr>
            <h2>Keputusan Petugas</h2>

            <form method="POST"
                  action="{{ route('petugas.bookings.status', $booking) }}">
                @csrf
                @method('PATCH')

                <label for="notes">Catatan / alasan penolakan</label>

                <textarea id="notes"
                          name="notes"
                          rows="3"
                          maxlength="2000">{{ old('notes') }}</textarea>

                <p class="muted">
                    Alasan wajib diisi apabila pengajuan ditolak.
                </p>

                <div class="form-actions">
                    <button type="submit"
                            class="btn"
                            name="action"
                            value="approve">
                        Setujui
                    </button>

                    <button type="submit"
                            class="btn btn-danger"
                            name="action"
                            value="reject">
                        Tolak
                    </button>
                </div>
            </form>
        @elseif ($booking->status === 'disetujui')
            <hr>
            <h2>Penyelesaian Peminjaman</h2>

            <p class="muted">
                Peminjaman dapat diselesaikan setelah waktu penggunaan berakhir.
            </p>

            <form method="POST"
                  action="{{ route('petugas.bookings.status', $booking) }}">
                @csrf
                @method('PATCH')

                <input type="hidden" name="action" value="complete">

                <label for="notes">Catatan penyelesaian (opsional)</label>

                <textarea id="notes"
                          name="notes"
                          rows="3"
                          maxlength="2000">{{ old('notes') }}</textarea>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        Tandai Selesai
                    </button>
                </div>
            </form>
        @endif
    </section>

    <section class="card" style="margin-top: 24px;">
        <h2>Riwayat Peminjaman</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Waktu WIB</th>
                        <th>Pengguna</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($booking->histories as $history)
                        <tr>
                            <td>
                                {{ $history->created_at->format('d-m-Y H:i') }}
                            </td>
                            <td>{{ $history->changedBy?->name ?? '-' }}</td>
                            <td>{{ ucfirst($history->new_status) }}</td>
                            <td style="white-space: pre-wrap;">{{ $history->notes ?? '-' }}</td>
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
            <a class="btn btn-secondary"
               href="{{ route('petugas.bookings.index') }}">
                Kembali
            </a>
        </div>
    </section>
@endsection