{{--
    Legacy collection table (props unchanged: id, columns, data, searchable, sortable, paginate,
    perPage, actions, emptyMessage, showNo), restyled to the design system.
    Prefer <x-ui.table> + <x-ui.th> + <x-ui.filter-bar> with server-side queries.

    Safety: a column `render` callback's output is escaped unless it returns an Htmlable
    (e.g. new HtmlString(...) or a rendered view) — prevents XSS from user data.
--}}
@props([
    'id' => 'data-table-' . uniqid(),
    'columns' => [],
    'data' => collect(),
    'searchable' => true,
    'sortable' => true,
    'paginate' => true,
    'perPage' => 10,
    'actions' => null,
    'emptyMessage' => 'Belum ada data.',
    'showNo' => true,
])

@php
    $sortColumn = (string) request()->query('sort', '');
    $sortDirection = request()->query('direction') === 'desc' ? 'desc' : 'asc';
    $search = trim((string) request()->query('search', ''));
    $isPaginator = $data instanceof \Illuminate\Contracts\Pagination\Paginator;
    $rows = $isPaginator ? collect($data->items()) : collect($data);

    // Search (only for plain collections; paginators must be filtered in the query).
    if ($searchable && $search !== '' && ! $isPaginator) {
        $rows = $rows->filter(function ($item) use ($columns, $search) {
            foreach ($columns as $column) {
                if (isset($column['searchable']) && $column['searchable'] === false) {
                    continue;
                }
                $value = data_get($item, $column['key']);
                if (is_scalar($value) && stripos((string) $value, $search) !== false) {
                    return true;
                }
            }

            return false;
        });
    }

    if ($sortable && $sortColumn !== '' && ! $isPaginator) {
        $rows = $rows->sortBy(fn ($item) => data_get($item, $sortColumn), SORT_NATURAL | SORT_FLAG_CASE, $sortDirection === 'desc');
    }

    if ($isPaginator) {
        $paginatedData = $data;
        $pageRows = $rows;
    } elseif ($paginate) {
        $page = max(1, (int) request()->query('page', 1));
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );
        $pageRows = collect($paginatedData->items());
    } else {
        $paginatedData = null;
        $pageRows = $rows->values();
    }

    $offset = $paginatedData && method_exists($paginatedData, 'firstItem') ? max(1, (int) $paginatedData->firstItem()) : 1;
    $colspan = count($columns) + ($showNo ? 1 : 0) + ($actions ? 1 : 0);

    $cell = function ($value) {
        return $value instanceof \Illuminate\Contracts\Support\Htmlable ? $value : e((string) $value);
    };
@endphp

<div id="{{ $id }}" class="space-y-4">
    @if ($searchable)
        <x-ui.filter-bar placeholder="Cari…" />
    @endif

    <div class="table-wrap">
        <div class="table-scroll" tabindex="0" role="region" aria-label="Tabel data">
            <table class="table-cf" data-mobile="stack">
                <thead>
                    <tr>
                        @if ($showNo)
                            <th scope="col" class="num w-12">No</th>
                        @endif
                        @foreach ($columns as $column)
                            @if ($sortable && (! isset($column['sortable']) || $column['sortable'] !== false))
                                <x-ui.th :sort="$column['key']">{{ $column['label'] }}</x-ui.th>
                            @else
                                <th scope="col">{{ $column['label'] }}</th>
                            @endif
                        @endforeach
                        @if ($actions)
                            <th scope="col" class="text-right"><span class="sr-only">Aksi</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pageRows as $index => $item)
                        <tr>
                            @if ($showNo)
                                <td class="num text-fg-subtle" data-label="No">{{ $offset + $loop->index }}</td>
                            @endif
                            @foreach ($columns as $column)
                                <td data-label="{{ $column['label'] }}">
                                    @if (isset($column['render']))
                                        {!! $cell($column['render']($item)) !!}
                                    @else
                                        {{ data_get($item, $column['key']) }}
                                    @endif
                                </td>
                            @endforeach
                            @if ($actions)
                                <td class="cell-actions">{!! $cell($actions($item)) !!}</td>
                            @endif
                        </tr>
                    @empty
                        <tr data-empty>
                            <td colspan="{{ $colspan }}">
                                <x-ui.empty-state :title="$emptyMessage" :description="$search !== '' ? 'Tidak ditemukan hasil untuk “'.$search.'”.' : null" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($paginatedData && $paginatedData->hasPages())
            <div class="table-footer">
                {{ $paginatedData->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
