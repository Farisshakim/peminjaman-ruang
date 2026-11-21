@extends('layouts.app')

@section('title', 'Login | SIPERU')

@section('content')
    <section class="card login-card">
        <h1>Masuk ke SIPERU</h1>

        <p class="muted">
            Masuk untuk mengakses Sistem Peminjaman Ruang Kampus.
        </p>
        
        @if ($errors->any())
            <div class="alert error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <p style="margin-top: 24px; text-align: center;">
                Belum punya akun mahasiswa?
                <a href="{{ route('register') }}">Daftar</a>
            </p>

            <label for="email">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="nama@siperu.test"
                autocomplete="username"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
            >

            <button class="primary" type="submit">Masuk</button>

        </form>
    </section>
@endsection