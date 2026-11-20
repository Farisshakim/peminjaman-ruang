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
        </form>
    </section>
@endsection