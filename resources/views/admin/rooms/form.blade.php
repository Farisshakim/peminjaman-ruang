@extends('layouts.app')

@section('title', ($room->exists ? 'Edit ruang' : 'Tambah ruang') . ' | SIPERU')

@section('content')
    <section class="card">
        <div class="toolbar">
            <h1>{{ $room->exists ? 'Edit ruang' : 'Tambah ruang' }}</h1>

            <a
                href="{{ route('admin.rooms.index') }}"
                class="btn btn-secondary"
            >
                Kembali ke daftar ruang
            </a>
        </div>

        <p class="muted">
            Atur informasi, jam operasional, dan fasilitas yang tersedia di ruang ini.
        </p>

        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <strong>Periksa kembali data yang kamu masukkan.</strong>

                <ul>
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

            <h2>Informasi ruang</h2>

            <div class="form-grid">
                <div>
                    <label for="name">Nama ruang</label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $room->name) }}"
                        maxlength="100"
                        placeholder="Contoh: Ruang Diskusi"
                        required
                        autofocus
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
                        placeholder="Contoh: Gedung A, Lantai 2"
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
                        step="1"
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
                            Tidak aktif
                        </option>
                    </select>
                </div>

                <div>
                    <label for="opening_time">Jam buka</label>

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
                    <label for="closing_time">Jam tutup</label>

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

            <label for="description">Deskripsi ruang</label>

            <textarea
                id="description"
                name="description"
                rows="4"
                maxlength="5000"
                placeholder="Jelaskan kegunaan atau informasi tambahan tentang ruang."
            >{{ old('description', $room->description) }}</textarea>

            <label for="photo">Foto ruang</label>

            <input
                id="photo"
                name="photo"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                aria-describedby="photo-help"
            >

            <p class="muted" id="photo-help">
                Format JPG, PNG, atau WebP dengan ukuran maksimal 2 MB.
                Kosongkan jika tidak ingin mengganti foto.
            </p>

            @if ($room->photo)
                <img
                    src="{{ asset('storage/' . $room->photo) }}"
                    alt="Foto {{ $room->name }}"
                    class="photo-preview"
                    style="max-width: 320px; width: 100%; height: auto; border-radius: 8px;"
                >
            @endif

            <h2 style="margin-top: 28px;">Fasilitas ruang</h2>

            <p class="muted">
                Pilih fasilitas yang ingin ditempatkan di ruang ini,
                lalu tentukan jumlahnya.
            </p>

            <div
                style="
                    padding: 18px;
                    background: #f5f8fc;
                    border: 1px solid #e1e7ef;
                    border-radius: 8px;
                    margin-bottom: 20px;
                "
            >
                <label for="facility_picker">Pilih fasilitas</label>

                <select
                    id="facility_picker"
                    aria-describedby="facility_feedback"
                >
                    <option value="">— Pilih fasilitas —</option>

                    @foreach ($facilities as $facility)
                        @php
                            $currentQuantity = (int) (
                                $quantities[$facility->id] ?? 0
                            );

                            $maximum = max(
                                0,
                                (int) $facility->available_stock + $currentQuantity
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
                ></p>
            </div>

            <div class="table-wrapper" style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Fasilitas dipilih</th>
                            <th scope="col">Maksimal untuk ruang ini</th>
                            <th scope="col">Jumlah</th>
                            <th scope="col">Tindakan</th>
                        </tr>
                    </thead>

                    <tbody id="selected_facilities">
                        @foreach ($facilities as $facility)
                            @php
                                $currentQuantity = (int) (
                                    $quantities[$facility->id] ?? 0
                                );

                                $maximum = max(
                                    0,
                                    (int) $facility->available_stock + $currentQuantity
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
                                    <label for="facility_{{ $facility->id }}">
                                        {{ $facility->name }}
                                    </label>
                                </td>

                                <td>{{ $maximum }} unit</td>

                                <td>
                                    <input
                                        id="facility_{{ $facility->id }}"
                                        name="facility_quantities[{{ $facility->id }}]"
                                        type="{{ $selectedQuantity > 0 ? 'number' : 'hidden' }}"
                                        value="{{ max(0, $selectedQuantity) }}"
                                        min="1"
                                        max="{{ $maximum }}"
                                        step="1"
                                        @required($selectedQuantity > 0)
                                        style="margin: 0; min-width: 90px;"
                                    >

                                    @error('facility_quantities.' . $facility->id)
                                        <span class="form-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-danger"
                                        data-remove-facility="{{ $facility->id }}"
                                        aria-label="Hapus {{ $facility->name }} dari ruang"
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
                Jumlah maksimal sudah memperhitungkan stok yang digunakan
                oleh ruang lain. Perubahan penempatan fasilitas berlaku
                setelah kamu menyimpan form ini.
            </p>

            <p class="muted">
                Jenis fasilitas belum tersedia?
                Tambahkan melalui
                <a
                    href="{{ route('admin.facilities.index') }}"
                    target="_blank"
                    rel="noopener"
                >
                    Kelola Fasilitas
                </a>
                sebelum mengisi form ruang.
            </p>

            <div
                class="form-actions"
                style="
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    gap: 12px;
                    margin-top: 24px;
                    padding-top: 20px;
                    border-top: 1px solid #e1e7ef;
                "
            >
                <button type="submit" class="btn">
                    {{ $room->exists ? 'Simpan perubahan' : 'Simpan ruang' }}
                </button>

                <a
                    href="{{ route('admin.rooms.index') }}"
                    class="btn btn-secondary"
                >
                    Batal
                </a>
            </div>
        </form>
    </section>

    <script>
        (() => {
            const picker = document.getElementById('facility_picker');
            const addButton = document.getElementById('add_facility');
            const feedback = document.getElementById('facility_feedback');
            const emptyRow = document.getElementById('empty_facilities');

            if (!picker || !addButton || !feedback || !emptyRow) {
                return;
            }

            const rows = Array.from(
                document.querySelectorAll('[data-facility-row]')
            );

            const facilities = Array.from(picker.options)
                .filter(option => option.value !== '')
                .map(option => ({
                    id: option.value,
                    name: option.dataset.name,
                    maximum: Number(option.dataset.max)
                }));

            const findRow = id => rows.find(
                row => row.dataset.facilityRow === id
            );

            function refreshPicker() {
                const previousValue = picker.value;

                picker.replaceChildren(
                    new Option(' Pilih fasilitas ', '')
                );

                let remainingChoices = 0;
                let availableChoices = 0;

                facilities.forEach(facility => {
                    const row = findRow(facility.id);

                    if (!row || row.dataset.selected === '1') {
                        return;
                    }

                    remainingChoices++;

                    const label = facility.maximum > 0
                        ? `${facility.name} — tersedia ${facility.maximum} unit`
                        : `${facility.name} — stok habis`;

                    const option = new Option(label, facility.id);

                    option.disabled = facility.maximum <= 0;
                    picker.add(option);

                    if (!option.disabled) {
                        availableChoices++;
                    }
                });

                const previousOption = Array.from(picker.options).find(
                    option =>
                        option.value === previousValue &&
                        !option.disabled
                );

                if (previousOption) {
                    picker.value = previousValue;
                }

                picker.disabled = availableChoices === 0;

                addButton.disabled =
                    availableChoices === 0 || picker.value === '';

                if (facilities.length === 0) {
                    feedback.textContent =
                        'Belum ada fasilitas. Tambahkan melalui Kelola Fasilitas.';
                } else if (remainingChoices === 0) {
                    feedback.textContent =
                        'Semua jenis fasilitas sudah dipilih.';
                } else if (availableChoices === 0) {
                    feedback.textContent =
                        'Stok fasilitas yang belum dipilih sedang habis.';
                } else {
                    feedback.textContent =
                        'Pilih fasilitas, lalu klik Tambahkan.';
                }

                emptyRow.hidden = rows.some(
                    row => row.dataset.selected === '1'
                );
            }

            picker.addEventListener('change', () => {
                addButton.disabled = picker.value === '';
            });

            addButton.addEventListener('click', () => {
                const facility = facilities.find(
                    item => item.id === picker.value
                );

                if (!facility || facility.maximum <= 0) {
                    return;
                }

                const row = findRow(facility.id);

                if (!row || row.dataset.selected === '1') {
                    return;
                }

                const input = row.querySelector('input');

                row.dataset.selected = '1';
                row.hidden = false;

                input.type = 'number';
                input.min = '1';
                input.max = String(facility.maximum);
                input.value = '1';
                input.required = true;

                picker.value = '';

                refreshPicker();
                input.focus();
            });

            document.querySelectorAll('[data-remove-facility]')
                .forEach(button => {
                    button.addEventListener('click', () => {
                        const row = findRow(
                            button.dataset.removeFacility
                        );

                        if (!row) {
                            return;
                        }

                        const input = row.querySelector('input');

                        row.dataset.selected = '0';
                        row.hidden = true;

                        // Nilai nol melepas alokasi ketika form disimpan.
                        input.required = false;
                        input.type = 'hidden';
                        input.value = '0';

                        refreshPicker();
                    });
                });

            refreshPicker();
        })();
    </script>
@endsection