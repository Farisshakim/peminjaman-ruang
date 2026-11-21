@extends('layouts.app')

@section(
    'title',
    ($room->exists ? 'Edit Ruang' : 'Tambah Ruang') . ' | SIPERU'
)

@section('content')
    <section class="card">
        <div class="toolbar">
            <h1>{{ $room->exists ? 'Edit Ruang' : 'Tambah Ruang' }}</h1>

            <a class="btn btn-secondary" href="{{ route('admin.rooms.index') }}">
                Kembali ke Daftar Ruang
            </a>
        </div>

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
                    <label for="opening_time">Jam buka (WIB)</label>
                    <div class="cp-input-wrapper" style="position: relative;">
                        <input
                            id="opening_time"
                            name="opening_time"
                            type="text"
                            value="{{ old(
                                'opening_time',
                                substr($room->opening_time ?? '07:00:00', 0, 5)
                            ) }}"
                            readonly
                            required
                            style="cursor: pointer; padding-right: 44px; background: white;"
                            onclick="openClockPicker('opening_time', event)"
                            placeholder="07:00"
                        >
                        <button
                            type="button"
                            onclick="openClockPicker('opening_time', event)"
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 18px; cursor: pointer; padding: 4px; color: #176b9a;"
                            title="Pilih jam"
                        >🕒</button>
                    </div>
                </div>

                <div>
                    <label for="closing_time">Jam tutup (WIB)</label>
                    <div class="cp-input-wrapper" style="position: relative;">
                        <input
                            id="closing_time"
                            name="closing_time"
                            type="text"
                            value="{{ old(
                                'closing_time',
                                substr($room->closing_time ?? '21:00:00', 0, 5)
                            ) }}"
                            readonly
                            required
                            style="cursor: pointer; padding-right: 44px; background: white;"
                            onclick="openClockPicker('closing_time', event)"
                            placeholder="21:00"
                        >
                        <button
                            type="button"
                            onclick="openClockPicker('closing_time', event)"
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 18px; cursor: pointer; padding: 4px; color: #176b9a;"
                            title="Pilih jam"
                        >🕒</button>
                    </div>
                </div>

                <div style="grid-column: 1 / -1; margin-top: -6px;">
                    <small class="muted" id="hours_helper">
                        Waktu operasional ruang dalam format WIB. Jam tutup harus setelah jam buka.
                    </small>
                </div>
            </div>

            <label for="description">Deskripsi</label>
            <textarea
                id="description"
                name="description"
                rows="4"
                maxlength="5000"
                placeholder="Tuliskan peruntukan ruang, aturan penggunaan, fasilitas pendukung, atau catatan khusus."
            >{{ old('description', $room->description) }}</textarea>

            <div style="display: flex; justify-content: flex-end; margin-top: 4px; margin-bottom: 12px;">
                <small class="muted" id="desc_counter">0 / 5000 karakter</small>
            </div>

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

            <div style="margin-top: 20px; padding: 18px; background: #f8fafc; border: 1px dashed #bfcbd9; border-radius: 8px;">
                <strong style="display: block; margin-bottom: 4px;">+ Tambah Jenis Fasilitas Lain ke Ruang Ini</strong>
                <p class="muted" style="margin: 0 0 12px; font-size: 13px;">
                    Fasilitas yang belum terdaftar di atas dapat langsung ditambahkan di bawah ini.
                </p>

                <div class="form-grid" style="margin-top: 0;">
                    <div>
                        <label for="new_facility_name" style="margin-top: 0; font-size: 13px;">Nama fasilitas baru</label>
                        <input
                            type="text"
                            id="new_facility_name"
                            name="new_facility_name"
                            value="{{ old('new_facility_name') }}"
                            placeholder="Contoh: Sound System / Mic Wireless"
                            maxlength="100"
                        >
                    </div>

                    <div>
                        <label for="new_facility_quantity" style="margin-top: 0; font-size: 13px;">Jumlah</label>
                        <input
                            type="number"
                            id="new_facility_quantity"
                            name="new_facility_quantity"
                            value="{{ old('new_facility_quantity', 0) }}"
                            min="0"
                            max="100000"
                            placeholder="0"
                        >
                    </div>
                </div>
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

    <!-- ClockPicker Popover Element -->
    <div id="clockpickerPopover" class="cp-popover" style="display: none;">
        <div class="cp-arrow"></div>
        <div class="cp-header">
            <span id="cpHourText" class="cp-title-unit active" onclick="setCpMode('hours')">07</span>
            <span class="cp-title-colon">:</span>
            <span id="cpMinuteText" class="cp-title-unit" onclick="setCpMode('minutes')">00</span>
        </div>
        <div class="cp-plate" id="cpPlate">
            <div class="cp-center-dot"></div>
            <div class="cp-line" id="cpLine">
                <div class="cp-bearing"></div>
            </div>
            <div id="cpTicksContainer"></div>
        </div>
    </div>

    <style>
        .cp-popover {
            position: absolute;
            width: 220px;
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.18);
            border-radius: 6px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
            z-index: 1000;
            padding: 12px 14px 14px;
            box-sizing: border-box;
            user-select: none;
        }

        .cp-arrow {
            position: absolute;
            top: -7px;
            right: 18px;
            width: 0;
            height: 0;
            border-left: 7px solid transparent;
            border-right: 7px solid transparent;
            border-bottom: 7px solid #ffffff;
            filter: drop-shadow(0 -1px 1px rgba(0,0,0,0.12));
        }

        .cp-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #f1f3f5;
            line-height: 1;
        }

        .cp-title-unit {
            cursor: pointer;
            color: #94a3b8;
            padding: 2px 6px;
            border-radius: 4px;
            transition: color 0.15s;
        }

        .cp-title-unit.active {
            color: #176b9a;
        }

        .cp-title-colon {
            color: #94a3b8;
        }

        .cp-plate {
            width: 186px;
            height: 186px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            position: relative;
            margin: 0 auto;
            cursor: pointer;
        }

        .cp-center-dot {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 4px;
            height: 4px;
            background: #176b9a;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            z-index: 5;
        }

        .cp-line {
            position: absolute;
            bottom: 50%;
            left: 50%;
            width: 1px;
            background: #176b9a;
            transform-origin: bottom center;
            z-index: 4;
            pointer-events: none;
            transition: transform 0.15s ease-out, height 0.15s ease-out;
        }

        .cp-bearing {
            position: absolute;
            top: -12px;
            left: -11.5px;
            width: 24px;
            height: 24px;
            background: #bae6fd;
            border: 1px solid #7dd3fc;
            border-radius: 50%;
            opacity: 0.85;
        }

        .cp-tick {
            position: absolute;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #334155;
            z-index: 6;
            cursor: pointer;
            line-height: 1;
        }

        .cp-tick.outer {
            font-size: 11px;
            font-weight: 500;
        }

        .cp-tick.inner {
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
        }

        .cp-tick.selected {
            color: #0369a1;
            font-weight: bold;
        }

        .cp-tick:hover {
            background: #f1f5f9;
        }
    </style>

    <script>
        // Character counter
        const descInput = document.getElementById('description');
        const descCounter = document.getElementById('desc_counter');
        if (descInput && descCounter) {
            const updateCount = () => {
                descCounter.textContent = `${descInput.value.length} / 5000 karakter`;
            };
            descInput.addEventListener('input', updateCount);
            updateCount();
        }

        // Operating duration calculation
        const openTimeInput = document.getElementById('opening_time');
        const closeTimeInput = document.getElementById('closing_time');
        const hoursHelperText = document.getElementById('hours_helper');

        const calculateDuration = () => {
            if (openTimeInput && closeTimeInput && hoursHelperText && openTimeInput.value && closeTimeInput.value) {
                const [oh, om] = openTimeInput.value.split(':').map(Number);
                const [ch, cm] = closeTimeInput.value.split(':').map(Number);
                const startMin = oh * 60 + om;
                const endMin = ch * 60 + cm;
                const diff = endMin - startMin;

                if (diff > 0) {
                    const h = Math.floor(diff / 60);
                    const m = diff % 60;
                    hoursHelperText.textContent = `Total durasi operasional: ${h} jam ${m > 0 ? m + ' menit' : ''} (WIB)`;
                    hoursHelperText.style.color = '#17633e';
                } else {
                    hoursHelperText.textContent = 'Jam tutup harus setelah jam buka.';
                    hoursHelperText.style.color = '#a32323';
                }
            }
        };

        if (openTimeInput && closeTimeInput) {
            openTimeInput.addEventListener('change', calculateDuration);
            closeTimeInput.addEventListener('change', calculateDuration);
            calculateDuration();
        }

        // --- ClockPicker Logic (Exact Dual-Ring Bootstrap ClockPicker) ---
        let currentTargetInput = null;
        let cpHour = 7;
        let cpMinute = 0;
        let cpMode = 'hours'; // 'hours' or 'minutes'

        function openClockPicker(inputId, event) {
            if (event) event.stopPropagation();
            const inputEl = document.getElementById(inputId);
            if (!inputEl) return;

            currentTargetInput = inputEl;
            const popover = document.getElementById('clockpickerPopover');

            // Parse time from input
            let timeVal = inputEl.value || '07:00';
            if (!timeVal.includes(':')) timeVal = '07:00';
            const [h, m] = timeVal.split(':').map(Number);
            cpHour = isNaN(h) ? 7 : Math.min(23, Math.max(0, h));
            cpMinute = isNaN(m) ? 0 : Math.min(59, Math.max(0, m));
            cpMode = 'hours';

            // Position popover relative to input wrapper
            const wrapper = inputEl.closest('.cp-input-wrapper');
            if (wrapper) {
                wrapper.appendChild(popover);
                popover.style.top = (inputEl.offsetHeight + 8) + 'px';
                popover.style.right = '0';
                popover.style.left = 'auto';
            }

            popover.style.display = 'block';
            renderClockPicker();
        }

        function closeClockPicker() {
            const popover = document.getElementById('clockpickerPopover');
            if (popover) popover.style.display = 'none';
        }

        function setCpMode(mode) {
            cpMode = mode;
            renderClockPicker();
        }

        function renderClockPicker() {
            const hourText = document.getElementById('cpHourText');
            const minText = document.getElementById('cpMinuteText');
            const container = document.getElementById('cpTicksContainer');
            const line = document.getElementById('cpLine');
            if (!hourText || !minText || !container || !line) return;

            hourText.textContent = String(cpHour).padStart(2, '0');
            minText.textContent = String(cpMinute).padStart(2, '0');

            hourText.className = 'cp-title-unit ' + (cpMode === 'hours' ? 'active' : '');
            minText.className = 'cp-title-unit ' + (cpMode === 'minutes' ? 'active' : '');

            container.innerHTML = '';
            const centerX = 93;
            const centerY = 93;

            if (cpMode === 'hours') {
                // Dual Ring 24h ClockPicker:
                // Inner ring (1 to 12): radius 48px
                const innerHours = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                const innerRadius = 48;

                // Outer ring (00, 13 to 23): radius 74px
                const outerHours = [0, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
                const outerRadius = 74;

                let handLength = 48;
                let handAngle = (cpHour % 12) * 30;

                // Render inner hours (1..12)
                innerHours.forEach((hour, i) => {
                    const angleDeg = i * 30;
                    const angleRad = (angleDeg - 90) * (Math.PI / 180);
                    const x = centerX + innerRadius * Math.cos(angleRad);
                    const y = centerY + innerRadius * Math.sin(angleRad);

                    const tick = document.createElement('div');
                    tick.className = 'cp-tick inner' + (cpHour === hour ? ' selected' : '');
                    tick.style.left = (x - 12) + 'px';
                    tick.style.top = (y - 12) + 'px';
                    tick.textContent = hour;

                    if (cpHour === hour) {
                        handLength = innerRadius;
                        handAngle = angleDeg;
                    }

                    tick.onclick = (e) => {
                        e.stopPropagation();
                        cpHour = hour;
                        renderClockPicker();
                        setTimeout(() => {
                            setCpMode('minutes');
                        }, 160);
                    };

                    container.appendChild(tick);
                });

                // Render outer hours (00, 13..23)
                outerHours.forEach((hour, i) => {
                    const angleDeg = i * 30;
                    const angleRad = (angleDeg - 90) * (Math.PI / 180);
                    const x = centerX + outerRadius * Math.cos(angleRad);
                    const y = centerY + outerRadius * Math.sin(angleRad);

                    const tick = document.createElement('div');
                    tick.className = 'cp-tick outer' + (cpHour === hour ? ' selected' : '');
                    tick.style.left = (x - 12) + 'px';
                    tick.style.top = (y - 12) + 'px';
                    tick.textContent = String(hour).padStart(2, '0');

                    if (cpHour === hour) {
                        handLength = outerRadius;
                        handAngle = angleDeg;
                    }

                    tick.onclick = (e) => {
                        e.stopPropagation();
                        cpHour = hour;
                        renderClockPicker();
                        setTimeout(() => {
                            setCpMode('minutes');
                        }, 160);
                    };

                    container.appendChild(tick);
                });

                line.style.height = handLength + 'px';
                line.style.transform = `rotate(${handAngle}deg)`;
            } else {
                // Minutes Ring (00, 05, 10, ..., 55): radius 72px
                const minRadius = 72;
                const handAngle = cpMinute * 6;

                for (let i = 0; i < 12; i++) {
                    const minVal = i * 5;
                    const angleDeg = i * 30;
                    const angleRad = (angleDeg - 90) * (Math.PI / 180);
                    const x = centerX + minRadius * Math.cos(angleRad);
                    const y = centerY + minRadius * Math.sin(angleRad);

                    const tick = document.createElement('div');
                    tick.className = 'cp-tick outer' + (cpMinute === minVal ? ' selected' : '');
                    tick.style.left = (x - 12) + 'px';
                    tick.style.top = (y - 12) + 'px';
                    tick.textContent = String(minVal).padStart(2, '0');

                    tick.onclick = (e) => {
                        e.stopPropagation();
                        cpMinute = minVal;
                        selectAndFinish();
                    };

                    container.appendChild(tick);
                }

                line.style.height = minRadius + 'px';
                line.style.transform = `rotate(${handAngle}deg)`;
            }
        }

        function selectAndFinish() {
            renderClockPicker();
            if (currentTargetInput) {
                const formatted = `${String(cpHour).padStart(2, '0')}:${String(cpMinute).padStart(2, '0')}`;
                currentTargetInput.value = formatted;
                currentTargetInput.dispatchEvent(new Event('change'));
            }
            setTimeout(closeClockPicker, 180);
        }

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            const popover = document.getElementById('clockpickerPopover');
            if (popover && popover.style.display !== 'none') {
                if (!popover.contains(e.target) && !e.target.closest('.cp-input-wrapper')) {
                    closeClockPicker();
                }
            }
        });

        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeClockPicker();
        });
    </script>
@endsection
