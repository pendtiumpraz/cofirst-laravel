{{-- Simple paginator view (Paginator::defaultSimpleView('pagination::simple-tailwind')). --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="pagination-cf"
        data-pagination-from="{{ $paginator->firstItem() ?? 0 }}">
        <p class="pagination-summary">
            Halaman <strong>{{ $paginator->currentPage() }}</strong>
        </p>

        <div class="pagination-pages">
            @if ($paginator->onFirstPage())
                <span class="page-link" aria-disabled="true">
                    <x-ui.icon name="chevron-left" />
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-link">
                    <x-ui.icon name="chevron-left" />
                    <span>Sebelumnya</span>
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-link">
                    <span>Berikutnya</span>
                    <x-ui.icon name="chevron-right" />
                </a>
            @else
                <span class="page-link" aria-disabled="true">
                    <span>Berikutnya</span>
                    <x-ui.icon name="chevron-right" />
                </span>
            @endif
        </div>
    </nav>
@endif
