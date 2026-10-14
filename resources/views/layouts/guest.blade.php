{{--
    Auth pages shell (<x-guest-layout title="…" heading="…" subheading="…">).
    setting('appearance.auth_layout'): 'split' = brand panel + form, 'centered' = card.
    Also usable with @extends('layouts.guest') + @section('title') / @section('content').
--}}
@php
    $ui = \App\Support\Navigation\Appearance::state();
    $pageTitle = (isset($title) && is_string($title) && trim($title) !== '' ? $title : null) ?? (trim($__env->yieldContent('title')) ?: null);
    $heading = isset($heading) && is_string($heading) ? $heading : null;
    $subheading = isset($subheading) && is_string($subheading) ? $subheading : null;
    $split = $ui['auth_layout'] === 'split';
    $home = Route::has('landing') ? route('landing') : url('/');
@endphp
<!DOCTYPE html>
<html {!! \App\Support\Navigation\Appearance::htmlAttributes() !!}>
<head>
    @include('layouts.partials.head', ['pageTitle' => $pageTitle])
</head>
<body class="min-h-dvh bg-canvas font-sans text-fg antialiased">
    @include('layouts.partials.skip-link')

    @if ($split)
        <div class="grid min-h-dvh lg:grid-cols-[minmax(0,5fr)_minmax(0,6fr)]">
            @include('layouts.guest.brand-panel', ['home' => $home, 'theme' => $ui['theme']])

            <div class="flex min-h-dvh flex-col">
                <header class="flex items-center justify-between px-4 pt-[max(1rem,env(safe-area-inset-top))] sm:px-8 lg:hidden">
                    <a href="{{ $home }}" class="inline-flex rounded-md"><x-brand.logo size="sm" /><span class="sr-only">— ke beranda</span></a>
                </header>
                {{-- Reason: on phones the form starts under the logo; centring it leaves an empty band above. --}}
                <main id="konten" tabindex="-1" class="flex flex-1 items-start justify-center px-4 pb-10 pt-8 focus:outline-none sm:items-center sm:px-8 sm:py-10">
                    <div class="w-full max-w-md">
                        @include('layouts.guest.heading')
                        {{ $slot ?? '' }}
                        @yield('content')
                    </div>
                </main>
                @include('layouts.guest.legal')
            </div>
        </div>
    @else
        <div class="flex min-h-dvh flex-col items-center px-4 pt-[max(2.5rem,env(safe-area-inset-top))] sm:px-6">
            {{-- Lockup + card sit on the optical centre of the canvas; the legal links stay at the bottom. --}}
            <div class="flex w-full flex-1 flex-col items-center sm:justify-center sm:py-10">
                <a href="{{ $home }}" class="inline-flex rounded-md"><x-brand.logo size="lg" layout="stacked" :descriptor="true" /><span class="sr-only">— ke beranda</span></a>
                <main id="konten" tabindex="-1" class="mt-8 w-full max-w-md focus:outline-none">
                    <div class="rounded-lg border border-line bg-surface p-6 sm:p-8">
                        @include('layouts.guest.heading')
                        {{ $slot ?? '' }}
                        @yield('content')
                    </div>
                </main>
            </div>
            @include('layouts.guest.legal')
        </div>
    @endif

    @includeIf('privacy.partials.cookie-banner')
    @stack('scripts')
</body>
</html>
