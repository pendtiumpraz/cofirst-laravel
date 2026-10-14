{{--
    <x-ui.textarea name="notes" rows="5" placeholder="Catatan untuk orang tua…" />
    <x-ui.textarea name="bio" :value="$user->bio" maxlength="500" />
    value falls back to old(); slot content is used when no value is given.
--}}
@aware(['for' => null, 'hint' => null])

@props([
    'name' => null,
    'value' => null,
    'rows' => 4,
    'id' => null,
    'error' => null,
])

@php
    $key = $name ? trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.') : null;
    $areaId = $id ?? $for ?? ($key ? str_replace('.', '_', $key) : null);
    $errorKey = $error === false ? null : ($error ?? $key);
    $bag = $errors ?? null;
    $invalid = $errorKey && $bag instanceof \Illuminate\Support\ViewErrorBag && $bag->has($errorKey);

    $current = $value ?? ($slot->hasActualContent() ? trim((string) $slot) : null);
    if ($key && request()->hasSession()) {
        $current = old($key, $current);
    }

    $describedBy = trim(implode(' ', array_filter([
        $attributes->get('aria-describedby'),
        ($hint && $areaId) ? $areaId.'-hint' : null,
        ($invalid && $areaId) ? $areaId.'-error' : null,
    ])));

    $extra = array_filter([
        'id' => $areaId,
        'name' => $name,
        'rows' => $rows,
        'aria-describedby' => $describedBy ?: null,
        'aria-invalid' => $invalid ? 'true' : null,
    ], fn ($v) => $v !== null);
@endphp

<textarea {{ $attributes->except('aria-describedby')->merge($extra)->class('input') }}>{{ is_array($current) ? '' : $current }}</textarea>
