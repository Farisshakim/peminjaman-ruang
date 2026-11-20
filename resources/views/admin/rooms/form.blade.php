@extends('layouts.app')

@section(
    'title',
    ($room->exists ? 'Edit Ruang' : 'Tambah Ruang') . ' | SIPERU'
)

@section('content')
    <section class="card">
        <h1>{{ $room->exists ? 'Edit Ruang' : 'Tambah Ruang' }}</h1>

        @if ($errors->any())
            <div class="alert error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            enctype="multipart/form-data"
            action="{{ $room->exists
                ? route('admin.rooms.update', $room)
                : route('admin.rooms.store') }}"
        >
            @csrf

            @if ($room->exists)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div>
                    <label for="name">Nama ruang</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $room->name) }}"
                        maxlength="100"
                        required
                    >
                </div>

                <div>
                    <label for="location">Lokasi</label>
                    <input
                        id="location"
                        name="location"
                        type="text"
                        value="{{ old('location', $room->location) }}"
                        maxlength="150"
                        placeholder="Gedung A, Lantai 1"
                        required
                    >
                </div>

                <div>
                    <label for="capacity">Kapasitas orang</label>
                    <input
                        id="capacity"
                        name="capacity"
                        type="number"
                        value="{{ old('capacity', $room->capacity) }}"
                        min="1"
                        max="100000"
                        required
                    >
                </div>

                <div>
                    <label for="is_active">Status ruang</label>
                    <select id="is_active" name="is_active" required>
                        <option
                            value="1"
                            @selected(
                                (string) old('is_active', $room->is_active ?? 1) === '1'
                            )
                        >
                            Aktif
                        </option>

                        <option
                            value="0"
                            @selected(
                                (string) old('is_active', (int) ($room->is_active ?? 1)) === '0'
                            )
                        >
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div>
                    <label for="opening_time">Jam buka</label>
                    <input
                        id="opening_time"
                        name="opening_time"
                        type="time"
                        value="{{ old(
                            'opening_time',
                            substr($room->opening_time ?? '07:00:00', 0, 5)
                        ) }}"
                        required
                    >
                </div>

                <div>
                    <label for="closing_time">Jam tutup</label>
                    <input
                        id="closing_time"
                        name="closing_time"
                        type="time"
                        value="{{ old(
                            'closing_time',
                            substr($room->closing_time ?? '21:00:00', 0, 5)
                        ) }}"
                        required
                    >
                </div>
            </div>

            <label for="description">Deskripsi</label>
            <textarea
                id="description"
                name="description"
                rows="4"
                maxlength="5000"
            >{{ old('description', $room->description) }}</textarea>

            <label for="photo">Foto ruang</label>
            <input
                id="photo"
                name="photo"
                type="file"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="muted">
                Opsional. JPG, PNG, atau WEBP, maksimal 2 MB.
                Kosongkan jika ingin mempertahankan foto lama.
                Jika formulir gagal validasi, pilih ulang foto.
            </small>

            @if ($room->photo)
                <img
                    class="photo-preview"
                    src="{{ asset('storage/' . $room->photo) }}"
                    alt="Foto ruang saat ini"
                >
            @endif

            <h2 style="margin-top: 28px;">Fasilitas ruang</h2>

            <p class="muted">
                Isi jumlah setiap fasilitas. Gunakan 0 jika tidak tersedia.
            </p>

            <div class="form-grid">
                @forelse ($facilities as $facility)
                    <div>
                        <label for="facility_{{ $facility->id }}">
                            {{ $facility->name }}
                        </label>

                        <input
                            id="facility_{{ $facility->id }}"
                            name="facility_quantities[{{ $facility->id }}]"
                            type="number"
                            value="{{ old(
                                'facility_quantities.' . $facility->id,
                                $quantities[$facility->id] ?? 0
                            ) }}"
                            min="0"
                            max="100000"
                            required
                        >
                    </div>
                @empty
                    <p class="muted">
                        Belum ada jenis fasilitas. Tambahkan melalui menu
                        Kelola Fasilitas, lalu edit ruang ini.
                    </p>
                @endforelse
            </div>

            <div class="form-actions">
                <button class="btn" type="submit">
                    {{ $room->exists ? 'Simpan Perubahan' : 'Simpan Ruang' }}
                </button>

                <a
                    class="btn btn-secondary"
                    href="{{ route('admin.rooms.index') }}"
                >
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection
