{{--
    Dropzone file input (click or drag & drop; dropped files are really attached to the input).

    <x-ui.field label="Bukti pembayaran" for="proof">
        <x-ui.file name="proof" accept="image/jpeg,image/png,application/pdf" :max-kb="2048" hint="PNG/JPG/PDF · maks. 2 MB" />
    </x-ui.field>
    <x-ui.file name="photos[]" multiple accept="image/*" preview />
    The form needs enctype="multipart/form-data". Size/type are re-validated server-side.
--}}
@aware(['for' => null])

@props([
    'name',
    'accept' => null,
    'multiple' => false,
    'maxKb' => 0,
    'hint' => null,
    'id' => null,
    'error' => null,
    'preview' => false,
])

@php
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $fileId = $id ?? $for ?? str_replace('.', '_', $key);
    $errorKey = $error === false ? null : ($error ?? $key);
    $bag = $errors ?? null;
    $invalid = $errorKey && $bag instanceof \Illuminate\Support\ViewErrorBag && ($bag->has($errorKey) || $bag->has($errorKey.'.*'));
    $config = ['maxKb' => (int) $maxKb, 'accept' => (string) $accept, 'multiple' => (bool) $multiple];
@endphp

<div x-data="cfFileDrop(@js($config))" {{ $attributes->only(['class', 'x-on:change'])->class('w-full') }}>
    <label for="{{ $fileId }}" @class(['dropzone cursor-pointer', 'is-invalid' => $invalid])
        x-bind:class="{ 'is-dragging': dragging }"
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="onDrop($event)">
        <x-ui.icon name="arrow-up-tray" class="size-6 text-fg-subtle" />
        <span class="text-sm text-fg">
            Seret berkas ke sini atau <span class="font-semibold text-brand-text underline underline-offset-4">pilih berkas</span>
        </span>
        @if (filled($hint))
            <span class="font-mono text-xs text-fg-subtle">{{ $hint }}</span>
        @endif
        <input type="file" id="{{ $fileId }}" name="{{ $name }}" class="sr-only" x-ref="input"
            x-on:change="onChange()"
            @if ($accept) accept="{{ $accept }}" @endif
            @if ($multiple) multiple @endif
            @if ($invalid) aria-invalid="true" aria-describedby="{{ $fileId }}-error" @endif
            {{ $attributes->except(['class', 'x-on:change']) }}>
    </label>

    <ul class="mt-2 space-y-1" x-show="files.length" x-cloak>
        <template x-for="file in files" :key="file.name">
            <li class="flex items-center gap-2 text-[0.8125rem] text-fg-muted">
                <x-ui.icon name="paper-clip" class="size-4 text-fg-subtle" />
                <span class="truncate" x-text="file.name"></span>
                <span class="num shrink-0 text-fg-subtle" x-text="file.size"></span>
            </li>
        </template>
    </ul>

    @if ($preview)
        <img x-show="preview" x-cloak x-bind:src="preview" alt="Pratinjau berkas" class="no-dim mt-3 max-h-48 rounded-lg border border-line object-contain">
    @endif

    <p class="error-text mt-2" x-show="error" x-cloak role="alert">
        <x-ui.icon name="m-exclamation-circle" />
        <span x-text="error"></span>
    </p>
</div>
