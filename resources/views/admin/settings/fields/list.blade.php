{{-- list — one item per line, stored as an array of strings --}}
@php
    $lines = old($field['path'], is_array($value) ? $value : []);
    $text = is_array($lines) ? implode("\n", array_map('strval', $lines)) : (string) $lines;
    $message = $errors->first($field['path']) ?: $errors->first($field['path'].'.*');
@endphp
<x-ui.field :label="$field['label']" :for="$field['id']" :hint="$field['help'] ?? 'Satu item per baris.'" :error="false">
    {{-- Reason: plain textarea — old() holds the already-split array, which x-ui.textarea would blank out. --}}
    <textarea id="{{ $field['id'] }}" name="{{ $field['name'] }}" rows="5" class="input"
        aria-describedby="{{ $field['id'] }}-hint"
        @if ($message) aria-invalid="true" @endif>{{ $text }}</textarea>
    @if ($message)
        <p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
    @endif
</x-ui.field>
