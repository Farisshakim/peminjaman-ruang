@extends('layouts.app')

@section(
    'title',
    ($user->exists ? 'Edit Akun' : 'Tambah Akun') . ' | SIPERU'
)

@section('content')
    @php
        $isSelf = $user->exists && $user->id === auth()->id();
    @endphp

    <section class="card">
        <h1>{{ $user->exists ? 'Edit Akun' : 'Tambah Akun' }}</h1>

        @if ($errors->any())
            <div class="alert error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            action="{{ $user->exists
                ? route('admin.users.update', $user)
                : route('admin.users.store') }}"
        >
            @csrf

            @if ($user->exists)
                @method('PUT')
            @endif

            <div class="form-grid">
                <div>
                    <label for="name">Nama lengkap</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        maxlength="255"
                        required
                    >
                </div>

                <div>
                    <label for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $user->email) }}"
                        maxlength="255"
                        required
                    >
                </div>

                <div>
                    <label for="role">Peran</label>

                    @if ($isSelf)
                        <input type="hidden" name="role" value="admin">
                        <input id="role" value="Admin" disabled>
                    @else
                        <select id="role" name="role" required>
                            @foreach (['mahasiswa', 'petugas', 'admin'] as $role)
                                <option
                                    value="{{ $role }}"
                                    @selected(
                                        old('role', $user->role ?? 'mahasiswa') === $role
                                    )
                                >
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label for="is_active">Status akun</label>

                    @if ($isSelf)
                        <input type="hidden" name="is_active" value="1">
                        <input id="is_active" value="Aktif" disabled>
                    @else
                        <select id="is_active" name="is_active" required>
                            <option
                                value="1"
                                @selected(
                                    (int) old('is_active', $user->is_active ?? 1) === 1
                                )
                            >
                                Aktif
                            </option>

                            <option
                                value="0"
                                @selected(
                                    (int) old('is_active', $user->is_active ?? 1) === 0
                                )
                            >
                                Nonaktif
                            </option>
                        </select>
                    @endif
                </div>

                <div>
                    <label for="nim">NIM</label>
                    <input
                        id="nim"
                        name="nim"
                        type="text"
                        value="{{ old('nim', $user->nim) }}"
                        maxlength="30"
                    >
                    <small class="muted">
                        Wajib untuk mahasiswa. Diabaikan untuk peran lain.
                    </small>
                </div>
            </div>

            @if ($isSelf)
                <p class="muted">
                    Peran dan status akun sendiri tidak dapat diubah.
                </p>
            @endif

            <h2 style="margin-top: 28px;">
                {{ $user->exists ? 'Ganti Password' : 'Password Akun' }}
            </h2>

            @if ($user->exists)
                <p class="muted">
                    Kosongkan kedua kolom jika tidak ingin mengganti password.
                </p>
            @endif

            <div class="form-grid">
                <div>
                    <label for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        @required(! $user->exists)
                    >
                </div>

                <div>
                    <label for="password_confirmation">
                        Konfirmasi password
                    </label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        @required(! $user->exists)
                    >
                </div>
            </div>

            <div class="form-actions">
                <button class="btn" type="submit">
                    {{ $user->exists ? 'Simpan Perubahan' : 'Simpan Akun' }}
                </button>

                <a
                    class="btn btn-secondary"
                    href="{{ route('admin.users.index') }}"
                >
                    Batal
                </a>
            </div>
        </form>
    </section>
@endsection