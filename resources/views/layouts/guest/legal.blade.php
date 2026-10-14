{{--
    Footer links of the auth pages. Each legal page is linked only when its route exists
    (they belong to the privacy module).
--}}
@php
    $links = \App\Support\Navigation\LegalLinks::all();
@endphp
<footer class="w-full px-4 pb-[max(1.5rem,env(safe-area-inset-bottom))] pt-6 sm:px-8">
    <nav aria-label="Tautan hukum" class="mx-auto flex max-w-md flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-fg-subtle">
        @foreach (array_intersect_key($links, array_flip(['privacy', 'terms', 'cookies'])) as $link)
            <a href="{{ $link['url'] }}" class="rounded-sm underline-offset-4 hover:text-fg hover:underline">{{ $link['label'] }}</a>
        @endforeach
        <span class="inline-flex items-center gap-1.5">
            <x-heroicon-o-shield-check class="size-4" aria-hidden="true" />
            Dilindungi UU PDP
        </span>
    </nav>
</footer>
