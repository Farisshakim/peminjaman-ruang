@extends('layouts.app')

@section('title', 'Laporan Peminjaman | SIPERU')

@section('content')
    <style>
        .report-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px 0;
        }

        .report-summary > div {
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .report-summary strong {
            display: block;
            font-size: 22px;
        }

        .report-filters {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin: 20px 0;
        }

        @media (max-width: 640px) {
            .report-filters {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm;
            }

            header,
            .no-print,
            .alert {
                display: none !important;
            }

            body {
                background: white !important;
                color: black !important;
                font-size: 11px;
            }

            main {
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }

            .table-wrapper {
                overflow: visible !important;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #aaa;
                padding: 6px !important;
                color: black !important;
            }

            thead {
                display: table-header-group;
            }

            tr {
                break-inside: avoid;
            }
        }
    </style>

    <section class="card">
        <h1>Laporan Peminjaman Ruang</h1>
        <p>SIPERU — Sistem Peminjaman Ruang Kampus</p>

        @if ($errors->any())
            <div class="alert error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! $printing)
            <form method="GET"
                  action="{{ route('admin.reports.index') }}"
                  class="no-print">

                <div class="report-filters">
                    <div>
                        <label for="start_date">Tanggal penggunaan mulai</label>
                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            value="{{ old('start_date', $filters['start_date']) }}"
                            required
                        >
                    </div>

                    <div>
                        <label for="end_date">Tanggal penggunaan sampai</label>
                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            value="{{ old('end_date', $filters['end_date']) }}"
                            required
                        >
                    </div>

                    <div>
                        <label for="room_id">Ruang</label>
                        <select id="room_id" name="room_id">
                            <option value="">Semua ruang</option>

                            @foreach ($rooms as $room)
                                <option
                                    value="{{ $room->id }}"
                                    @selected(
                                        (string) old('room_id', $filters['room_id'])
                                        === (string) $room->id
                                    )
                                >
                                    {{ $room->name }}
                                    {{ $room->is_active ? '' : '(Nonaktif)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">Semua status</option>

                            @foreach ($statuses as $status)
                                <option
                                    value="{{ $status }}"
                                    @selected(
                                        old('status', $filters['status']) === $status
                                    )
                                >
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">
                        Tampilkan Laporan
                    </button>

                    <a class="btn btn-secondary"
                       href="{{ route('admin.reports.index') }}">
                        Reset
                    </a>
                </div>
            </form>
        @endif

        <p>
            <strong>Periode penggunaan:</strong>
            {{ \Illuminate\Support\Carbon::parse($filters['start_date'])->format('d-m-Y') }}
            s.d.
            {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->format('d-m-Y') }}
        </p>

        <p>
            <strong>Ruang:</strong>
            {{ $rooms->firstWhere('id', $filters['room_id'])?->name ?? 'Semua ruang' }}
            <br>
            <strong>Status:</strong>
            {{ ! empty($filters['status']) ? ucfirst($filters['status']) : 'Semua status' }}
        </p>

        <div class="report-summary">
            <div>
                Total Pengajuan
                <strong>{{ $total }}</strong>
            </div>

            @foreach ($statuses as $status)
                <div>
                    {{ ucfirst($status) }}
                    <strong>{{ $counts->get($status, 0) }}</strong>
                </div>
            @endforeach
        </div>

        <p class="muted">
            Ringkasan mengikuti filter yang sedang ditampilkan.
            Semua waktu menggunakan WIB.
        </p>

        <div class="actions no-print" style="margin-bottom: 20px;">
            @if ($printing)
                <button type="button" class="btn" onclick="window.print()">
                    Cetak / Simpan PDF
                </button>

                <a class="btn btn-secondary"
                   href="{{ route('admin.reports.index', $filters) }}">
                    Kembali ke Laporan
                </a>
            @else
                <a class="btn"
                   href="{{ route('admin.reports.print', $filters) }}"
                   target="_blank"
                   rel="noopener">
                    Buka Versi Cetak
                </a>

                <a class="btn btn-secondary"
                   href="{{ route('admin.dashboard') }}">
                    Dashboard
                </a>
            @endif
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Mahasiswa / NIM</th>
                        <th>Kegiatan</th>
                        <th>Ruang</th>
                        <th>Tanggal</th>
                        <th>Waktu WIB</th>
                        <th>Peserta</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>
                                {{ $printing
                                    ? $loop->iteration
                                    : $bookings->firstItem() + $loop->index }}
                            </td>
                            <td>
                                {{ $booking->user->name }}<br>
                                {{ $booking->user->nim ?? '-' }}
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
                            <td>{{ $booking->participant_count }}</td>
                            <td>{{ ucfirst($booking->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                Tidak ada peminjaman sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (! $printing)
            <div class="no-print">
                @include('partials.pagination', ['items' => $bookings])
            </div>
        @endif

        @if ($printing)
            <p style="margin-top: 24px;">
                Laporan dibuat oleh {{ auth()->user()->name }}
                pada {{ now()->format('d-m-Y H:i') }} WIB.
            </p>
        @endif
    </section>
@endsection