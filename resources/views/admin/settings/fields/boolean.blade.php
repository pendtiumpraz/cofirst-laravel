{{-- boolean — switch; the hidden "0" keeps an unticked switch in the request --}}
<div>
    <x-ui.toggle :name="$field['name']" :id="$field['id']" :label="$field['label']" :description="$field['help']"
        :checked="filter_var($value, FILTER_VALIDATE_BOOLEAN)" unchecked-value="0" :error="$field['path']" />
    @error($field['path'])
        <p class="error-text mt-1"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
    @enderror
</div>
