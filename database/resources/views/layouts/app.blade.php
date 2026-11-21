<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'SIPERU')</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #203047;
            line-height: 1.6;
        }

        header {
            background: #173b57;
            color: white;
            padding: 12px 24px;
        }

        .header-content {
            max-width: 1040px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .brand-group {
            display: flex;
            flex-direction: column;
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #d2e3f0;
            margin-top: 2px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .nav-btn {
            color: #d2e3f0;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            padding: 8px 14px;
            border-radius: 6px;
            transition: background-color 0.2s, color 0.2s;
        }

        .nav-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }

        .nav-btn.active {
            background: #176b9a;
            color: #ffffff;
        }

        main {
            max-width: 1040px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border: 1px solid #e1e7ef;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(23, 59, 87, 0.05);
        }

        .login-card {
            max-width: 440px;
            margin: 60px auto;
        }

        h1 {
            margin-top: 0;
            font-size: 26px;
        }

        .muted {
            color: #607086;
        }

        label {
            display: block;
            margin: 18px 0 6px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #bfcbd9;
            border-radius: 8px;
            font: inherit;
            background: white;
            box-sizing: border-box;
        }

        input:focus, select:focus, textarea:focus {
            outline: 2px solid #287bb5;
            outline-offset: 2px;
        }

        select {
            cursor: pointer;
        }

        textarea {
            resize: vertical;
        }

        button {
            border: 0;
            border-radius: 8px;
            padding: 11px 18px;
            font: inherit;
            font-weight: bold;
            cursor: pointer;
        }

        .primary {
            background: #176b9a;
            color: white;
            width: 100%;
            margin-top: 24px;
        }

        .logout {
            background: white;
            color: #173b57;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .error {
            background: #fff0f0;
            color: #a32323;
        }

        .success {
            background: #e8f7ef;
            color: #17633e;
        }

        .badge {
            display: inline-block;
            border-radius: 20px;
            padding: 4px 12px;
            background: #e8f1fa;
            color: #175881;
        }

        .info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 24px;
        }

        .info div {
            padding: 18px;
            background: #f5f8fc;
            border-radius: 10px;
            overflow-wrap: anywhere;
        }

        @media (max-width: 480px) {
            .card {
                padding: 20px;
            }

            .login-card {
                margin-top: 30px;
            }
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .toolbar h1 {
            margin-bottom: 0;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: #176b9a;
            color: white;
            text-decoration: none;
            font: inherit;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-secondary {
            background: #e8eef5;
            color: #203047;
        }

        .btn-danger {
            background: #b42332;
            color: white;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .actions form {
            margin: 0;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e1e7ef;
            vertical-align: middle;
        }

        th {
            background: #f5f8fc;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .photo-preview {
            width: 140px;
            height: 95px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e1e7ef;
            display: block;
            margin-top: 10px;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .form-error {
            color: #a32323;
            display: block;
            margin-top: 6px;
        }

        .room-photo {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e1e7ef;
            display: block;
            margin-bottom: 6px;
        }

        .room-photo-placeholder {
            width: 80px;
            height: 60px;
            background: #f0f3f6;
            border-radius: 6px;
            border: 1px solid #e1e7ef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #607086;
            margin-bottom: 6px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .status-active, .status-disetujui {
            background: #e8f7ef;
            color: #17633e;
        }

        .status-inactive, .status-ditolak {
            background: #fff0f0;
            color: #a32323;
        }

        .status-menunggu {
            background: #fff8e5;
            color: #8a5800;
        }

        .status-dibatalkan {
            background: #f1f3f6;
            color: #475467;
        }

        .status-selesai {
            background: #eef4ff;
            color: #175cd3;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="brand-group">
                <a class="brand"
                   href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                    SIPERU
                </a>
                <span class="brand-subtitle">Sistem Informasi Peminjaman Ruang</span>
            </div>

            @auth
                @if (auth()->user()->role === 'admin')
                    <nav class="nav-menu">
                        <a href="{{ route('admin.dashboard') }}"
                           class="nav-btn {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.rooms.index') }}"
                           class="nav-btn {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">
                            Kelola Ruang
                        </a>
                        <a href="{{ route('admin.facilities.index') }}"
                           class="nav-btn {{ request()->routeIs('admin.facilities.*') ? 'active' : '' }}">
                            Kelola Fasilitas
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                           class="nav-btn {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            Kelola Akun
                        </a>
                        <a href="{{ route('admin.reports.index') }}"
                           class="nav-btn {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            Laporan
                        </a>
                    </nav>
                @elseif (auth()->user()->role === 'mahasiswa')
                    <nav class="nav-menu">
                        <a href="{{ route('mahasiswa.dashboard') }}"
                           class="nav-btn {{ request()->routeIs('mahasiswa.dashboard') ? 'active' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('mahasiswa.rooms.index') }}"
                           class="nav-btn {{ request()->routeIs('mahasiswa.rooms.*') ? 'active' : '' }}">
                            Cari Ruang
                        </a>
                        <a href="{{ route('mahasiswa.bookings.index') }}"
                           class="nav-btn {{ request()->routeIs('mahasiswa.bookings.*') ? 'active' : '' }}">
                            Peminjaman Saya
                        </a>
                    </nav>
                @elseif (auth()->user()->role === 'petugas')
                    <nav class="nav-menu">
                        <a href="{{ route('petugas.dashboard') }}"
                           class="nav-btn {{ request()->routeIs('petugas.dashboard') ? 'active' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('petugas.bookings.index') }}"
                           class="nav-btn {{ request()->routeIs('petugas.bookings.*') ? 'active' : '' }}">
                            Kelola Peminjaman
                        </a>
                    </nav>
                @endif

                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button class="logout" type="submit">Keluar</button>
                </form>
            @endauth
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="alert success" role="status">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert error" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>