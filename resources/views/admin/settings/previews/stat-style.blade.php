{{-- KPI styles: one hairline-divided strip vs separate cards. --}}
@php
    $stats = [['312', 'Siswa'], ['94%', 'Hadir'], ['48 jt', 'Pendapatan']];
@endphp
<span class="flex h-28 flex-col justify-center gap-2 rounded-md border border-line bg-canvas p-3">
    <span class="h-1.5 w-16 rounded-full bg-fg/30"></span>
    @if ($option === 'cards')
        <span class="grid grid-cols-3 gap-1.5">
            @foreach ($stats as [$number, $label])
                <span class="min-w-0 rounded-md border border-line bg-surface px-2 py-1.5 shadow-1">
                    <span class="block truncate text-[8px] text-fg-subtle">{{ $label }}</span>
                    <span class="font-num block text-sm leading-tight text-fg">{{ $number }}</span>
                </span>
            @endforeach
        </span>
    @else
        <span class="grid grid-cols-3 divide-x divide-line rounded-md border border-line bg-surface">
            @foreach ($stats as [$number, $label])
                <span class="min-w-0 px-2 py-1.5">
                    <span class="block truncate text-[8px] text-fg-subtle">{{ $label }}</span>
                    <span class="font-num block text-sm leading-tight text-fg">{{ $number }}</span>
                </span>
            @endforeach
        </span>
    @endif
</span>
