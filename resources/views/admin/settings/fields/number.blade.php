{{-- number — with optional unit suffix (menit, hari, %, …) and min/max from the rules --}}
<x-ui.field :label="$field['label']" :for="$field['id']" :hint="$field['help']" :error="$field['path']" :required="$field['required']">
    <div class="sm:max-w-xs">
        <x-ui.input :name="$field['name']" type="number" :value="is_scalar($value) ? $value : null"
            :suffix="$field['unit']" :min="$field['min']" :max="$field['max']"
            :step="in_array('integer', $field['rules'], true) ? '1' : 'any'"
            inputmode="{{ in_array('integer', $field['rules'], true) ? 'numeric' : 'decimal' }}"
            class="num" />
    </div>
</x-ui.field>
