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
            padding: 18px 24px;
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

        .brand {
            font-size: 22px;
            font-weight: bold;
            text-decoration: none;
            color: white;
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

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #bfcbd9;
            border-radius: 8px;
            font: inherit;
        }

        input:focus {
            outline: 2px solid #287bb5;
            outline-offset: 2px;
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
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a class="brand"
               href="{{ auth()->check() ? route('dashboard') : route('login') }}">
                SIPERU
            </a>

            @auth
                <form method="POST" action="{{ route('logout') }}">
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