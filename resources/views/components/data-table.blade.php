@props([
    'id' => 'data-table-' . uniqid(),
    'columns' => [],
    'data' => collect(),
    'searchable' => true,
    'sortable' => true,
    'paginate' => true,
    'perPage' => 10,
    'actions' => null,
    'emptyMessage' => 'No data available',
    'showNo' => true,
])

@php
    $sortColumn = request()->get('sort', '');
    $sortDirection = request()->get('direction', 'asc');
    $search = request()->get('search', '');
    
    // Apply search
    if ($searchable && $search) {
        $data = $data->filter(function ($item) use ($columns, $search) {
            foreach ($columns as $column) {
                if (isset($column['searchable']) && $column['searchable'] === false) {
                    continue;
                }
                $value = data_get($item, $column['key']);
                if (stripos($value, $search) !== false) {
                    return true;
                }
            }
            return false;
        });
    }
    
    // Apply sorting
    if ($sortable && $sortColumn) {
        $data = $data->sortBy(function ($item) use ($sortColumn) {
            return data_get($item, $sortColumn);
        }, SORT_REGULAR, $sortDirection === 'desc');
    }
    
    // Apply pagination
    if ($paginate && $data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        $paginatedData = $data;
    } elseif ($paginate) {
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $data->forPage(request()->get('page', 1), $perPage),
            $data->count(),
            $perPage,
            request()->get('page', 1),
            ['path' => request()->url()]
        );
    } else {
        $paginatedData = $data;
    }
@endphp

<div class="data-table-container">
    @if($searchable)
    <div class="mb-4">
        <form method="GET" action="{{ request()->url() }}" class="flex gap-2">
            @foreach(request()->except(['search', 'page']) as $key => $value)
                @if(is_array($value))
                    @foreach($value as $arrayKey => $arrayValue)
                        <input type="hidden" name="{{ $key }}[{{ $arrayKey }}]" value="{{ $arrayValue }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            
            <div class="relative flex-1 max-w-sm">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Search
            </button>
            
            @if($search)
                <a href="{{ request()->url() }}?{{ http_build_query(request()->except(['search', 'page'])) }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Clear
                </a>
            @endif
        </form>
    </div>
    @endif
    
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @if($showNo)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No
                        </th>
                    @endif
                    
                    @foreach($columns as $column)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            @if($sortable && (!isset($column['sortable']) || $column['sortable'] !== false))
                                <a href="{{ request()->fullUrlWithQuery([
                                    'sort' => $column['key'],
                                    'direction' => $sortColumn === $column['key'] && $sortDirection === 'asc' ? 'desc' : 'asc',
                                    'page' => 1
                                ]) }}" class="group inline-flex items-center">
                                    {{ $column['label'] }}
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible {{ $sortColumn === $column['key'] ? 'visible' : 'invisible' }}">
                                        @if($sortColumn === $column['key'])
                                            @if($sortDirection === 'asc')
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </a>
                            @else
                                {{ $column['label'] }}
                            @endif
                        </th>
                    @endforeach
                    
                    @if($actions)
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($paginate ? $paginatedData->items() : $paginatedData as $index => $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        @if($showNo)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $paginate ? ($paginatedData->currentPage() - 1) * $paginatedData->perPage() + $index + 1 : $index + 1 }}
                            </td>
                        @endif
                        
                        @foreach($columns as $column)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if(isset($column['render']))
                                    {!! $column['render']($item) !!}
                                @else
                                    {{ data_get($item, $column['key']) }}
                                @endif
                            </td>
                        @endforeach
                        
                        @if($actions)
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                {{ $actions($item) }}
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + ($showNo ? 1 : 0) + ($actions ? 1 : 0) }}" 
                            class="px-6 py-4 text-center text-gray-500">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($paginate && $paginatedData->hasPages())
        <div class="mt-4">
            {{ $paginatedData->appends(request()->query())->links() }}
        </div>
    @endif
</div>