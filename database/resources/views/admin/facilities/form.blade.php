@extends('layouts.app')

@section(
    'title',
    ($facility->exists ? 'Edit Fasilitas' : 'Tambah Fasilitas') . ' | SIPERU'
)

@section('content')
    <section class="card">
        <h1>
            {{ $facility->exists ? 'Edit Fasilitas' : 'Tambah Fasilitas' }}
        </h1>

        <p class="muted">
            Masukkan nama jenis fasilitas, misalnya Proyektor atau AC.
            Jumlahnya diatur pada masing-masing ruang.
        </p>

        <form
            method="POST"
            action="{{ $facility->exists
                ? route('admin.facilities.update', $facility)
                : route('admin.facilities.store') }}"
        >
            @csrf

            @if ($facility->exists)
                @method('PUT')
            @endif

            <label for="name">Nama fasilitas</label>

            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name', $facility->name) }}"
                maxlength="100"
                placeholder="Contoh: Proyektor"
                required
                autofocus
                @error('name')
                    aria-invalid="true"
                    aria-describedby="name-error"
                @enderror
            >

            @error('name')
                <small class="form-error" id="name-error">
                    {{ $message }}
                </small>
            @enderror

            <label for="total_stock">Total stok milik kampus</label>

            <input
                id="total_stock"
                name="total_stock"
                type="number"
                min="0"
                max="100000"
                value="{{ old('total_stock', $facility->total_stock ?? 0) }}"
                required
            >

            @error('total_stock')
                <span class="form-error">{{ $message }}</span>
            @enderror

            <p class="muted">
                Isi jumlah seluruh barang, termasuk yang sudah berada di ruangan.
            </p>

            <div class="form-actions">
                <button class="btn" type="submit">
                    {{ $facility->exists ? 'Simpan Perubahan' : 'Simpan Fasilitas' }}
                </button>

                <a
                    class="btn btn-secondary"
                    href="{{ route('admin.facilities.index') }}"
                >
                    Batal
                </a>
            </div>


            <div class="form-actions" style="margin-top: 24px;">
                <button type="submit" class="btn">
                    {{ $room->exists ? 'Simpan Perubahan' : 'Simpan Ruang' }}
                </button>

                <a href="{{ route('admin.rooms.index') }}"
                class="btn btn-secondary">
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection