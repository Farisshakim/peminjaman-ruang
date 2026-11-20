@extends('layouts.app')

@section('title', 'Daftar Mahasiswa | SIPERU')

@section('content')
    <section class="card login-card">
        <h1>Daftar Mahasiswa</h1>

        <p class="muted">
            Buat akun untuk mengajukan peminjaman ruang kampus.
        </p>

        @if ($errors->any())
            <div class="alert error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}">
            @csrf

            <label for="name">Nama lengkap</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                maxlength="255"
                autocomplete="name"
                required
                autofocus
            >

            <label for="nim">NIM</label>
            <input
                id="nim"
                name="nim"
                type="text"
                value="{{ old('nim') }}"
                maxlength="30"
                required
            >

            <label for="email">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                maxlength="255"
                autocomplete="email"
                required
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
                aria-describedby="password-help"
                required
            >

            <small class="muted" id="password-help">
                Gunakan password minimal 8 karakter.
            </small>

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
                required
            >

            <button class="primary" type="submit">
                Daftar
            </button>
        </form>

        <p style="margin-top: 24px; text-align: center;">
            Sudah punya akun?
            <a href="{{ route('login') }}">Masuk</a>
        </p>
    </section>
@endsection