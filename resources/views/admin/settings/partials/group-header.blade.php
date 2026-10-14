{{--
    Group title, description, audit meta ("Terakhir diubah oleh … · tanggal") and
    "Kembalikan semua ke bawaan" (only when something is customised and the user may edit).
--}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex min-w-0 items-start gap-4">
        <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-brand-soft text-brand-soft-fg" aria-hidden="true">
            <x-ui.icon :name="$group['icon']" size="20" />
        </span>
        <div class="min-w-0">
            <h2 class="font-display text-card-title font-title tracking-display text-fg">{{ $group['label'] }}</h2>
            @if (filled($group['description']))
                <p class="mt-1 max-w-measure text-sm text-fg-muted">{{ $group['description'] }}</p>
            @endif
            <p class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-fg-subtle">
                @if ($lastChange)
                    <x-ui.icon name="m-clock" class="size-3.5" />
                    <span>
                        Terakhir diubah
                        <time datetime="{{ $lastChange->updated_at?->toIso8601String() }}">{{ $lastChange->updated_at?->timezone(config('app.timezone'))->translatedFormat('j M Y, H.i T') }}</time>
                        oleh <span class="font-medium text-fg-muted">{{ $lastChange->editor?->name ?? 'sistem' }}</span>
                    </span>
                    <span aria-hidden="true">·</span>
                    <span>{{ $customised }} dari {{ collect($sections)->sum(fn ($s) => count($s['fields'])) }} bidang disesuaikan</span>
                @else
                    <x-ui.icon name="m-check-circle" class="size-3.5" />
                    <span>Semua bidang masih memakai nilai bawaan.</span>
                @endif
            </p>
        </div>
    </div>

    @if ($canEdit && $customised > 0)
        <x-ui.button variant="ghost" size="sm" icon="arrow-uturn-left" type="submit" form="settings-reset"
            class="self-start"
            data-confirm="Semua bidang di {{ $group['label'] }} akan kembali ke nilai bawaan. Perubahan yang belum disimpan ikut hilang."
            data-confirm-title="Kembalikan semua ke bawaan?"
            data-confirm-ok="Kembalikan"
            data-confirm-tone="danger">
            Kembalikan semua ke bawaan
        </x-ui.button>
    @endif
</div>
