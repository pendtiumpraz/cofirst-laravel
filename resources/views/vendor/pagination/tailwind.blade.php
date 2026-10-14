{{--
    Default paginator view (Paginator::defaultView('pagination::tailwind')).
    'Menampilkan 1–20 dari 134' + page links. data-pagination-from feeds the legacy
    table-enhancements.js row numbering.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="pagination-cf"
        data-pagination-from="{{ $paginator->firstItem() ?? 0 }}">
        <p class="pagination-summary">
            @if ($paginator->firstItem())
                Menampilkan <strong>{{ number_format($paginator->firstItem(), 0, ',', '.') }}–{{ number_format($paginator->lastItem(), 0, ',', '.') }}</strong>
                dari <strong>{{ number_format($paginator->total(), 0, ',', '.') }}</strong>
            @else
                Menampilkan {{ $paginator->count() }} dari {{ number_format($paginator->total(), 0, ',', '.') }}
            @endif
        </p>

        <div class="pagination-pages">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="page-link" aria-disabled="true" aria-label="Halaman sebelumnya">
                    <x-ui.icon name="chevron-left" />
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-link" aria-label="Halaman sebelumnya">
                    <x-ui.icon name="chevron-left" />
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @endif

            {{-- Compact position on phones --}}
            <span class="page-gap px-2 sm:hidden">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            {{-- Numbered links (tablet and up) --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="page-gap hidden sm:inline-flex" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-link hidden sm:inline-flex" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-link hidden sm:inline-flex" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-link" aria-label="Halaman berikutnya">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <x-ui.icon name="chevron-right" />
                </a>
            @else
                <span class="page-link" aria-disabled="true" aria-label="Halaman berikutnya">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <x-ui.icon name="chevron-right" />
                </span>
            @endif
        </div>
    </nav>
@endif
