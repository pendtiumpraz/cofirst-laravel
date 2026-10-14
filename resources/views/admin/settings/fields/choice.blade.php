{{--
    choice — large A/B version cards: a live mini preview drawn with the design tokens,
    the option name and description, "Aktif" on the saved option and a "Pratinjau"
    link that applies the option to the admin's own session only (signed URL).
--}}
@php
    $saved = is_scalar($value) ? (string) $value : '';
    $current = (string) old($field['path'], $saved);
    $previewView = 'admin.settings.previews.'.str_replace('_', '-', \Illuminate\Support\Str::after($field['key'], '.'));
    $cols = count($field['options']) >= 3 ? 'sm:grid-cols-2 xl:grid-cols-3' : 'sm:grid-cols-2';
    $message = $errors->first($field['path']);
@endphp
<fieldset class="field min-w-0" @if ($field['help']) aria-describedby="{{ $field['id'] }}-hint" @endif>
    <legend class="label mb-1">{{ $field['label'] }}</legend>
    @if ($field['help'])
        <p id="{{ $field['id'] }}-hint" class="hint -mt-0.5 mb-1">{{ $field['help'] }}</p>
    @endif

    <div class="grid gap-4 {{ $cols }}">
        @foreach ($field['options'] as $optionValue => $option)
            @php
                $optionValue = (string) $optionValue;
                $optionId = $field['id'].'_'.\Illuminate\Support\Str::slug($optionValue, '_');
                $previewUrl = $field['previewable'] ? \App\Support\Settings\AppearancePreview::url($field['key'], $optionValue) : null;
                $isPreviewing = ($preview[$field['key']] ?? null) === $optionValue;
            @endphp
            <div class="flex min-w-0 flex-col overflow-hidden rounded-lg border border-line bg-surface transition-colors duration-fast motion-reduce:transition-none hover:border-line-strong has-[:checked]:border-brand has-[:checked]:ring-1 has-[:checked]:ring-brand has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ring">
                <label for="{{ $optionId }}" class="flex flex-1 cursor-pointer flex-col">
                    <span class="block border-b border-line bg-surface-2 p-3" aria-hidden="true">
                        @includeFirst([$previewView, 'admin.settings.previews.generic'], ['option' => $optionValue, 'label' => $option['label']])
                    </span>
                    <span class="flex flex-1 items-start gap-3 p-4">
                        <input type="radio" id="{{ $optionId }}" name="{{ $field['name'] }}" value="{{ $optionValue }}"
                            class="check-input mt-0.5 shrink-0" @checked($current === $optionValue)
                            @if ($option['description']) aria-describedby="{{ $optionId }}-description" @endif
                            @if ($message) aria-invalid="true" @endif>
                        <span class="min-w-0">
                            <span class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-semibold text-fg">
                                {{ $option['label'] }}
                                @if ($saved === $optionValue)
                                    <x-ui.badge tone="success" dot>Aktif</x-ui.badge>
                                @endif
                                @if ($isPreviewing)
                                    <x-ui.badge tone="info" icon="eye">Dipratinjau</x-ui.badge>
                                @endif
                            </span>
                            @if ($option['description'])
                                <span id="{{ $optionId }}-description" class="mt-1 block text-[0.8125rem] leading-relaxed text-fg-muted">{{ $option['description'] }}</span>
                            @endif
                        </span>
                    </span>
                </label>
                @if ($previewUrl)
                    <div class="flex items-center justify-between gap-2 border-t border-line px-4">
                        <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="link inline-flex min-h-11 items-center gap-1.5 text-sm">
                            <x-ui.icon name="eye" class="size-4" />
                            Pratinjau<span class="sr-only"> {{ $option['label'] }} (membuka tab baru)</span>
                        </a>
                        <x-ui.icon name="arrow-top-right-on-square" class="size-4 text-fg-subtle" />
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if ($message)
        <p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>
    @endif
</fieldset>
