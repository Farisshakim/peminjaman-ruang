@if ($items->hasPages())
    <nav class="pagination" aria-label="Navigasi halaman">
        <div>
            @if ($items->previousPageUrl())
                <a
                    class="btn btn-secondary"
                    href="{{ $items->previousPageUrl() }}"
                >
                    Sebelumnya
                </a>
            @endif
        </div>

        <span>
            Halaman {{ $items->currentPage() }}
            dari {{ $items->lastPage() }}
        </span>

        <div>
            @if ($items->nextPageUrl())
                <a
                    class="btn btn-secondary"
                    href="{{ $items->nextPageUrl() }}"
                >
                    Berikutnya
                </a>
            @endif
        </div>
    </nav>
@endif
