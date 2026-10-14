{{--
    Per-student narrative fields (notes, Bloom's taxonomy, follow-up).
    Params: $prefix (e.g. "students[3]" or ""), $values (array of current values), $bloom (ClassReportService::bloomFields()),
    $open (bool: expand the Bloom section)
--}}
@php
    $name = fn (string $field) => $prefix === '' ? $field : $prefix.'['.$field.']';
    $id = fn (string $field) => trim(str_replace(['[', ']'], ['_', ''], $name($field)), '_');
@endphp

<div class="space-y-4">
    <x-ui.field label="Catatan & rekomendasi" :for="$id('notes_recommendations')" optional hint="Terlihat oleh siswa dan orang tua.">
        <x-ui.textarea :name="$name('notes_recommendations')" rows="2" :value="$values['notes_recommendations'] ?? null" maxlength="2000" />
    </x-ui.field>

    <details class="group rounded-lg border border-line" @if (! empty($open)) open @endif>
        <summary class="flex min-h-[44px] cursor-pointer list-none items-center justify-between gap-3 px-4 py-2 text-sm font-medium text-fg hover:bg-surface-2 [&::-webkit-details-marker]:hidden">
            <span>Capaian taksonomi Bloom <span class="font-normal text-fg-subtle">(opsional)</span></span>
            <x-ui.icon name="chevron-down" class="shrink-0 text-fg-subtle transition-transform duration-fast group-open:rotate-180 motion-reduce:transition-none" />
        </summary>
        <div class="grid gap-4 border-t border-line p-4 sm:grid-cols-2">
            @foreach ($bloom as $field => [$label, $hint])
                <x-ui.field :label="$label" :for="$id($field)" :hint="$hint" optional>
                    <x-ui.textarea :name="$name($field)" rows="2" :value="$values[$field] ?? null" maxlength="2000" />
                </x-ui.field>
            @endforeach
            <x-ui.field label="Tindak lanjut (internal)" :for="$id('follow_up_notes')" optional hint="Hanya terlihat oleh pengajar dan admin." class="sm:col-span-2">
                <x-ui.textarea :name="$name('follow_up_notes')" rows="2" :value="$values['follow_up_notes'] ?? null" maxlength="2000" />
            </x-ui.field>
        </div>
    </details>
</div>
