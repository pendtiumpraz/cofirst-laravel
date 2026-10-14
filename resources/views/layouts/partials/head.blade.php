{{--
    Shared <head> content for every layout (app, guest, public, offline).

    Variables (all optional):
      $pageTitle    page title; rendered as "Judul · Nama situs"
      $description  meta description (default setting('general.description'))
      $robots       e.g. "noindex, nofollow" for authenticated pages
      $withCsrf     false to omit the CSRF meta (anonymous cacheable pages such as /offline)
      $social       true to add Open Graph tags (public pages)
--}}
@php
    $ui = \App\Support\Navigation\Appearance::state();
    $colors = \App\Support\Navigation\Appearance::colors($ui['theme']);
    $nonce = \App\Support\Navigation\Appearance::nonceAttribute();
    $siteName = (string) setting('general.site_name', 'Coding First');
    $pageTitle = isset($pageTitle) && is_string($pageTitle) && trim($pageTitle) !== '' ? trim($pageTitle) : null;
    $fullTitle = $pageTitle && $pageTitle !== $siteName ? $pageTitle.' · '.$siteName : $siteName;
    $metaDescription = isset($description) && is_string($description) && $description !== '' ? $description : setting('general.description');
    $brand = 'brand/'.$ui['logo'].'/';
    $pwaEnabled = (bool) setting('pwa.enabled') && Route::has('pwa.manifest');
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@if ($withCsrf ?? true)
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endif
<title>{{ $fullTitle }}</title>
@if ($metaDescription)
    <meta name="description" content="{{ $metaDescription }}">
@endif
@if (! empty($robots))
    <meta name="robots" content="{{ $robots }}">
@endif
<meta name="theme-color" content="{{ $colors['canvas'] }}" media="(prefers-color-scheme: light)" data-cf-theme-color="light">
<meta name="theme-color" content="{{ $colors['canvas_dark'] }}" media="(prefers-color-scheme: dark)" data-cf-theme-color="dark">
<link rel="icon" href="{{ asset($brand.'favicon.ico') }}" sizes="48x48">
<link rel="icon" href="{{ asset($brand.'favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset($brand.'apple-touch-icon.png') }}">
@if ($pwaEnabled)
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="application-name" content="{{ setting('pwa.name') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ setting('pwa.short_name') }}">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
@endif
@if (! empty($social))
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    @if ($metaDescription)<meta property="og:description" content="{{ $metaDescription }}">@endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset($brand.'og-image.png') }}">
    <meta property="og:locale" content="id_ID">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ url()->current() }}">
@endif
<style{!! $nonce !!}>[x-cloak]{display:none!important}</style>
{{-- Colour mode before first paint: user preference → device choice → default (contract: resources/js/color-mode.js). --}}
<x-ui.color-mode-script />
{{-- Desktop sidebar rail state before first paint (device preference, set only by the toggle). --}}
<script{!! $nonce !!}>try { if (window.localStorage.getItem('cf-sidebar') === 'collapsed') { document.documentElement.setAttribute('data-sidebar', 'collapsed'); } } catch (e) {}</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@include('pwa.partials.register')
@stack('styles')
