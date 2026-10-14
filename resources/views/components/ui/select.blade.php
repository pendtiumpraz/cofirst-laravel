{{--
    <x-ui.select name="status" :options="['active' => 'Aktif', 'inactive' => 'Nonaktif']" placeholder="Pilih status" />
    <x-ui.select name="class_id" :options="$classes->pluck('name', 'id')" :value="$enrollment->class_id" />
    <x-ui.select name="roles[]" multiple :options="$roles" :value="['admin']" />
    Options: [value => label] · [['value' => …, 'label' => …, 'disabled' => bool], …] ·
             optgroups [groupLabel => [value => label]]. Extra <option>s may go in the slot.
    value falls back to old(); in GET filter bars pass :value="request('status')".
--}}
@aware(['for' => null, 'hint' => null])

@props([
    'name' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'id' => null,
    'error' => null,
])

@php
    $key = $name ? trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.') : null;
    $selectId = $id ?? $for ?? ($key ? str_replace('.', '_', $key) : null);
    $errorKey = $error === false ? null : ($error ?? $key);
    $bag = $errors ?? null;
    $invalid = $errorKey && $bag instanceof \Illuminate\Support\ViewErrorBag && $bag->has($errorKey);

    $current = $value;
    if ($key && request()->hasSession()) {
        $current = old($key, $value);
    }
    if ($current instanceof \Illuminate\Support\Collection) {
        $current = $current->all();
    }
    if ($current instanceof \BackedEnum) {
        $current = $current->value;
    }
    $selected = array_map('strval', array_filter((array) $current, fn ($v) => $v !== null));

    if ($options instanceof \Illuminate\Support\Collection) {
        $options = $options->all();
    }

    // Normalise to [['value','label','disabled'] | ['group' => label, 'options' => [...]]].
    $normalise = function (array $list) {
        $out = [];
        foreach ($list as $optionValue => $option) {
            if (is_array($option) && array_key_exists('label', $option)) {
                $out[] = ['value' => (string) ($option['value'] ?? $optionValue), 'label' => $option['label'], 'disabled' => ! empty($option['disabled'])];
            } elseif (! is_array($option)) {
                $out[] = ['value' => (string) $optionValue, 'label' => $option, 'disabled' => false];
            }
        }

        return $out;
    };

    $groups = [];
    foreach ($options as $groupKey => $option) {
        if (is_array($option) && ! array_key_exists('label', $option)) {
            $groups[] = ['group' => $groupKey, 'options' => $normalise($option)];
        } else {
            $groups[] = ['group' => null, 'options' => $normalise([$groupKey => $option])];
        }
    }

    $describedBy = trim(implode(' ', array_filter([
        $attributes->get('aria-describedby'),
        ($hint && $selectId) ? $selectId.'-hint' : null,
        ($invalid && $selectId) ? $selectId.'-error' : null,
    ])));

    $extra = array_filter([
        'id' => $selectId,
        'name' => $name,
        'aria-describedby' => $describedBy ?: null,
        'aria-invalid' => $invalid ? 'true' : null,
    ], fn ($v) => $v !== null);
@endphp

<select {{ $attributes->except('aria-describedby')->merge($extra)->class('input') }}>
    @if (filled($placeholder))
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($groups as $group)
        @if ($group['group'] !== null)
            <optgroup label="{{ $group['group'] }}">
        @endif
        @foreach ($group['options'] as $option)
            <option value="{{ $option['value'] }}" @selected(in_array($option['value'], $selected, true)) @disabled($option['disabled'])>{{ $option['label'] }}</option>
        @endforeach
        @if ($group['group'] !== null)
            </optgroup>
        @endif
    @endforeach
    {{ $slot }}
</select>
