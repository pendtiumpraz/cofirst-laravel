{{--
    <x-ui.checkbox name="remember" label="Ingat saya" />
    <x-ui.checkbox name="consent_photo" value="1" label="Izinkan publikasi foto/karya anak"
        description="Dapat ditarik kapan saja dari Pusat Privasi." unchecked-value="0" />
    <x-ui.checkbox name="roles[]" value="teacher" :checked="$user->hasRole('teacher')" label="Guru" />
    Checked state follows old input after a failed submit, otherwise :checked.
    unchecked-value renders a hidden input so an unticked box still submits a value.
    The slot may replace `label` (e.g. with links: Saya menyetujui <a …>Kebijakan Privasi</a>).
--}}
@aware(['for' => null, 'hint' => null])

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
    $isArray = $name && str_ends_with($name, '[]');
    $key = $name ? trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.') : null;
    $boxId = $id ?? ($isArray ? null : $for) ?? ($key ? str_replace('.', '_', $key).($isArray ? '_'.\Illuminate\Support\Str::slug((string) $value, '_') : '') : null);
    $errorKey = $error === false ? null : ($error ?? $key);
    $bag = $errors ?? null;
    $invalid = $errorKey && $bag instanceof \Illuminate\Support\ViewErrorBag && $bag->has($errorKey);

    $isChecked = (bool) $checked;
    if ($key && request()->hasSession() && request()->session()->hasOldInput()) {
        $old = old($key);
        $isChecked = $isArray
            ? in_array((string) $value, array_map('strval', (array) $old), true)
            : ($old !== null && (string) $old === (string) $value);
    }

    $describedBy = trim(implode(' ', array_filter([
        $attributes->get('aria-describedby'),
        $description && $boxId ? $boxId.'-description' : null,
        ($invalid && $boxId && ! $isArray) ? $boxId.'-error' : null,
    ])));

    $extra = array_filter([
        'id' => $boxId,
        'name' => $name,
        'value' => (string) $value,
        'aria-describedby' => $describedBy ?: null,
        'aria-invalid' => $invalid ? 'true' : null,
    ], fn ($v) => $v !== null);

    $hasLabel = filled($label) || $slot->hasActualContent();
@endphp

<label @class(['check', 'cursor-not-allowed' => $attributes->has('disabled')])>
    @if ($uncheckedValue !== null && $name && ! $isArray)
        <input type="hidden" name="{{ $name }}" value="{{ $uncheckedValue }}">
    @endif
    <input type="checkbox" {{ $attributes->except('aria-describedby')->merge($extra)->class('check-input') }} @checked($isChecked)>
    @if ($hasLabel)
        <span class="check-label">
            {{ $slot->hasActualContent() ? $slot : $label }}
            @if (filled($description))
                <span @if ($boxId) id="{{ $boxId }}-description" @endif class="check-description">{{ $description }}</span>
            @endif
        </span>
    @endif
</label>
