{{--
    Sticky save bar of the settings form: one compact row on phones, sits above the
    mobile bottom tab bar (html[data-mobile-nav=tabs], below lg) and shows whether
    there are unsaved changes.
--}}
<div class="sticky bottom-0 z-20 -mx-4 mt-6 border-t border-line bg-canvas/95 px-4 py-3 backdrop-blur-sm sm:-mx-0 sm:rounded-lg sm:border sm:bg-surface/95 sm:px-5 max-lg:[[data-mobile-nav=tabs]_&]:bottom-[calc(var(--cf-tabbar-h)+env(safe-area-inset-bottom))]">
    <div class="flex items-center justify-between gap-3">
        <p class="min-w-0 text-sm" aria-live="polite">
            <span x-show="! dirty" class="flex items-center gap-2 text-fg-muted">
                <x-ui.icon name="m-check-circle" class="size-4 text-success" />
                <span class="sm:hidden">Tersimpan</span>
                <span class="hidden sm:inline">Tidak ada perubahan yang belum disimpan.</span>
            </span>
            <span x-show="dirty" x-cloak class="flex items-center gap-2 font-medium text-fg">
                <span class="size-2 shrink-0 rounded-full bg-warning-solid" aria-hidden="true"></span>
                <span class="sm:hidden">Belum disimpan</span>
                <span class="hidden sm:inline">Ada perubahan yang belum disimpan.</span>
            </span>
        </p>
        <div class="flex shrink-0 items-center gap-2">
            <x-ui.button variant="ghost" :href="route('admin.settings.edit', $group['key'])" x-show="dirty" x-cloak class="hidden sm:inline-flex">
                Batalkan
            </x-ui.button>
            <x-ui.button type="submit" icon="check" loading-when="saving" loading-text="Menyimpan…">
                Simpan<span class="hidden sm:inline">&nbsp;perubahan</span>
            </x-ui.button>
        </div>
    </div>
</div>
