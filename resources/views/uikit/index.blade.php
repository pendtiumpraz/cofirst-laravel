@extends('uikit.layout')

{{--
    Living style guide. Each partial is one chapter; keep them under 500 lines.
    Code snippets live in @php nowdocs so Blade does not compile the example tags.
--}}

@section('content')
    <x-ui.page-header
        eyebrow="Sistem desain"
        subtitle="Satu kontrak token (--cf-*) untuk dua versi tampilan. Semua komponen di bawah dirender dari Blade yang sama; ganti versi, mode warna, kerapatan, dan gaya KPI dari bilah atas."
        :breadcrumbs="[['label' => 'Admin', 'url' => route('admin.dashboard')], ['label' => 'UI Kit']]">
        <x-slot:title>Komponen yang <em>tenang</em> dan konsisten.</x-slot:title>
        <x-slot:meta>
            <span>Versi {{ strtoupper($preview['theme']) }}</span>
            <span aria-hidden="true">·</span>
            <span>{{ ['light' => 'Terang', 'dark' => 'Gelap', 'system' => 'Ikuti sistem'][$preview['mode']] }}</span>
            <span aria-hidden="true">·</span>
            <span>{{ $preview['density'] === 'compact' ? 'Rapat' : 'Lega' }}</span>
        </x-slot:meta>
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="exclamation-triangle" :href="request()->fullUrlWithQuery(['errors' => request()->boolean('errors') ? null : 1])">
                {{ request()->boolean('errors') ? 'Sembunyikan galat' : 'Tampilkan status galat' }}
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @include('uikit.partials.tokens')
    @include('uikit.partials.typography')
    @include('uikit.partials.actions')
    @include('uikit.partials.feedback')
    @include('uikit.partials.layout')
    @include('uikit.partials.forms')
    @include('uikit.partials.data')
    @include('uikit.partials.overlays')
    @include('uikit.partials.legacy')
@endsection
