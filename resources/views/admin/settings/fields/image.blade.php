{{--
    image — current image (on a dark tile for "dark" variants), a remove switch that
    applies on save, and a dropzone for a new upload (PNG/JPG/WebP, no SVG).
--}}
@php
    $url = null;
    if (is_string($value) && $value !== '') {
        $url = preg_match('#^(https?:)?//#', $value) || str_starts_with($value, '/')
            ? $value
            : \Illuminate\Support\Facades\Storage::disk('public')->url($value);
    }
    $dark = str_ends_with($field['key'], '_dark');
    $maxKb = (int) ($field['max'] ?? 1024);
    $removeName = \App\Support\Settings\SettingsRegistry::inputName($field['key'], \App\Support\Settings\SettingsRegistry::REMOVE);
    $sizeLabel = $maxKb >= 1024 ? str_replace('.', ',', (string) round($maxKb / 1024, 1)).' MB' : $maxKb.' KB';
@endphp
<x-ui.field :label="$field['label']" :for="$field['id']" :hint="$field['help']" :error="$field['path']"
    x-data="{ remove: {{ old(\App\Support\Settings\SettingsRegistry::REMOVE.'.'.$field['key']) ? 'true' : 'false' }} }">
    @if ($url)
        <div class="flex flex-wrap items-center gap-4 rounded-lg border border-line bg-surface-2 p-3" x-show="! remove">
            {{-- Reason: constant ramp colours on purpose — the tile shows the logo on the background it is made for, in either colour mode. --}}
            <span @class([
                'grid h-16 w-40 place-items-center rounded-md border border-line p-2',
                'bg-neutral-950' => $dark,
                'bg-neutral-50' => ! $dark,
            ])>
                <img src="{{ $url }}" alt="{{ $field['label'] }} saat ini" class="no-dim max-h-12 w-auto max-w-full object-contain">
            </span>
            <span class="min-w-0 flex-1 text-sm text-fg-muted">Gambar saat ini. Unggah berkas baru untuk menggantinya.</span>
            <x-ui.button variant="danger-ghost" size="sm" icon="trash" x-on:click="remove = true">Hapus</x-ui.button>
        </div>
        <div x-show="remove" x-cloak class="alert alert-warning">
            <x-ui.icon name="exclamation-triangle" class="alert-icon" />
            <div class="min-w-0 flex-1 text-sm">
                Gambar akan dihapus saat Anda menyimpan; logo bawaan dipakai kembali.
                <button type="button" class="link ml-1" x-on:click="remove = false">Batalkan</button>
            </div>
        </div>
        <input type="hidden" name="{{ $removeName }}" x-bind:value="remove ? 1 : 0" value="0">
    @endif

    <x-ui.file :name="$field['name']" :id="$field['id']" accept="image/png,image/jpeg,image/webp" :max-kb="$maxKb"
        hint="PNG, JPG atau WebP · maks. {{ $sizeLabel }}" preview />
</x-ui.field>
