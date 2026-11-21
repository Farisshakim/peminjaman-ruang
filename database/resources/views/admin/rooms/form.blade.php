@extends('layouts.app')

@section('title', ($room->exists ? 'Edit Ruang' : 'Tambah Ruang') . ' | SIPERU')

@section('content')
    <section class="card">
        <div class="toolbar">
            <h1>{{ $room->exists ? 'Edit Ruang' : 'Tambah Ruang' }}</h1>

            <a class="btn btn-secondary"
               href="{{ route('admin.rooms.index') }}">
                Kembali ke Daftar Ruang
            </a>
        </div>

        @if ($errors->any())
            <div class="alert error" role="alert">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                        placeholder="Gedung A, Lantai 1"
                        maxlength="150"
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

                    @php
                        $activeValue = (string) old(
                            'is_active',
                            (int) ($room->is_active ?? true)
                        );
                    @endphp

                    <select id="is_active" name="is_active" required>
                        <option value="1" @selected($activeValue === '1')>
                            Aktif
                        </option>
                        <option value="0" @selected($activeValue === '0')>
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div>
                    <label for="opening_time">Jam buka (WIB)</label>
                    <input
                        id="opening_time"
                        name="opening_time"
                        type="time"
                        step="60"
                        value="{{ old(
                            'opening_time',
                            substr($room->opening_time ?? '07:00:00', 0, 5)
                        ) }}"
                        required
                    >
                </div>

                <div>
                    <label for="closing_time">Jam tutup (WIB)</label>
                    <input
                        id="closing_time"
                        name="closing_time"
                        type="time"
                        step="60"
                        value="{{ old(
                            'closing_time',
                            substr($room->closing_time ?? '21:00:00', 0, 5)
                        ) }}"
                        required
                    >
                </div>
            </div>

            <p class="muted">
                Seluruh waktu menggunakan WIB.
                Jam tutup harus setelah jam buka.
            </p>

            <label for="description">Deskripsi</label>
            <textarea
                id="description"
                name="description"
                rows="4"
                maxlength="5000"
                placeholder="Tuliskan peruntukan ruang dan aturan penggunaannya."
            >{{ old('description', $room->description) }}</textarea>

            <label for="photo">Foto ruang</label>
            <input
                id="photo"
                name="photo"
                type="file"
                accept="image/jpeg,image/png,image/webp"
            >

            <p class="muted">
                Opsional. Format JPG, PNG, atau WEBP, maksimal 2 MB.
                Kosongkan untuk mempertahankan foto lama.
                Jika penyimpanan gagal, pilih ulang foto sebelum mengirim.
            </p>

            @if ($room->photo)
                <img
                    class="photo-preview"
                    src="{{ asset('storage/' . $room->photo) }}"
                    alt="Foto {{ $room->name }}"
                >
            @endif

            <h2 style="margin-top: 28px;">Fasilitas Ruang</h2>

<p class="muted">
    Pilih fasilitas yang ingin ditempatkan di ruang ini,
    lalu tentukan jumlahnya.
</p>

<div style="
    padding: 18px;
    background: #f5f8fc;
    border: 1px solid #e1e7ef;
    border-radius: 8px;
    margin-bottom: 20px;
">
    <label for="facility_search">Cari fasilitas</label>

    <input
        id="facility_search"
        type="search"
        placeholder="Ketik nama fasilitas..."
        autocomplete="off"
    >

    <label for="facility_picker">Pilih fasilitas</label>

    <select id="facility_picker">
        <option value="">— Pilih fasilitas —</option>

        @foreach ($facilities as $facility)
            @php
                $currentQuantity = (int) ($quantities[$facility->id] ?? 0);

                $maximum = max(
                    0,
                    $facility->available_stock + $currentQuantity
                );
            @endphp

            <option
                value="{{ $facility->id }}"
                data-name="{{ $facility->name }}"
                data-max="{{ $maximum }}"
            >
                {{ $facility->name }} — maksimal {{ $maximum }} unit
            </option>
        @endforeach
    </select>

    <button
        id="add_facility"
        type="button"
        class="btn"
        style="margin-top: 12px;"
    >
        Tambahkan
    </button>

    <p
        id="facility_feedback"
        class="muted"
        role="status"
        aria-live="polite"
        style="margin-bottom: 0;"
    ></p>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Fasilitas Dipilih</th>
                <th>Maksimal untuk Ruang Ini</th>
                <th>Jumlah</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody id="selected_facilities">
            @foreach ($facilities as $facility)
                @php
                    $currentQuantity = (int) ($quantities[$facility->id] ?? 0);

                    $maximum = max(
                        0,
                        $facility->available_stock + $currentQuantity
                    );

                    $selectedQuantity = (int) old(
                        'facility_quantities.' . $facility->id,
                        $currentQuantity
                    );
                @endphp

                <tr
                    data-facility-row="{{ $facility->id }}"
                    data-selected="{{ $selectedQuantity > 0 ? '1' : '0' }}"
                    @if ($selectedQuantity <= 0) hidden @endif
                >
                    <td>
                        <label
                            for="facility_{{ $facility->id }}"
                            style="margin: 0;"
                        >
                            {{ $facility->name }}
                        </label>
                    </td>

                    <td>{{ $maximum }} unit</td>

                    <td style="min-width: 130px;">
                        {{-- Nilai 0 tetap dikirim ketika fasilitas dilepas. --}}
                        <input
                            id="facility_{{ $facility->id }}"
                            name="facility_quantities[{{ $facility->id }}]"
                            type="{{ $selectedQuantity > 0 ? 'number' : 'hidden' }}"
                            value="{{ $selectedQuantity }}"
                            min="1"
                            max="{{ $maximum }}"
                            step="1"
                            @required($selectedQuantity > 0)
                            style="margin: 0;"
                        >

                        @error('facility_quantities.' . $facility->id)
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn btn-danger"
                            data-remove-facility="{{ $facility->id }}"
                        >
                            Hapus dari ruang
                        </button>
                    </td>
                </tr>
            @endforeach

            <tr id="empty_facilities">
                <td colspan="4" class="muted">
                    Belum ada fasilitas yang dipilih.
                </td>
            </tr>
        </tbody>
    </table>
