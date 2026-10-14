{{--
    Accessible on/off switch that posts to a toggle-status route.
    Params: $action (url), $active (bool), $label (aria label, e.g. "Status kurikulum Python Dasar")
--}}
<form method="POST" action="{{ $action }}" class="inline-flex">
    @csrf
    <label class="inline-flex min-h-[44px] cursor-pointer items-center gap-2">
        <input type="checkbox" role="switch" class="peer sr-only" @checked($active) aria-label="{{ $label }}"
            x-data x-on:change="$el.form.requestSubmit()">
        <span class="switch" aria-hidden="true"></span>
        <span class="text-sm text-fg-muted">{{ $active ? 'Aktif' : 'Nonaktif' }}</span>
    </label>
    <noscript><button type="submit" class="btn btn-ghost btn-xs">Ubah</button></noscript>
</form>
