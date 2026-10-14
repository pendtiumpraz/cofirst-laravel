{{--
    System-wide strips: setting('system.maintenance_notice') (always shown) and
    setting('system.dashboard_announcement') (dismissible on this device until the text changes).
--}}
@php
    $maintenance = trim((string) setting('system.maintenance_notice', ''));
    $announcement = trim((string) setting('system.dashboard_announcement', ''));
@endphp
@if ($maintenance !== '')
    <div class="border-b border-line bg-warning-soft text-warning" role="status">
        <div class="mx-auto flex max-w-app items-start gap-3 px-4 py-2.5 text-sm sm:px-6 lg:px-8">
            <x-heroicon-o-wrench-screwdriver class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
            <p><span class="font-semibold">Pemeliharaan terjadwal:</span> {{ $maintenance }}</p>
        </div>
    </div>
@endif
@if ($announcement !== '')
    <div x-data="cfNotice(@js(md5($announcement)))" x-show="visible" class="border-b border-line bg-info-soft text-info" role="region" aria-label="Pengumuman">
        <div class="mx-auto flex max-w-app items-start gap-3 px-4 py-2.5 text-sm sm:px-6 lg:px-8">
            <x-heroicon-o-megaphone class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
            <p class="flex-1">{{ $announcement }}</p>
            <button type="button" @click="dismiss()" class="-my-1.5 -mr-2 inline-flex size-9 shrink-0 items-center justify-center rounded-md hover:bg-surface/40">
                <x-heroicon-o-x-mark class="size-5" aria-hidden="true" />
                <span class="sr-only">Tutup pengumuman</span>
            </button>
        </div>
    </div>
@endif
