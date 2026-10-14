{{--
    Label + control + hint + validation message.

    <x-ui.field label="Email" for="email" hint="Kami tidak membagikan email Anda." required>
        <x-ui.input name="email" type="email" autocomplete="email" />
    </x-ui.field>

    for      id of the control. Controls inside a field use it as their id when they get none,
             and link the hint/error through aria-describedby automatically.
    error    error-bag key to show ($errors->first($error)); defaults to `for`
             (use dot notation for arrays: items.0.name). Pass :error="false" to hide.
    optional appends '(opsional)'; required appends a visual '*' (also set `required` on the control).
--}}
@props([
    'label' => null,
    'for' => null,
    'hint' => null,
    'error' => null,
    'optional' => false,
    'required' => false,
    'srOnlyLabel' => false,
])

@php
    $errorKey = $error === false ? null : ($error ?? ($for ? trim(str_replace(['[]', '[', ']'], ['', '.', ''], $for), '.') : null));
    $bag = $errors ?? null;
    $message = $errorKey && $bag instanceof \Illuminate\Support\ViewErrorBag ? $bag->first($errorKey) : null;
@endphp

<div {{ $attributes->class('field') }}>
    @if (filled($label))
        <label @if ($for) for="{{ $for }}" @endif @class(['label', 'sr-only' => $srOnlyLabel])>
            {{ $label }}
            @if ($optional)
                <span class="label-optional">(opsional)</span>
            @endif
            @if ($required)
                <span class="label-required" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if (filled($hint))
        <p @if ($for) id="{{ $for }}-hint" @endif class="hint">{{ $hint }}</p>
    @endif

    @if (filled($message))
        <p @if ($for) id="{{ $for }}-error" @endif class="error-text">
            <x-ui.icon name="m-exclamation-circle" />
            <span>{{ $message }}</span>
        </p>
    @endif
</div>
