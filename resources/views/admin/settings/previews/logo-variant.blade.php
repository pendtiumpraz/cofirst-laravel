{{-- Logo lockups: A hanko, B caret, or both for "Ikuti tema". --}}
<span class="flex h-28 items-center justify-center gap-4 overflow-hidden rounded-md border border-line bg-canvas px-3">
    @if ($option === 'auto')
        <span data-theme="a" class="flex flex-col items-center gap-1.5">
            <x-brand.mark-a size="32" />
            <span class="text-[10px] font-semibold text-fg-subtle">Tema A</span>
        </span>
        <x-ui.icon name="arrows-right-left" class="size-4 text-fg-subtle" />
        <span data-theme="b" class="flex flex-col items-center gap-1.5">
            <x-brand.mark-b size="32" />
            <span class="text-[10px] font-semibold text-fg-subtle">Tema B</span>
        </span>
    @else
        <span data-theme="{{ $option === 'b' ? 'b' : 'a' }}" class="text-fg">
            <x-brand.lockup :variant="$option === 'b' ? 'b' : 'a'" size="sm" />
        </span>
    @endif
</span>
