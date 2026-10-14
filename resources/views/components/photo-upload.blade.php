{{--
    Legacy photo upload (props: name, currentPhoto, accept, maxSize KB, enableCrop, aspectRatio),
    restyled to the design system. Behaviour lives in Alpine.data('photoUpload') (resources/js/components.js):
    drag & drop really attaches the file, no third-party scripts are loaded.
    enableCrop keeps the hidden crop_data input for compatibility; the preview is shown at the
    target aspect ratio (server-side cropping/EXIF stripping is the upload controller's job).
--}}
@props([
    'name' => 'photo',
    'currentPhoto' => null,
    'accept' => 'image/*',
    'maxSize' => 5120, // KB
    'enableCrop' => false,
    'aspectRatio' => 1
])

@php
    $config = [
        'maxSize' => (int) $maxSize,
        'enableCrop' => (bool) $enableCrop,
        'aspectRatio' => (float) $aspectRatio ?: 1,
    ];
    $maxLabel = $maxSize >= 1024
        ? rtrim(rtrim(number_format($maxSize / 1024, 1, ',', '.'), '0'), ',').' MB'
        : $maxSize.' KB';
    $inputId = 'photo-upload-'.\Illuminate\Support\Str::slug($name).'-'.substr(md5((string) $currentPhoto.$name), 0, 6);
@endphp

<div x-data="photoUpload(@js($config))" {{ $attributes->merge(['class' => 'photo-upload-container']) }}>
    <div class="space-y-4">
        @if ($currentPhoto)
            <div class="current-photo" x-show="!preview">
                <img src="{{ $currentPhoto }}" alt="Foto saat ini" class="size-32 rounded-lg border border-line object-cover">
            </div>
        @endif

        <div
            x-on:drop.prevent="handleDrop($event)"
            x-on:dragover.prevent="isDragging = true"
            x-on:dragleave.prevent="isDragging = false"
            x-bind:class="{ 'is-dragging': isDragging }"
            class="dropzone"
        >
            <input
                type="file"
                id="{{ $inputId }}"
                name="{{ $name }}"
                x-on:change="handleFileSelect($event)"
                accept="{{ $accept }}"
                class="sr-only"
                x-ref="fileInput"
            >

            <div x-show="!preview" class="flex flex-col items-center gap-2">
                <x-ui.icon name="photo" class="size-10 text-fg-subtle" />
                <p class="text-sm text-fg">
                    Seret foto ke sini atau
                    <label for="{{ $inputId }}" class="cursor-pointer font-semibold text-brand-text underline underline-offset-4">pilih berkas</label>
                </p>
                <p class="font-mono text-xs text-fg-subtle">JPG/PNG/WebP · maks. {{ $maxLabel }}</p>
            </div>

            <div x-show="preview" x-cloak class="relative">
                <img x-bind:src="preview" alt="Pratinjau foto"
                    class="mx-auto max-h-64 rounded-lg object-cover"
                    x-bind:style="enableCrop ? 'aspect-ratio: ' + aspectRatio + '; width: 16rem; max-width: 100%' : ''">
                <button
                    type="button"
                    x-on:click="removeFile()"
                    class="btn btn-secondary btn-icon btn-sm absolute right-2 top-2"
                    aria-label="Hapus pilihan foto"
                >
                    <x-ui.icon name="x-mark" />
                </button>
            </div>
        </div>

        @if ($enableCrop)
            <input type="hidden" name="crop_data" x-model="cropDataJson">
        @endif

        <p x-show="error" x-cloak class="error-text" role="alert">
            <x-ui.icon name="m-exclamation-circle" />
            <span x-text="error"></span>
        </p>
    </div>
</div>
