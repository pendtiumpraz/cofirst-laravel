{{--
    Switch (role="switch") for on/off settings — 44×24 track, keyboard and screen-reader friendly.

    <x-ui.toggle name="notifications[email]" label="Email" description="Ringkasan mingguan." :checked="$prefs['email']" unchecked-value="0" />
    <x-ui.toggle name="reduce_motion" label="Kurangi animasi" x-model="reduce" />
    Same value/old()/unchecked-value rules as <x-ui.checkbox>. Label sits left, switch right.
--}}
@props([
    'name' => null,
    'value' => '1',
    'checked' => false,
    'label' => null,
    'description' => null,
    'id' => null,
    'error' => null,
    'uncheckedValue' => null,
])

@php
    $key = $name ? trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.') : null;
    $switchId = $id ?? ($key ? str_replace('.', '_', $key) : 'switch-'.\Illuminate\Support\Str::random(6));
    $errorKey = $error === false ? null : ($error ?? $key);
    $bag = $errors ?? null;
    $invalid = $errorKey && $bag instanceof \Illuminate\Support\ViewErrorBag && $bag->has($errorKey);

    $isChecked = (bool) $checked;
    if ($key && request()->hasSession() && request()->session()->hasOldInput()) {
        $old = old($key);
        $isChecked = $old !== null && (string) $old === (string) $value;
    }

    $extra = array_filter([
        'id' => $switchId,
        'name' => $name,
        'value' => (string) $value,
        'role' => 'switch',
        'aria-describedby' => $description ? $switchId.'-description' : null,
        'aria-invalid' => $invalid ? 'true' : null,
    ], fn ($v) => $v !== null);
@endphp

<label for="{{ $switchId }}" class="flex cursor-pointer items-start justify-between gap-4 py-1">
    @if (filled($label) || filled($description))
        <span class="min-w-0">
            @if (filled($label))
                <span class="block text-sm font-medium text-fg">{{ $label }}</span>
            @endif
            @if (filled($description))
                <span id="{{ $switchId }}-description" class="mt-0.5 block text-[0.8125rem] text-fg-subtle">{{ $description }}</span>
            @endif
        </span>
    @endif
    <span class="relative inline-flex shrink-0 items-center py-2.5">
        @if ($uncheckedValue !== null && $name)
            <input type="hidden" name="{{ $name }}" value="{{ $uncheckedValue }}">
        @endif
        <input type="checkbox" {{ $attributes->merge($extra)->class('peer sr-only') }} @checked($isChecked)>
        <span class="switch" aria-hidden="true"></span>
    </span>
</label>
