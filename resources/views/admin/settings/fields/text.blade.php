{{-- text | email | url | tel --}}
@php
    $inputType = in_array($field['type'], ['email', 'url', 'tel'], true) ? $field['type'] : 'text';
    $icon = match ($field['type']) {
        'email' => 'envelope',
        'url' => 'link',
        'tel' => 'phone',
        default => null,
    };
@endphp
<x-ui.field :label="$field['label']" :for="$field['id']" :hint="$field['help']" :error="$field['path']" :required="$field['required']">
    <x-ui.input :name="$field['name']" :type="$inputType" :value="is_scalar($value) ? $value : null"
        :icon="$icon" :placeholder="$field['placeholder']" :maxlength="$field['max']"
        :inputmode="$field['type'] === 'tel' ? 'tel' : null"
        autocomplete="off" spellcheck="{{ $field['type'] === 'text' ? 'true' : 'false' }}" />
</x-ui.field>
