{{--
    Admin › Privasi & PDP › Kebijakan & versi. Variables: $documents (paginator), $type (filter|null),
    $current (type => current PolicyDocument meta|null).
--}}
@php
    use App\Models\Privacy\PolicyDocument;
    use App\Services\Privacy\PolicyService;

    $short = ['privacy_notice' => 'Kebijakan Privasi', 'terms' => 'Syarat & Ketentuan', 'cookie_policy' => 'Cookie', 'children_notice' => 'Anak & orang tua', 'dpa_template' => 'Templat DPA'];
    $tabs = ['semua' => ['label' => 'Semua', 'href' => route('admin.privacy.policies.index')]]
        + collect($short)->map(fn ($label, $key) => ['label' => $label, 'href' => route('admin.privacy.policies.index', ['jenis' => $key])])->all();
    $missing = collect(PolicyDocument::TYPES)->filter(fn ($label, $key) => empty($current[$key]));
@endphp

<x-app-layout title="Kebijakan & versi">
    <x-ui.page-header title="Kebijakan & versi"
        subtitle="Dokumen hukum berversi. Versi yang sudah terbit tidak dapat diubah; setiap perubahan menjadi versi baru yang diumumkan sebelum berlaku (UU PDP Pasal 21 ayat (2)).">
        <x-slot:actions>
            <x-ui.dropdown label="Buat versi baru" icon="plus" variant="primary" align="right" width="72">
                <p class="dropdown-label">Jenis dokumen</p>
                @foreach (PolicyDocument::TYPES as $key => $label)
                    <x-ui.dropdown-item :href="route('admin.privacy.policies.create', ['jenis' => $key])" icon="document-plus">{{ $label }}</x-ui.dropdown-item>
                @endforeach
            </x-ui.dropdown>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($missing->isNotEmpty())
        <x-ui.alert tone="info" title="Belum semua dokumen diterbitkan" class="mb-6">
            {{ $missing->implode(', ') }} belum memiliki versi terbit, sehingga halaman publiknya memakai teks bawaan.
            Buat draf dari teks bawaan, tinjau, lalu terbitkan.
        </x-ui.alert>
    @endif

    <x-ui.tabs class="overflow-x-auto scrollbar-none" label="Saring jenis dokumen" :tabs="$tabs" :active="$type ?? 'semua'" />

    <x-ui.table caption="Semua versi dokumen" mobile="stack" class="mt-4">
        <x-slot:head>
            <x-ui.th>Dokumen</x-ui.th>
            <x-ui.th>Versi</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Berlaku</x-ui.th>
            <x-ui.th numeric>Penerimaan</x-ui.th>
            <x-ui.th><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($documents as $doc)
            @php
                $isCurrent = ($current[$doc->type] ?? null)?->id === $doc->id;
                [$tone, $status] = match (true) {
                    $doc->isDraft() => ['warning', 'Draf'],
                    $doc->isScheduled() => ['info', 'Terjadwal'],
                    $isCurrent => ['success', 'Berlaku'],
                    default => ['neutral', 'Digantikan'],
                };
            @endphp
            <tr>
                <td class="cell-primary" data-label="Dokumen">
                    <a href="{{ route('admin.privacy.policies.show', $doc) }}" class="font-medium text-fg hover:text-brand-text">{{ $doc->title }}</a>
                    @if ($doc->title !== $doc->type_label)
                        <span class="cell-secondary block">{{ $doc->type_label }}</span>
                    @endif
                </td>
                <td data-label="Versi"><x-ui.badge mono>v{{ $doc->version }}</x-ui.badge></td>
                <td data-label="Status"><x-ui.badge :tone="$tone" dot>{{ $status }}</x-ui.badge></td>
                <td data-label="Berlaku">{{ $doc->isDraft() ? '—' : PolicyService::date($doc->effective_at) }}</td>
                <td class="num" data-label="Penerimaan">{{ number_format($doc->acceptances_count, 0, ',', '.') }}</td>
                <td class="cell-actions">
                    <x-ui.button variant="ghost" size="sm" icon="eye" :href="route('admin.privacy.policies.show', $doc)" label="Lihat {{ $doc->title }} versi {{ $doc->version }}" />
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="document-text" title="Belum ada versi untuk jenis ini."
                description="Buat draf baru dari teks bawaan, tinjau pratinjaunya, lalu terbitkan." />
        </x-slot:empty>

        @if ($documents->hasPages())
            <x-slot:footer>{{ $documents->links() }}</x-slot:footer>
        @endif
    </x-ui.table>
</x-app-layout>