</div>

<p class="muted">
    Maksimal untuk ruang ini sudah termasuk alokasi lama ruang ini.
    Perubahan jumlah dan penghapusan dari ruang baru berlaku
    setelah tombol simpan ditekan.
</p>

<p class="muted">
    Jenis fasilitas dan total stok dikelola melalui
    <a
        href="{{ route('admin.facilities.index') }}"
        target="_blank"
        rel="noopener"
    >
        Kelola Fasilitas
    </a>.
</p>

<script>
    (() => {
        const search = document.getElementById('facility_search');
        const picker = document.getElementById('facility_picker');
        const addButton = document.getElementById('add_facility');
        const feedback = document.getElementById('facility_feedback');
        const emptyRow = document.getElementById('empty_facilities');

        const rows = Array.from(
            document.querySelectorAll('[data-facility-row]')
        );

        // Simpan pilihan asli agar daftar dapat dibangun ulang
        // sesuai pencarian dan fasilitas yang sudah dipilih.
        const facilities = Array.from(picker.options)
            .filter(option => option.value !== '')
            .map(option => ({
                id: option.value,
                name: option.dataset.name,
                maximum: Number(option.dataset.max),
            }));

        const findRow = id => rows.find(
            row => row.dataset.facilityRow === id
        );

        function refreshPicker() {
            const keyword = search.value.trim().toLocaleLowerCase('id');
            const previousValue = picker.value;

            picker.replaceChildren(
                new Option('— Pilih fasilitas —', '')
            );

            let matches = 0;
            let availableChoices = 0;

            facilities.forEach(facility => {
                const row = findRow(facility.id);

                if (row.dataset.selected === '1') return;

                if (!facility.name.toLocaleLowerCase('id').includes(keyword)) {
                    return;
                }

                matches++;

                const option = new Option(
                    `${facility.name} — maksimal ${facility.maximum} unit`,
                    facility.id
                );

                option.disabled = facility.maximum <= 0;
                picker.add(option);

                if (!option.disabled) availableChoices++;
            });

            if (Array.from(picker.options).some(
                option => option.value === previousValue && !option.disabled
            )) {
                picker.value = previousValue;
            }

            addButton.disabled = availableChoices === 0;

            if (matches === 0) {
                feedback.textContent =
                    'Tidak ada fasilitas yang cocok atau semuanya sudah dipilih.';
            } else if (availableChoices === 0) {
                feedback.textContent =
                    'Fasilitas ditemukan, tetapi stok tersedia sudah habis.';
            } else {
                feedback.textContent =
                    'Pilih fasilitas, lalu klik Tambahkan.';
            }

            emptyRow.hidden = rows.some(
                row => row.dataset.selected === '1'
            );
        }

        addButton.addEventListener('click', () => {
            const facility = facilities.find(
                item => item.id === picker.value
            );

            if (!facility) {
                feedback.textContent = 'Pilih fasilitas terlebih dahulu.';
                return;
            }

            const row = findRow(facility.id);

            if (row.dataset.selected === '1' || facility.maximum <= 0) {
                return;
            }

            const input = row.querySelector('input');

            row.dataset.selected = '1';
            row.hidden = false;

            input.type = 'number';
            input.value = '1';
            input.required = true;

            search.value = '';
            picker.value = '';

            refreshPicker();
            input.focus();
        });

        document.querySelectorAll('[data-remove-facility]')
            .forEach(button => {
                button.addEventListener('click', () => {
                    const row = findRow(button.dataset.removeFacility);
                    const input = row.querySelector('input');

                    row.dataset.selected = '0';
                    row.hidden = true;

                    input.required = false;
                    input.type = 'hidden';
                    input.value = '0';

                    refreshPicker();
                });
            });

        search.addEventListener('input', refreshPicker);

        // Enter pada kotak pencarian tidak langsung menyimpan ruang.
        search.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                picker.focus();
            }
        });

        refreshPicker();
    })();
</script>
@endsection