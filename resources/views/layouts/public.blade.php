{{--
    Public website shell (<x-public-layout title="…" description="…" :transparent-header="true">).
    Also usable with @extends('layouts.public') + @section('title') / @section('content').
    Includes the privacy module's cookie banner when it exists, the optional WhatsApp button and
    the toast region (session flashes are shown as toasts — do not repeat them inline).
--}}
@php
    $pageTitle = (isset($title) && is_string($title) && trim($title) !== '' ? $title : null) ?? (trim($__env->yieldContent('title')) ?: null);
    $metaDescription = (isset($description) && is_string($description) && $description !== '' ? $description : null)
        ?? (trim($__env->yieldContent('description')) ?: null);
    $transparentHeader = isset($transparentHeader) && $transparentHeader === true;
    $announcement = trim((string) setting('landing.announcement', ''));
    $whatsapp = preg_replace('/\D+/', '', (string) setting('general.whatsapp', ''));
@endphp
<!DOCTYPE html>
<html {!! \App\Support\Navigation\Appearance::htmlAttributes() !!}>
<head>
    @include('layouts.partials.head', ['pageTitle' => $pageTitle, 'description' => $metaDescription, 'social' => true])
    @include('layouts.public.structured-data')
</head>
<body class="flex min-h-dvh flex-col bg-canvas font-sans text-fg antialiased">
    @include('layouts.partials.skip-link')

    @if ($announcement !== '')
        <div class="border-b border-line bg-surface-2 text-fg-muted">
            <p class="mx-auto max-w-landing px-4 py-2 text-center text-sm sm:px-6">{{ $announcement }}</p>
        </div>
    @endif

    @include('layouts.public.header', ['transparentHeader' => $transparentHeader])

    <main id="konten" tabindex="-1" class="flex-1 focus:outline-none">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('layouts.public.footer')

    @if (setting('integrations.whatsapp_button') && strlen($whatsapp) >= 8)
        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener noreferrer"
           class="fixed bottom-[max(1.25rem,env(safe-area-inset-bottom))] right-5 z-40 inline-flex min-h-12 items-center gap-2 rounded-full bg-brand px-4 text-sm font-semibold text-brand-fg shadow-2 transition-transform hover:-translate-y-0.5 motion-reduce:transition-none motion-reduce:hover:translate-y-0">
            <x-heroicon-o-chat-bubble-oval-left-ellipsis class="size-5" aria-hidden="true" />
            <span>WhatsApp</span>
            <span class="sr-only">(membuka aplikasi WhatsApp di tab baru)</span>
        </a>
    @endif

    {{-- Session flashes + CF.toast region; forms on public pages show their field errors inline. --}}
    <x-ui.toast :validation="false" />
    @includeIf('privacy.partials.cookie-banner')
    @stack('scripts')
</body>
</html>
