@extends('layouts.app')

@section('title', 'Kelola Fasilitas | SIPERU')

@section('content')
    <section class="card">
        <div class="toolbar">
            <h1>Kelola Fasilitas</h1>

            <a class="btn" href="{{ route('admin.facilities.create') }}">
                + Tambah Fasilitas
            </a>
        </div>

        <p class="muted">
            Atur jenis fasilitas yang tersedia di ruang kampus.
        </p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama fasilitas</th>
                        <th>Digunakan oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($facilities as $facility)
                        <tr>
                            <td>
                                {{ $facilities->firstItem() + $loop->index }}
                            </td>

                            <td>{{ $facility->name }}</td>

                            <td>{{ $facility->rooms_count }} ruang</td>

                            <td>
                                <div class="actions">
                                    <a
                                        class="btn btn-secondary"
                                        href="{{ route('admin.facilities.edit', $facility) }}"
                                    >
                                        Edit
                                    </a>

                                    @if ($facility->rooms_count === 0)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.facilities.destroy', $facility) }}"
                                            onsubmit="return confirm('Hapus fasilitas ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                class="btn btn-danger"
                                                type="submit"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span class="muted">Sedang digunakan</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                Belum ada fasilitas. Tambahkan fasilitas pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($facilities->hasPages())
            <nav class="pagination" aria-label="Halaman daftar fasilitas">
                <div>
                    @if ($facilities->previousPageUrl())
                        <a
                            class="btn btn-secondary"
                            href="{{ $facilities->previousPageUrl() }}"
                        >
                            Sebelumnya
                        </a>
                    @endif
                </div>

                <span>
                    Halaman {{ $facilities->currentPage() }}
                    dari {{ $facilities->lastPage() }}
                </span>

                <div>
                    @if ($facilities->nextPageUrl())
                        <a
                            class="btn btn-secondary"
                            href="{{ $facilities->nextPageUrl() }}"
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