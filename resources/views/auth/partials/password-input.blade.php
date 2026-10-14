{{--
    Password field with a show/hide toggle and an optional strength meter.

    @include('auth.partials.password-input', [
        'name' => 'password',                 required
        'label' => 'Kata sandi',              required
        'id' => 'password',                   defaults to name
        'autocomplete' => 'current-password', or 'new-password'
        'bag' => 'updatePassword',            error bag (default 'default')
        'errorKey' => 'password',             defaults to name
        'hint' => '…',                        plain hint under the field
        'meter' => true,                      strength meter + live checklist
        'requirements' => PasswordPolicy::requirements(),
        'autofocus' => true,
        'asideUrl' => route('password.request'), 'asideLabel' => 'Lupa kata sandi?',   link right of the label
    ])

    The checklist is rendered on the server (works without JavaScript); Alpine only colours it.
--}}
@php
    $id = $id ?? $name;
    $bag = $bag ?? 'default';
    $errorKey = $errorKey ?? $name;
    $message = isset($errors) ? $errors->getBag($bag)->first($errorKey) : null;
    $hint = $hint ?? null;
    $requirements = array_values($requirements ?? []);
    $meter = ($meter ?? false) && $requirements !== [];
    $describedBy = trim(implode(' ', array_filter([
        $hint ? $id.'-hint' : null,
        $meter ? $id.'-rules' : null,
        $message ? $id.'-error' : null,
    ])));
@endphp

<div class="field" x-data="{
        show: false,
        value: '',
        rules: @js($meter ? $requirements : []),
        met(index) {
            const rule = this.rules[index];
            return rule ? new RegExp(rule.pattern, rule.flags).test(this.value) : false;
        },
        get passed() { return this.rules.filter((rule, index) => this.met(index)).length; },
        get score() {
            if (! this.value || ! this.rules.length) return 0;
            let score = Math.round((this.passed / this.rules.length) * 3);
            if (this.passed === this.rules.length && this.value.length >= 12) score += 1;
            return Math.max(1, Math.min(4, score));
        },
        get strength() { return ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][this.score]; },
    }">
    <div class="flex items-baseline justify-between gap-3">
        <label for="{{ $id }}" class="label">{{ $label }}</label>
        @if (! empty($asideUrl))
            <a href="{{ $asideUrl }}" class="link text-[0.8125rem] font-medium">{{ $asideLabel ?? '' }}</a>
        @endif
    </div>

    <div class="relative">
        <input id="{{ $id }}" name="{{ $name }}" type="password"
            x-bind:type="show ? 'text' : 'password'"
            class="input pr-12"
            autocomplete="{{ $autocomplete ?? 'current-password' }}"
            maxlength="255" spellcheck="false" autocapitalize="none"
            @if ($required ?? true) required @endif
            @if ($autofocus ?? false) autofocus @endif
            @if ($meter) x-model="value" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($message) aria-invalid="true" @endif>
        <button type="button"
            class="absolute inset-y-0 right-0 inline-flex w-11 items-center justify-center rounded-r-md text-fg-subtle transition-colors duration-fast hover:text-fg motion-reduce:transition-none"
            x-on:click="show = ! show"
            x-bind:aria-pressed="show.toString()"
            aria-pressed="false"
            aria-controls="{{ $id }}">
            <span class="sr-only">Tampilkan kata sandi</span>
            <x-ui.icon name="eye" class="size-5" x-show="! show" />
            <x-ui.icon name="eye-slash" class="size-5" x-show="show" x-cloak />
        </button>
    </div>

    @if ($meter)
        <div class="flex items-center gap-3" x-show="value.length > 0" x-cloak>
            <div class="grid flex-1 grid-cols-4 gap-1" aria-hidden="true">
                @foreach ([1, 2, 3, 4] as $step)
                    <span class="h-1.5 rounded-full transition-colors duration-fast motion-reduce:transition-none"
                        x-bind:class="score >= {{ $step }} ? (score <= 1 ? 'bg-danger-solid' : (score === 2 ? 'bg-warning-solid' : 'bg-success-solid')) : 'bg-surface-3'"></span>
                @endforeach
            </div>
            <p class="w-24 shrink-0 text-right text-xs font-medium text-fg-muted" aria-live="polite">
                <span class="sr-only">Kekuatan kata sandi:</span>
                <span x-text="strength"></span>
            </p>
        </div>

        <ul id="{{ $id }}-rules" class="grid gap-x-4 gap-y-1 text-[0.8125rem] leading-5 text-fg-subtle sm:grid-cols-2" aria-label="Syarat kata sandi">
            @foreach ($requirements as $index => $rule)
                <li class="flex items-center gap-1.5" x-bind:class="met({{ $index }}) ? 'text-success' : ''">
                    <x-ui.icon name="m-check-circle" class="size-4" x-show="met({{ $index }})" x-cloak />
                    <x-ui.icon name="m-minus-circle" class="size-4" x-show="! met({{ $index }})" />
                    <span>{{ $rule['label'] }}</span>
                    <span class="sr-only" x-text="met({{ $index }}) ? '(terpenuhi)' : ''"></span>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($hint)
        <p id="{{ $id }}-hint" class="hint">{{ $hint }}</p>
    @endif

    @if ($message)
        <p id="{{ $id }}-error" class="error-text">
            <x-ui.icon name="m-exclamation-circle" />
            <span>{{ $message }}</span>
        </p>
    @endif
</div>
