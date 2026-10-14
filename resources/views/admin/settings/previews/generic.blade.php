{{-- Fallback thumbnail for choice fields without a dedicated preview (e.g. keys added later). --}}
<span class="grid h-28 place-items-center rounded-md border border-line bg-canvas">
    <span class="font-display text-3xl font-title tracking-display text-fg-subtle">{{ mb_strtoupper(mb_substr((string) $option, 0, 2)) }}</span>
</span>
