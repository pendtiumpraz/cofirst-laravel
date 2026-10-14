{{-- schema.org EducationalOrganization (no personal data). --}}
@php
    $logo = 'brand/'.\App\Support\Navigation\Appearance::state()['logo'].'/icon-512.png';
    $sameAs = array_values(array_filter([
        setting('general.social_instagram'), setting('general.social_youtube'),
        setting('general.social_tiktok'), setting('general.social_linkedin'),
    ]));
    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => setting('general.site_name'),
        'legalName' => setting('general.legal_name'),
        'description' => setting('general.description'),
        'url' => url('/'),
        'logo' => asset($logo),
        'email' => setting('general.contact_email'),
        'telephone' => setting('general.contact_phone') ?: null,
        'address' => setting('general.address') ?: null,
        'sameAs' => $sameAs ?: null,
    ]);
@endphp
<script type="application/ld+json"{!! \App\Support\Navigation\Appearance::nonceAttribute() !!}>{!! json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
