{{--
    Shown on Tampilan & Versi while the admin previews options in their own session.
    "Terapkan" saves the previewed values for everyone; "Akhiri" discards them.
--}}
@php
    $registry = app(\App\Support\Settings\SettingsRegistry::class);
    $items = collect($preview)->map(function ($value, $key) use ($registry) {
        $field = $registry->field($key);

        return ($field['label'] ?? $key).': '.($field['options'][$value]['label'] ?? $value);
    });
    $canApply = $canEdit;
@endphp
<x-ui.alert tone="info" icon="eye" title="Pratinjau aktif — hanya Anda yang melihatnya">
    <ul role="list" class="mt-1 space-y-0.5">
        @foreach ($items as $line)
            <li>{{ $line }}</li>
        @endforeach
    </ul>
    <x-slot:actions>
        @if ($canApply)
            <form method="POST" action="{{ route('admin.settings.preview.apply') }}">
                @csrf
                <x-ui.button type="submit" size="sm" icon="check"
                    data-confirm="Pilihan yang sedang dipratinjau akan berlaku untuk semua pengguna."
                    data-confirm-title="Terapkan pratinjau?" data-confirm-ok="Terapkan" data-confirm-tone="primary">
                    Terapkan untuk semua
                </x-ui.button>
            </form>
        @endif
        <form method="POST" action="{{ route('admin.settings.preview.destroy') }}">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" size="sm" variant="secondary" icon="x-mark">Akhiri pratinjau</x-ui.button>
        </form>
    </x-slot:actions>
</x-ui.alert>
