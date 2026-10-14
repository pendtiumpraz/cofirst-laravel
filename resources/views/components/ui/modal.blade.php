{{--
    Accessible dialog (bottom sheet on phones): focus trap, Esc / backdrop to close, focus restored.

    <x-ui.modal name="tambah-kelas" title="Tambah kelas" description="Isi data kelas baru." max-width="lg">
        <x-slot:trigger><x-ui.button icon="plus">Tambah kelas</x-ui.button></x-slot:trigger>
        …form fields…
        <x-slot:footer>
            <x-ui.button variant="secondary" x-on:click="hide()">Batal</x-ui.button>
            <x-ui.button type="submit" form="form-kelas">Simpan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    Open/close from anywhere: $dispatch('open-modal', 'tambah-kelas') / $dispatch('close-modal', 'tambah-kelas')
    Inside the modal: hide() or $dispatch('close'). :show="$errors->any()" re-opens it after validation errors.
    max-width: sm | md | lg (default) | xl | 2xl | 4xl
--}}
@props([
    'name',
    'title' => null,
    'description' => null,
    'maxWidth' => 'lg',
    'show' => false,
    'closeable' => true,
])

@php
    $modalId = 'modal-'.\Illuminate\Support\Str::slug($name);
    $widthClass = match ($maxWidth) {
        'sm' => 'modal-sm',
        'md' => 'modal-md',
        'xl' => 'modal-xl',
        '2xl' => 'modal-2xl',
        '4xl' => 'modal-4xl',
        default => 'modal-lg',
    };
@endphp

<div x-data="cfModal(@js($name), @js((bool) $show))"
    x-on:open-modal.window="onOpen($event)"
    x-on:close-modal.window="onClose($event)"
    {{ $attributes->class('contents') }}>
    @isset($trigger)
        <span class="contents" x-on:click="show()">{{ $trigger }}</span>
    @endisset

    <template x-teleport="body" x-on:close.stop="hide()">
        <div class="modal-root" x-show="open" x-cloak
            @if ($closeable) x-on:keydown.escape.prevent.stop="hide()" @endif>
            <div class="modal-backdrop" x-show="open"
                x-transition:enter="transition-opacity duration-base ease-cf" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-fast" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @if ($closeable) x-on:click="hide()" @endif aria-hidden="true"></div>

            <div id="{{ $modalId }}" role="dialog" aria-modal="true"
                @if (filled($title)) aria-labelledby="{{ $modalId }}-title" @endif
                @if (filled($description)) aria-describedby="{{ $modalId }}-description" @endif
                class="modal-panel {{ $widthClass }}"
                x-show="open" x-trap.inert.noscroll="open"
                x-transition:enter="transition duration-slow ease-cf" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition duration-fast" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="modal-handle" aria-hidden="true"></div>

                @if (filled($title) || $closeable)
                    <div class="modal-header">
                        <div class="min-w-0">
                            @if (filled($title))
                                <h2 id="{{ $modalId }}-title" class="modal-title">{{ $title }}</h2>
                            @endif
                            @if (filled($description))
                                <p id="{{ $modalId }}-description" class="modal-description">{{ $description }}</p>
                            @endif
                        </div>
                        @if ($closeable)
                            <button type="button" class="btn btn-ghost btn-icon btn-sm -mr-2 -mt-1 shrink-0" x-on:click="hide()" aria-label="Tutup">
                                <x-ui.icon name="x-mark" />
                            </button>
                        @endif
                    </div>
                @endif

                <div class="modal-body">{{ $slot }}</div>

                @isset($footer)
                    <div {{ $footer->attributes->class('modal-footer') }}>{{ $footer }}</div>
                @endisset
            </div>
        </div>
    </template>
</div>
