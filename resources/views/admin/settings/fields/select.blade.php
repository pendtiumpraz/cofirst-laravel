{{-- select --}}
<x-ui.field :label="$field['label']" :for="$field['id']" :hint="$field['help']" :error="$field['path']" :required="$field['required']">
    <div class="sm:max-w-md">
        <x-ui.select :name="$field['name']" :value="is_scalar($value) ? (string) $value : null"
            :options="collect($field['options'])->map(fn ($option) => $option['label'])->all()"
            :placeholder="$field['required'] ? null : 'Tidak dipilih'" />
    </div>
</x-ui.field>
