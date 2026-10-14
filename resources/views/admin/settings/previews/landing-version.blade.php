{{-- Landing page wireframes: A "Majalah sekolah" (editorial chapters + seal), B "Halaman produk" (code window + metric strip). --}}
<span class="flex h-28 flex-col gap-1.5 overflow-hidden rounded-md border border-line bg-canvas p-2.5">
    <span class="flex items-center justify-between border-b border-line pb-1">
        <span class="h-1.5 w-8 rounded-full bg-fg/40"></span>
        <span class="flex gap-1">
            <span class="h-1 w-3 rounded-full bg-fg/15"></span>
            <span class="h-1 w-3 rounded-full bg-fg/15"></span>
            <span class="h-1 w-3 rounded-full bg-fg/15"></span>
        </span>
    </span>

    @if ($option === 'b')
        <span class="flex min-h-0 flex-1 gap-2">
            <span class="flex flex-1 flex-col justify-center gap-1">
                <span class="h-2 w-11/12 rounded-full bg-fg/60"></span>
                <span class="h-2 w-2/3 rounded-full bg-fg/60"></span>
                <span class="h-1 w-4/5 rounded-full bg-fg/15"></span>
                <span class="mt-0.5 inline-flex h-3.5 w-14 items-center rounded-sm bg-brand px-1 font-mono text-[7px] leading-none text-brand-fg">&gt;_ mulai</span>
            </span>
            <span class="flex w-[42%] flex-col gap-1 rounded-md bg-code p-1.5">
                <span class="flex gap-0.5">
                    <span class="size-1 rounded-full bg-code-fg/40"></span>
                    <span class="size-1 rounded-full bg-code-fg/40"></span>
                    <span class="size-1 rounded-full bg-code-fg/40"></span>
                </span>
                <span class="h-1 w-3/4 rounded-full bg-code-fg/50"></span>
                <span class="h-1 w-1/2 rounded-full bg-code-fg/30"></span>
                <span class="flex items-center gap-0.5">
                    <span class="h-1 w-2/5 rounded-full bg-code-fg/50"></span>
                    <span class="h-2 w-0.5 bg-accent-solid"></span>
                </span>
            </span>
        </span>
        <span class="grid grid-cols-3 divide-x divide-line rounded-sm border border-line bg-surface">
            @foreach (['1.200+', '12', '4,9'] as $number)
                <span class="font-num px-1 text-center text-[9px] leading-4 text-fg">{{ $number }}</span>
            @endforeach
        </span>
    @else
        <span class="flex min-h-0 flex-1 gap-2">
            <span class="flex flex-1 flex-col gap-1">
                <span class="font-mono text-[7px] uppercase tracking-widest text-fg-subtle">01 / Metode</span>
                <span class="cf-display font-display text-[13px] font-title leading-tight text-fg">Belajar <em>coding</em> dengan tenang</span>
                <span class="h-1 w-4/5 rounded-full bg-fg/15"></span>
            </span>
            <span class="grid w-9 shrink-0 place-items-center">
                <span class="size-7 -rotate-6 rounded-md bg-accent-solid"></span>
            </span>
        </span>
        <span class="flex items-center gap-2 border-t border-line pt-1">
            <span class="font-mono text-[7px] text-fg-subtle">02</span>
            <span class="h-1 flex-1 rounded-full bg-fg/15"></span>
            <span class="font-mono text-[7px] text-fg-subtle">03</span>
            <span class="h-1 flex-1 rounded-full bg-fg/15"></span>
        </span>
    @endif
</span>
