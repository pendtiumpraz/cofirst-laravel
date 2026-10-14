{{--
    Command palette (Ctrl/⌘ K, or the search buttons): jump to any page of the menu.
    Combobox pattern: arrow keys move, Enter opens, Esc closes.
--}}
@php
    // Reason: the first menu group has no label (Beranda, Pengguna …) — call it 'Utama', not 'Akun'.
    $paletteItems = collect(\App\Support\Navigation\Navigation::flatten($sections))
        ->map(fn ($item) => ['label' => $item['label'], 'section' => $item['section'] ?? 'Utama', 'url' => $item['url']])
        ->merge(collect($account)->map(fn ($item) => ['label' => $item['label'], 'section' => 'Akun', 'url' => $item['url']]))
        ->unique('url')
        ->values();
@endphp
<div id="cf-palette" x-data="cfPalette(@js($paletteItems))" x-show="open" x-cloak @cf-palette-open.window="show()"
     @keydown.escape.prevent.stop="hide()" @keydown.tab="trap($event)"
     class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="cf-palette-label">
    <div class="absolute inset-0 bg-canvas/75" @click="hide()" aria-hidden="true" x-show="open" x-transition.opacity.duration.150ms></div>

    <div x-ref="panel" x-show="open" x-transition.opacity.duration.150ms
         class="relative mx-auto mt-[max(4rem,12vh)] w-[min(40rem,calc(100%-2rem))] overflow-hidden rounded-xl border border-line-strong bg-surface shadow-3">
        <div class="flex items-center gap-3 border-b border-line px-4">
            <x-heroicon-o-magnifying-glass class="size-5 shrink-0 text-fg-subtle" aria-hidden="true" />
            <label id="cf-palette-label" for="cf-palette-input" class="sr-only">Cari halaman</label>
            <input id="cf-palette-input" x-ref="initial" x-model="q" type="text" autocomplete="off" spellcheck="false"
                   role="combobox" aria-expanded="true" aria-controls="cf-palette-list" aria-autocomplete="list"
                   :aria-activedescendant="results.length ? 'cf-palette-opt-' + active : null"
                   @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter.prevent="go()"
                   placeholder="Cari halaman, mis. jadwal atau laporan…"
                   class="h-14 w-full border-0 bg-transparent p-0 text-base text-fg placeholder:text-fg-subtle focus:outline-none focus:ring-0">
            <kbd class="kbd hidden sm:inline-flex">Esc</kbd>
        </div>
        <ul id="cf-palette-list" role="listbox" aria-label="Hasil" class="max-h-[min(60vh,28rem)] overflow-y-auto p-2">
            <template x-for="(item, i) in results" :key="item.url">
                <li role="option" :id="'cf-palette-opt-' + i" :aria-selected="(i === active).toString()">
                    <a :href="item.url" @mousemove="active = i" tabindex="-1"
                       class="flex min-h-11 items-center justify-between gap-4 rounded-md px-3 text-sm"
                       x-bind:class="i === active ? 'bg-surface-2 text-fg' : 'text-fg-muted'">
                        <span class="truncate font-medium" x-text="item.label"></span>
                        <span class="shrink-0 text-xs text-fg-subtle" x-text="item.section"></span>
                    </a>
                </li>
            </template>
            <li x-show="results.length === 0" class="px-3 py-8 text-center text-sm text-fg-subtle">Tidak ada halaman yang cocok.</li>
        </ul>
    </div>
</div>
