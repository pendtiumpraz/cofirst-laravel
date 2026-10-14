{{--
    <x-ui.input name="name" />                                    value falls back to old('name')
    <x-ui.input name="amount" type="number" prefix="Rp" :value="$invoice->amount" />
    <x-ui.input name="q" type="search" icon="magnifying-glass" placeholder="Cari…" />
    <x-ui.input name="phone" type="tel" prefix="+62" inputmode="tel" />

    id      defaults to the surrounding <x-ui.field for> or to the name (dots → _)
    error   error-bag key (defaults to the name in dot notation); :error="false" disables
    Every other attribute (placeholder, required, autocomplete, x-model, wire-less Alpine…) is passed to <input>.
    Sets aria-invalid + aria-describedby (field hint / error) automatically.
--}}
@aware(['for' => null, 'hint' => null])

@props([
    'name' => null,
    'type' => 'text',
    'value' => null,
    'id' => null,
    'error' => null,
    'icon' => null,
    'prefix' => null,
    'suffix' => null,
])

@php
    $key = $name ? trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.') : null;
    $inputId = $id ?? $for ?? ($key ? str_replace('.', '_', $key) : null);
    $errorKey = $error === false ? null : ($error ?? $key);
    $bag = $errors ?? null;
    $invalid = $errorKey && $bag instanceof \Illuminate\Support\ViewErrorBag && $bag->has($errorKey);

    $current = $value;
    if ($key && $type !== 'password' && request()->hasSession()) {
        $current = old($key, $value);
    }
    if (is_array($current)) {
        $current = null;
    }

    $describedBy = trim(implode(' ', array_filter([
        $attributes->get('aria-describedby'),
        ($hint && $inputId) ? $inputId.'-hint' : null,
        ($invalid && $inputId) ? $inputId.'-error' : null,
    ])));

    $extra = array_filter([
        'id' => $inputId,
        'name' => $name,
        'type' => $type,
        'aria-describedby' => $describedBy ?: null,
        'aria-invalid' => $invalid ? 'true' : null,
    ], fn ($v) => $v !== null);

    $hasAddon = $icon || filled($prefix) || filled($suffix);
@endphp

@if ($hasAddon)
    <div class="input-group">
        @if (filled($prefix))
            <span class="input-addon" aria-hidden="true">{{ $prefix }}</span>
        @endif
        @if ($icon)
            <x-ui.icon :name="$icon" class="input-icon" />
        @endif
        <input {{ $attributes->except('aria-describedby')->merge($extra)->class('input') }}
            @if ($current !== null && $type !== 'password') value="{{ $current }}" @endif>
        @if (filled($suffix))
            <span class="input-addon" aria-hidden="true">{{ $suffix }}</span>
        @endif
    </div>
@else
    <input {{ $attributes->except('aria-describedby')->merge($extra)->class('input') }}
        @if ($current !== null && $type !== 'password') value="{{ $current }}" @endif>
@endif
