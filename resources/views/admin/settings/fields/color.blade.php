{{-- color — native colour picker kept in sync with a hex text input --}}
@php
    $current = old($field['path'], is_string($value) ? $value : '#ffffff');
    $current = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $current) ? strtolower($current) : '#ffffff';
@endphp
<x-ui.field :label="$field['label']" :for="$field['id']" :hint="$field['help'] ?? 'Format heksadesimal, contoh #faf8f4.'" :error="$field['path']" :required="$field['required']">
    <div class="flex items-center gap-3" x-data="{ color: @js($current) }">
        <input type="color" x-model="color" value="{{ $current }}"
            class="h-control w-14 shrink-0 cursor-pointer rounded-md border border-line-input bg-surface p-1"
            aria-label="Pilih warna untuk {{ $field['label'] }}">
        <div class="w-36">
            <x-ui.input :name="$field['name']" :value="$current" x-model="color" maxlength="7"
                spellcheck="false" autocomplete="off" class="font-mono lowercase" />
        </div>
    </div>
</x-ui.field>
