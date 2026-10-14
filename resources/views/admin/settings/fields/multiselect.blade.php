{{-- multiselect — one checkbox per option (easier than <select multiple> on phones) --}}
@php
    $selected = array_map('strval', (array) ($value ?? []));
    $message = $errors->first($field['path']) ?: $errors->first($field['path'].'.*');
@endphp
<fieldset class="field" @if ($field['help']) aria-describedby="{{ $field['id'] }}-hint" @endif>
    <legend class="label mb-1.5">{{ $field['label'] }}</legend>
    <div class="grid gap-x-6 gap-y-2 sm:grid-cols-2">
        @foreach ($field['options'] as $optionValue => $option)
            <x-ui.checkbox :name="$field['name'].'[]'" :value="$optionValue" :id="$field['id'].'_'.$optionValue"
                :label="$option['label']" :description="$option['description']"
                :checked="in_array((string) $optionValue, $selected, true)" :error="false" />
        @endforeach
    </div>
    @if ($field['help'])
        <p id="{{ $field['id'] }}-hint" class="hint">{{ $field['help'] }}</p>
    @endif
    @if ($message)
        <p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
    @endif
</fieldset>
