@php
    use Illuminate\Support\Facades\Route;

    // Consent status from the privacy module when it is installed (UU PDP Pasal 20–25).
    $consents = collect();
    $consentService = 'App\\Services\\Privacy\\ConsentService';
    if (class_exists($consentService)) {
        try {
            $service = app($consentService);
            if (method_exists($service, 'purposesFor') && method_exists($service, 'hasConsent')) {
                $consents = collect($service->purposesFor($user))->map(fn ($purpose) => [
                    'label' => data_get($purpose, 'name') ?? data_get($purpose, 'label') ?? data_get($purpose, 'key'),
                    'required' => (bool) data_get($purpose, 'is_required', data_get($purpose, 'required', false)),
                    'granted' => (bool) $service->hasConsent($user, (string) data_get($purpose, 'key')),
                ])->filter(fn ($c) => filled($c['label']))->values();
            }
        } catch (\Throwable) {
            $consents = collect();
        }
    }

    $links = collect([
        ['route' => 'admin.privacy.consent-records.index', 'label' => 'Riwayat persetujuan', 'icon' => 'document-check'],
        ['route' => 'admin.privacy.dsr.index', 'label' => 'Permintaan subjek data', 'icon' => 'inbox-stack'],
        ['route' => 'admin.privacy.audit.index', 'label' => 'Log audit', 'icon' => 'clipboard-document-list'],
    ])->filter(fn ($link) => Route::has($link['route']))->values();
@endphp

<div class="grid grid-cols-1 gap-gutter lg:grid-cols-3">
    <x-ui.card title="Persetujuan" subtitle="Status persetujuan per tujuan pemrosesan." padding="none" class="lg:col-span-2">
        @if ($consents->isEmpty())
            <x-ui.empty-state icon="document-check" title="Belum ada data persetujuan."
                description="Persetujuan dicatat saat pendaftaran, melalui banner cookie, atau oleh orang tua/wali di Pusat Privasi." />
        @else
            <ul class="divide-y divide-line" role="list">
                @foreach ($consents as $consent)
                    <li class="flex items-center justify-between gap-3 px-card py-3">
                        <span class="text-sm text-fg">{{ $consent['label'] }}
                            @if ($consent['required'])<span class="text-xs text-fg-subtle">(wajib)</span>@endif
                        </span>
                        <x-ui.badge :tone="$consent['granted'] ? 'success' : 'neutral'" dot>{{ $consent['granted'] ? 'Disetujui' : 'Tidak/belum disetujui' }}</x-ui.badge>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    <x-ui.card title="Hak subjek data">
        <p class="text-sm text-fg-muted">
            Pemilik akun (atau orang tua/wali untuk anak) berhak mengakses, memperbaiki, mengunduh, membatasi atau meminta penghapusan datanya.
            Permintaan wajib ditanggapi paling lambat {{ (int) setting('privacy.dsr_sla_hours', 72) }} jam.
        </p>
        @if ($links->isNotEmpty())
            <ul class="mt-4 space-y-1" role="list">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ route($link['route']) }}" class="flex min-h-[44px] items-center gap-2 rounded-md px-2 text-sm font-medium text-brand-text hover:bg-surface-2">
                            <x-ui.icon :name="$link['icon']" size="18" />
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        <p class="mt-4 text-xs text-fg-subtle">Membuka halaman ini tercatat di log audit akses data pribadi.</p>
    </x-ui.card>
</div>
