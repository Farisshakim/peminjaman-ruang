@extends('layouts.app')

@section('title', 'Kelola Ruang | SIPERU')

@section('content')
    <style>
        .filter-form.room-search {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
            margin-bottom: 24px;
        }

        .filter-form.room-search input[type="search"] {
            flex: 1 1 260px;
            width: auto;
            min-width: 0;
            margin: 0;
        }

        .filter-form.room-search .btn {
            margin: 0;
            white-space: nowrap;
        }

        @media (max-width: 600px) {
            .filter-form.room-search input[type="search"] {
                flex-basis: 100%;
            }
        }
    </style>
    <section class="card">
        <div class="toolbar">
            <h1>Kelola Ruang</h1>

            <div class="actions">
                <a class="btn" href="{{ route('admin.rooms.create') }}">
                    Tambah Ruang
                </a>
            </div>
        </div>

        <form
            class="filter-form room-search"
            method="GET"
            action="{{ route('admin.rooms.index') }}"
        >
            <input
                name="search"
                type="search"
                value="{{ $search }}"
                placeholder="Cari nama atau lokasi ruang"
                aria-label="Cari ruang"
                maxlength="100"
            >

            <button class="btn" type="submit">Cari</button>

            <a
                class="btn btn-secondary"
                href="{{ route('admin.rooms.index') }}"
            >
                Reset
            </a>
        </form>

        @error('search')
            <div class="alert error">{{ $message }}</div>
        @enderror

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Ruang</th>
                        <th>Kapasitas</th>
                        <th>Jam operasional</th>
                        <th>Fasilitas</th>
                        <th>Status</th>
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
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >
                                    <div class="room-photo-placeholder" style="display: none;">Tanpa Foto</div>
                                @else
                                    <div class="room-photo-placeholder">Tanpa Foto</div>
                                @endif

                                <strong>{{ $room->name }}</strong><br>
                                <span class="muted">
                                    {{ $room->location }}
                                </span>
                            </td>

                            <td>{{ $room->capacity }} orang</td>

                            <td>
                                {{ substr($room->opening_time, 0, 5) }}
                                –
                                {{ substr($room->closing_time, 0, 5) }}
                            </td>

                            <td>
                                @forelse ($room->facilities as $facility)
                                    <div>
                                        {{ $facility->name }}:
                                        {{ $facility->pivot->quantity }}
                                    </div>
                                @empty
                                    <span class="muted">Belum diatur</span>
                                @endforelse
                            </td>

                            <td>
                                <span class="{{ $room->is_active
                                    ? 'status-active'
                                    : 'status-inactive' }}">
                                    {{ $room->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>
                                <a
                                    class="btn btn-secondary"
                                    href="{{ route('admin.rooms.edit', $room) }}"
                                >
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                Tidak ada ruang yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rooms->hasPages())
            <nav class="pagination" aria-label="Halaman daftar ruang">
                <div>
                    @if ($rooms->previousPageUrl())
                        <a
                            class="btn btn-secondary"
                            href="{{ $rooms->previousPageUrl() }}"
                        >
                            Sebelumnya
                        </a>
                    @endif
                </div>

                <span>
                    Halaman {{ $rooms->currentPage() }}
                    dari {{ $rooms->lastPage() }}
                </span>

                <div>
                    @if ($rooms->nextPageUrl())
                        <a
                            class="btn btn-secondary"
                            href="{{ $rooms->nextPageUrl() }}"
                        >
                            Berikutnya
                        </a>
                    @endif
                </div>
            </nav>
        @endif
    </section>
@endsection