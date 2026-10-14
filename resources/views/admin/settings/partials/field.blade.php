{{--
    One settings field: the control for its type plus the audit/reset row
    ("Diubah 13 Okt 2026 oleh …" · "Kembalikan ke bawaan") for customised values.
--}}
@php
    $type = $field['type'];
    $value = $values[$field['key']] ?? null;
    $record = $records->get($field['key']);
    $view = match (true) {
        in_array($type, \App\Support\Settings\SettingsRegistry::TEXT_TYPES, true) => 'text',
        in_array($type, ['textarea', 'markdown'], true) => 'textarea',
        in_array($type, ['number', 'boolean', 'select', 'multiselect', 'choice', 'color', 'image', 'repeater', 'list'], true) => $type,
        default => 'text',
    };
@endphp
<div id="bidang-{{ $field['id'] }}" class="min-w-0 scroll-mt-28">
    @include('admin.settings.fields.'.$view, ['field' => $field, 'value' => $value])

    @if ($record)
        <div class="mt-1.5 flex flex-wrap items-center gap-x-2 text-xs text-fg-subtle">
            <span>
                Diubah {{ $record->updated_at?->timezone(config('app.timezone'))->translatedFormat('j M Y') }}
                oleh {{ $record->editor?->name ?? 'sistem' }}
            </span>
            @if ($canEdit)
                <span aria-hidden="true">·</span>
                <x-ui.button variant="link" size="xs" type="submit" form="settings-reset" name="field" value="{{ $field['key'] }}"
                    class="text-xs"
                    data-confirm="{{ $field['label'] }} akan kembali ke nilai bawaan. Perubahan lain yang belum disimpan ikut hilang."
                    data-confirm-title="Kembalikan ke bawaan?"
                    data-confirm-ok="Kembalikan">
                    Kembalikan ke bawaan
                </x-ui.button>
            @endif
        </div>
    @endif
</div>
