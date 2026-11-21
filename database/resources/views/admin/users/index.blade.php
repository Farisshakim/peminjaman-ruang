@extends('layouts.app')

@section('title', 'Kelola Akun | SIPERU')

@section('content')
    <section class="card">
        <div class="toolbar">
            <h1>Kelola Akun</h1>

            <a class="btn" href="{{ route('admin.users.create') }}">
                + Tambah Akun
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>NIM</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                {{ $user->name }}

                                @if ($user->id === auth()->id())
                                    <span class="badge">Kamu</span>
                                @endif
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>{{ ucfirst($user->role) }}</td>

                            <td>{{ $user->nim ?? '—' }}</td>

                            <td>
                                <span class="{{ $user->is_active
                                    ? 'status-active'
                                    : 'status-inactive' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>
                                <a
                                    class="btn btn-secondary"
                                    href="{{ route('admin.users.edit', $user) }}"
                                >
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Belum ada akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <nav class="pagination" aria-label="Halaman daftar akun">
                <div>
                    @if ($users->previousPageUrl())
                        <a
                            class="btn btn-secondary"
                            href="{{ $users->previousPageUrl() }}"
                        >
                            Sebelumnya
                        </a>
                    @endif
                </div>

                <span>
                    Halaman {{ $users->currentPage() }}
                    dari {{ $users->lastPage() }}
                </span>

                <div>
                    @if ($users->nextPageUrl())
                        <a
                            class="btn btn-secondary"
                            href="{{ $users->nextPageUrl() }}"
                        >
                            Berikutnya
                        </a>
                    @endif
                </div>
            </nav>
        @endif

        <div style="margin-top: 24px;">
            <a href="{{ route('admin.dashboard') }}">
                Kembali ke dashboard
            </a>
        </div>
    </section>
@endsection