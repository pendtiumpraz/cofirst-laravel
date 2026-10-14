{{--
    Admin › Pengaturan — one settings group, rendered from config/settings.php.
    Data: $groups, $group, $sections, $values (saved), $records (stored rows + editor),
    $lastChange, $canEdit, $preview (active session previews).
    Per-field / per-group reset buttons submit the separate #settings-reset form.
--}}
@php
    $customised = $records->count();
@endphp

<x-app-layout title="Pengaturan">
    <x-ui.page-header
        title="Pengaturan"
        subtitle="Identitas lembaga, tampilan, aturan akademik, keamanan dan kepatuhan privasi — semua dari satu tempat."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pengaturan' => null]" />

    <div class="grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-10">
        @include('admin.settings.partials.nav')

        <div class="min-w-0 space-y-6">
            @include('admin.settings.partials.group-header')

            @unless ($canEdit)
                <x-ui.alert tone="neutral" icon="lock-closed" title="Hanya dapat dilihat">
                    Bagian {{ $group['label'] }} hanya dapat diubah oleh superadmin karena memengaruhi keamanan dan kepatuhan UU PDP.
                </x-ui.alert>
            @endunless

            @if ($group['key'] === 'appearance' && $preview !== [])
                @include('admin.settings.partials.preview-banner')
            @endif

            <form id="settings-form" method="POST" action="{{ route('admin.settings.update', $group['key']) }}"
                enctype="multipart/form-data" novalidate
                x-data="{ dirty: false, saving: false }"
                x-on:input="dirty = true"
                x-on:change="dirty = true"
                x-on:submit.window="saving = true"
                x-init="window.addEventListener('beforeunload', (e) => { if (dirty && ! saving) { e.preventDefault(); e.returnValue = ''; } })">
                @csrf
                @method('PUT')

                <fieldset class="m-0 min-w-0 border-0 p-0" @disabled(! $canEdit)>
                    <legend class="sr-only">{{ $group['label'] }}</legend>
                    <div class="card">
                        <div class="card-body">
                            @foreach ($sections as $section)
                                @include('admin.settings.partials.section', ['section' => $section, 'index' => $loop->index])
                            @endforeach
                        </div>
                    </div>
                </fieldset>

                @if ($canEdit)
                    @include('admin.settings.partials.save-bar')
                @endif
            </form>

            @if ($canEdit)
                {{-- Reason: reset buttons live inside the main form but submit this one (form="settings-reset"). --}}
                <form id="settings-reset" method="POST" action="{{ route('admin.settings.reset', $group['key']) }}" class="hidden">
                    @csrf
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
