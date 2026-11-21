@extends('layouts.app')

@section('title', 'Kelola fasilitas | SIPERU')

@section('content')
    <style>
    .facility-page .facility-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 12px;
    }

    .facility-page h1 {
        margin: 0;
    }

    .facility-page .facility-description {
        margin-bottom: 20px;
    }

    .facility-page .table-wrapper {
        overflow-x: auto;
    }

    .facility-page table {
        width: 100%;
    }

    .facility-page th,
    .facility-page td {
        vertical-align: middle;
    }

    .facility-page .number-cell {
        text-align: center;
        font-variant-numeric: tabular-nums;
    }

    .facility-page .facility-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .facility-page .usage-note,
    .facility-page .empty-state {
        color: #64748b;
        font-size: 0.875rem;
    }

    .facility-page .empty-state {
        padding: 24px;
        text-align: center;
    }

    /* Konfirmasi hapus */
    .facility-delete-dialog {
        box-sizing: border-box;
        width: min(340px, calc(100% - 32px));
        max-height: calc(100dvh - 32px);
        margin: auto;
        padding: 20px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        color: #20314d;
        box-shadow: 0 8px 24px rgb(15 23 42 / 12%);
    }

    .facility-delete-dialog h2 {
        margin: 0 0 8px;
        font-size: 1.05rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .facility-delete-dialog p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .facility-delete-dialog form {
        margin: 0;
    }

    .facility-delete-dialog .delete-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 18px;
    }

    .facility-delete-dialog .btn {
        margin: 0;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
    }
</style>

    <section class="card facility-page">
        <div class="facility-toolbar">
            <h1>Kelola Fasilitas</h1>

            <a
                class="btn"
                href="{{ route('admin.facilities.create') }}"
            >
                Tambah Fasilitas
            </a>
        </div>

        <p class="muted facility-description">
            Kelola jenis fasilitas, total stok, dan jumlah yang ditempatkan
            di ruang kampus.
        </p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama fasilitas</th>
                        <th scope="col" class="number-cell">Total stok</th>
                        <th scope="col" class="number-cell">Dialokasikan</th>
                        <th scope="col" class="number-cell">Sisa tersedia</th>
                        <th scope="col">Digunakan oleh</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($facilities as $facility)
                        @php
                            $allocated = (int) $facility->allocated_stock;
                            $available = (int) $facility->total_stock - $allocated;
                            $roomsCount = (int) $facility->rooms_count;
                        @endphp

                        <tr>
                            <td>
                                {{ $facilities->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <strong>{{ $facility->name }}</strong>
                            </td>

                            <td class="number-cell">
                                {{ number_format($facility->total_stock, 0, ',', '.') }}
                            </td>

                            <td class="number-cell">
                                {{ number_format($allocated, 0, ',', '.') }}
                            </td>

                            <td class="number-cell">
                                <span class="stock-badge {{ $available <= 0 ? 'empty' : '' }}">
                                    {{ number_format($available, 0, ',', '.') }}
                                </span>
                            </td>

                            <td>{{ $roomsCount }} ruang</td>

                            <td>
                                <div class="facility-actions">
                                    <a
                                        class="btn btn-secondary"
                                        href="{{ route('admin.facilities.edit', $facility) }}"
                                        aria-label="Edit fasilitas {{ $facility->name }}"
                                    >
                                        Edit
                                    </a>

                                    @if ($roomsCount === 0)
                                        <button
                                            class="btn btn-danger"
                                            type="button"
                                            data-delete-facility
                                            data-name="{{ $facility->name }}"
                                            data-url="{{ route('admin.facilities.destroy', $facility) }}"
                                            aria-haspopup="dialog"
                                            aria-controls="facility-delete-dialog"
                                            aria-label="Hapus fasilitas {{ $facility->name }}"
                                        >
                                            Hapus
                                        </button>
                                    @else
                                        <span class="usage-note">
                                            Masih digunakan
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                Belum ada fasilitas.
                                Klik Tambah fasilitas untuk membuat data pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="muted" style="margin-top: 16px;">
            Jumlah stok dihitung dalam unit. Fasilitas hanya dapat dihapus
            jika sudah tidak digunakan oleh ruang mana pun.
        </p>

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

    </section>

    <dialog
        id="facility-delete-dialog"
        class="facility-delete-dialog"
        aria-labelledby="facility-delete-title"
        aria-describedby="facility-delete-description"
    >
        <h2 id="facility-delete-title">Hapus fasilitas?</h2>

        <p id="facility-delete-description">
            Hapus <strong id="facility-delete-name"></strong>
            dari daftar fasilitas?
        </p>

        <form id="facility-delete-form" method="POST">
            @csrf
            @method('DELETE')

            <div class="delete-actions">
                <button
                    id="facility-delete-cancel"
                    class="btn btn-secondary"
                    type="button"
                    autofocus
                >
                    Batal
                </button>

                <button
                    id="facility-delete-submit"
                    class="btn btn-danger"
                    type="submit"
                >
                    Hapus
                </button>
            </div>
        </form>
    </dialog>

    <script>
        (() => {
            const dialog = document.getElementById('facility-delete-dialog');
            const form = document.getElementById('facility-delete-form');
            const name = document.getElementById('facility-delete-name');
            const cancelButton = document.getElementById('facility-delete-cancel');
            const submitButton = document.getElementById('facility-delete-submit');

            let triggerButton = null;
            let submitting = false;

            document.querySelectorAll('[data-delete-facility]')
                .forEach(button => {
                    button.addEventListener('click', () => {
                        triggerButton = button;
                        submitting = false;

                        name.textContent = button.dataset.name;
                        form.action = button.dataset.url;

                        submitButton.disabled = false;
                        submitButton.textContent = 'Ya, hapus fasilitas';
                        cancelButton.disabled = false;

                        dialog.showModal();
                        cancelButton.focus();
                    });
                });

            cancelButton.addEventListener('click', () => {
                if (!submitting) {
                    dialog.close();
                }
            });

            // Tombol Escape dapat menutup modal sebelum penghapusan dikirim.
            dialog.addEventListener('cancel', event => {
                if (submitting) {
                    event.preventDefault();
                }
            });

            dialog.addEventListener('close', () => {
                if (triggerButton) {
                    triggerButton.focus();
                }
            });

            form.addEventListener('submit', event => {
                if (submitting || !form.getAttribute('action')) {
                    event.preventDefault();
                    return;
                }

                submitting = true;
                submitButton.disabled = true;
                submitButton.textContent = 'Menghapus...';
                cancelButton.disabled = true;
            });
        })();
    </script>
@endsection