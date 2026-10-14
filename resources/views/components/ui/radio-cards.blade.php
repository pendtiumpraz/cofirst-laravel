{{--
    Radio group rendered as selectable cards (A/B version choice, DSR request type, role selection).

    <x-ui.radio-cards name="type" legend="Jenis permintaan" :cols="2" :options="[
        'access' => ['label' => 'Akses', 'description' => 'Salinan data pribadi Anda.', 'icon' => 'eye'],
        'erasure' => ['label' => 'Hapus', 'description' => 'Penghapusan data.', 'icon' => 'trash'],
        'other' => 'Lainnya',
    ]" value="access" hint="Kami merespons paling lambat 3×24 jam." />
    value falls back to old(). cols: 1 | 2 | 3.
--}}
@props([
    'name',
    'options' => [],
    'value' => null,
    'legend' => null,
    'hint' => null,
    'cols' => 1,
    'error' => null,
    'required' => false,
])

@php
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $baseId = str_replace('.', '_', $key);
    $errorKey = $error === false ? null : ($error ?? $key);
    $bag = $errors ?? null;
    $message = $errorKey && $bag instanceof \Illuminate\Support\ViewErrorBag ? $bag->first($errorKey) : null;

    $current = $value;
    if (request()->hasSession()) {
        $current = old($key, $value);
    }
    $current = is_scalar($current) ? (string) $current : null;

    if ($options instanceof \Illuminate\Support\Collection) {
        $options = $options->all();
    }

    $gridClass = match ((int) $cols) {
        2 => 'sm:grid-cols-2',
        3 => 'sm:grid-cols-2 lg:grid-cols-3',
        default => '',
    };

    $describedBy = trim(implode(' ', array_filter([
        $hint ? $baseId.'-hint' : null,
        $message ? $baseId.'-error' : null,
    ])));
@endphp

<fieldset {{ $attributes->class('field') }} @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
    @if (filled($legend))
        <legend class="label mb-1.5">
            {{ $legend }}
            @if ($required)
                <span class="label-required" aria-hidden="true">*</span>
            @endif
        </legend>
    @endif

    <div class="grid gap-3 {{ $gridClass }}">
        @foreach ($options as $optionValue => $option)
            @php
                $option = is_array($option) ? $option : ['label' => $option];
                $optionId = $baseId.'_'.\Illuminate\Support\Str::slug((string) $optionValue, '_');
            @endphp
            <label for="{{ $optionId }}" class="radio-card">
                <input type="radio" id="{{ $optionId }}" name="{{ $name }}" value="{{ $optionValue }}"
                    class="check-input mt-0.5" @checked($current === (string) $optionValue)
                    @disabled(! empty($option['disabled'])) @required($required)
                    @if ($message) aria-invalid="true" @endif
                    @if (! empty($option['description'])) aria-describedby="{{ $optionId }}-description" @endif>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2 text-sm font-semibold text-fg">
                        @if (! empty($option['icon']))
                            <x-ui.icon :name="$option['icon']" class="size-[1.125rem] text-fg-subtle" />
                        @endif
                        {{ $option['label'] }}
                    </span>
                    @if (! empty($option['description']))
                        <span id="{{ $optionId }}-description" class="mt-1 block text-[0.8125rem] text-fg-muted">{{ $option['description'] }}</span>
                    @endif
                </span>
            </label>
        @endforeach
    </div>

    @if (filled($hint))
        <p id="{{ $baseId }}-hint" class="hint">{{ $hint }}</p>
    @endif
    @if (filled($message))
        <p id="{{ $baseId }}-error" class="error-text">
            <x-ui.icon name="m-exclamation-circle" />
            <span>{{ $message }}</span>
        </p>
    @endif
</fieldset>
