{{--
    Delete button + confirmation dialog + DELETE form (CSRF included).

    <x-ui.confirm-delete :action="route('admin.classes.destroy', $class)"
        title="Hapus kelas ini?" message="Jadwal, absensi dan laporan kelas ikut terhapus. Tindakan ini tidak dapat dibatalkan." />
    <x-ui.confirm-delete :action="…" icon-only size="xs" />                         row action (ghost trash icon)
    <x-ui.confirm-delete :action="…" confirm-phrase="HAPUS" variant="danger" label="Hapus akun" />   typed confirmation
    Optional slot: extra details shown in the dialog. Optional `trigger` slot replaces the button.
    method: DELETE (default) | POST | PATCH … (e.g. archive). variant: danger-ghost (default) | danger | secondary | ghost | link
--}}
@props([
    'action',
    'title' => 'Hapus data ini?',
    'message' => 'Tindakan ini tidak dapat dibatalkan.',
    'label' => 'Hapus',
    'confirmText' => 'Ya, hapus',
    'cancelText' => 'Batal',
    'variant' => 'danger-ghost',
    'size' => 'sm',
    'icon' => 'trash',
    'iconOnly' => false,
    'method' => 'DELETE',
    'confirmPhrase' => null,
    'name' => null,
])

@php
    $modalName = $name ?? 'hapus-'.substr(md5($action.'|'.$title), 0, 10);
    $formId = 'form-'.$modalName;
    $verb = strtoupper($method);
@endphp

<x-ui.modal :name="$modalName" :title="$title" max-width="md" {{ $attributes }}>
    <x-slot:trigger>
        @isset($trigger)
            {{ $trigger }}
        @else
            <x-ui.button :variant="$variant" :size="$size" :icon="$icon" :label="$iconOnly ? $label : null">
                @unless ($iconOnly)
                    {{ $label }}
                @endunless
            </x-ui.button>
        @endisset
    </x-slot:trigger>

    <form id="{{ $formId }}" method="POST" action="{{ $action }}"
        @if ($confirmPhrase) x-data="{ typed: '' }" @endif>
        @csrf
        @if ($verb !== 'POST')
            @method($verb)
        @endif

        @if (filled($message))
            <p class="text-fg-muted">{{ $message }}</p>
        @endif

        @if ($slot->hasActualContent())
            <div class="mt-3 text-sm text-fg-muted">{{ $slot }}</div>
        @endif

        @if ($confirmPhrase)
            <div class="field mt-4">
                <label for="{{ $formId }}-phrase" class="label">
                    Ketik <span class="font-mono font-semibold">{{ $confirmPhrase }}</span> untuk melanjutkan
                </label>
                <input id="{{ $formId }}-phrase" type="text" class="input" autocomplete="off" x-model="typed" spellcheck="false">
            </div>
        @endif

        <div class="-mx-6 -mb-4 mt-6 flex flex-col-reverse gap-2 border-t border-line bg-surface-2/50 px-6 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="hide()">{{ $cancelText }}</button>
            <button type="submit" class="btn btn-danger"
                @if ($confirmPhrase) disabled x-bind:disabled="typed !== @js($confirmPhrase)" @endif>
                <x-ui.icon name="trash" />
                <span>{{ $confirmText }}</span>
            </button>
        </div>
    </form>
</x-ui.modal>
