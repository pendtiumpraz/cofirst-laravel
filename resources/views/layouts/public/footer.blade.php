{{--
    Public footer: brand + description + socials, explore links, contact details from
    setting('general.*'), privacy & legal links (guarded by Route::has via LegalLinks),
    the DPO contact and the data controller line required by UU PDP.
--}}
@php
    $legal = \App\Support\Navigation\LegalLinks::all();
    $socials = collect([
        'Instagram' => setting('general.social_instagram'),
        'YouTube' => setting('general.social_youtube'),
        'TikTok' => setting('general.social_tiktok'),
        'LinkedIn' => setting('general.social_linkedin'),
    ])->filter();
    $explore = collect([['Beranda', 'landing'], ['Program', 'courses'], ['Tentang kami', 'about'], ['Kontak', 'contact'], ['Masuk', 'login']])
        ->filter(fn ($l) => Route::has($l[1]));
    $email = setting('general.contact_email');
    $phone = setting('general.contact_phone');
    $whatsapp = preg_replace('/\D+/', '', (string) setting('general.whatsapp', ''));
    $dpoEmail = setting('privacy.dpo_email');
    $heading = 'eyebrow';
    $linkClass = 'rounded-sm text-fg-muted underline-offset-4 hover:text-fg hover:underline';
@endphp
<footer class="border-t border-line bg-surface-2 pb-[env(safe-area-inset-bottom)]">
    <div class="mx-auto grid max-w-landing gap-10 px-4 py-14 sm:px-6 md:grid-cols-2 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <x-brand.logo size="md" :descriptor="true" />
            @if ($description = setting('general.description'))
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-fg-muted">{{ $description }}</p>
            @endif
            @if ($socials->isNotEmpty())
                <ul role="list" class="mt-5 flex flex-wrap gap-2">
                    @foreach ($socials as $name => $url)
                        <li>
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer me"
                               class="inline-flex min-h-9 items-center gap-1.5 rounded-md border border-line bg-surface px-3 text-xs font-medium text-fg-muted hover:border-line-strong hover:text-fg">
                                {{ $name }}
                                <x-heroicon-m-arrow-up-right class="size-3.5" aria-hidden="true" />
                                <span class="sr-only">(tab baru)</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <nav aria-labelledby="cf-footer-explore" class="lg:col-span-2">
            <h2 id="cf-footer-explore" class="{{ $heading }}">Jelajahi</h2>
            <ul role="list" class="mt-4 space-y-2.5 text-sm">
                @foreach ($explore as [$label, $route])
                    <li><a href="{{ route($route) }}" class="{{ $linkClass }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        <div class="lg:col-span-3">
            <h2 class="{{ $heading }}">Kontak</h2>
            <address class="mt-4 space-y-2.5 text-sm not-italic text-fg-muted">
                @if ($address = setting('general.address'))<p>{{ $address }}</p>@endif
                @if ($email)<p><a href="mailto:{{ $email }}" class="{{ $linkClass }}">{{ $email }}</a></p>@endif
                @if ($phone)<p><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="{{ $linkClass }}">{{ $phone }}</a></p>@endif
                @if (strlen($whatsapp) >= 8)<p><a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="{{ $linkClass }}">WhatsApp +{{ $whatsapp }}</a></p>@endif
                @if ($hours = setting('general.business_hours'))<p class="text-fg-subtle">{{ $hours }}</p>@endif
                @if ($maps = setting('general.maps_url'))<p><a href="{{ $maps }}" target="_blank" rel="noopener noreferrer" class="{{ $linkClass }}">Lihat di peta<span class="sr-only"> (tab baru)</span></a></p>@endif
            </address>
        </div>

        <nav aria-labelledby="cf-footer-legal" class="lg:col-span-3">
            <h2 id="cf-footer-legal" class="{{ $heading }}">Privasi &amp; hukum</h2>
            <ul role="list" class="mt-4 space-y-2.5 text-sm">
                @foreach ($legal as $link)
                    <li><a href="{{ $link['url'] }}" class="{{ $linkClass }}">{{ $link['label'] }}</a></li>
                @endforeach
                @if (view()->exists('privacy.partials.cookie-banner'))
                    <li>
                        <button type="button" x-data @click="$dispatch('cf-cookie-settings')" class="{{ $linkClass }} text-left">Pengaturan cookie</button>
                    </li>
                @endif
            </ul>
            @if ($dpoEmail)
                <p class="mt-5 text-sm text-fg-muted">
                    Petugas Pelindungan Data Pribadi:<br>
                    <a href="mailto:{{ $dpoEmail }}" class="{{ $linkClass }}">{{ $dpoEmail }}</a>
                </p>
            @endif
        </nav>
    </div>

    <div class="border-t border-line">
        {{-- Reason: keep the legal line clear of the floating WhatsApp button. --}}
        <div @class([
            'mx-auto flex max-w-landing flex-col gap-2 px-4 py-6 text-xs text-fg-subtle sm:flex-row sm:items-center sm:justify-between sm:px-6',
            'pb-24 lg:pb-6 lg:pr-44' => setting('integrations.whatsapp_button') && strlen($whatsapp) >= 8,
        ])>
            <p>© {{ now()->year }} {{ setting('general.legal_name', 'Coding First Indonesia') }}. Hak cipta dilindungi.</p>
            <p>Pengendali Data Pribadi: {{ setting('privacy.controller_name', setting('general.legal_name')) }} · sesuai UU No. 27 Tahun 2022</p>
        </div>
    </div>
</footer>
