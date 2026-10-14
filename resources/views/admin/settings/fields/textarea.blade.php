{{-- textarea | markdown — with a live character counter when the rules set a maximum --}}
@php
    $markdown = $field['type'] === 'markdown';
    $current = old($field['path'], is_scalar($value) ? (string) $value : '');
    $hint = $field['help'] ?? ($markdown ? 'Mendukung Markdown: **tebal**, _miring_, daftar dengan "-", dan [tautan](https://…).' : null);
@endphp
<x-ui.field :label="$field['label']" :for="$field['id']" :hint="$hint" :error="$field['path']" :required="$field['required']"
    x-data="{ count: {{ mb_strlen((string) $current) }} }">
    <x-ui.textarea :name="$field['name']" :value="is_scalar($value) ? $value : null" :rows="$markdown ? 10 : 3"
        :maxlength="$field['max']" :placeholder="$field['placeholder']"
        :class="$markdown ? 'font-mono text-[0.8125rem]' : null"
        x-on:input="count = $event.target.value.length" />
    @if ($field['max'])
        <p class="num -mt-0.5 text-right text-xs text-fg-subtle" aria-hidden="true">
            <span x-text="count">{{ mb_strlen((string) $current) }}</span> / {{ $field['max'] }}
        </p>
    @endif
</x-ui.field>
