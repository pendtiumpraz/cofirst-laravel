{{--
    Line diff between two versions of one document type.
    Variables: $document, $against (PolicyDocument|null), $versions (other versions), $hunks (PolicyDiff::fold), $stats.
--}}
@php
    $crumbs = (Route::has('admin.dashboard') ? ['Admin' => route('admin.dashboard')] : [])
        + ['Kebijakan & versi' => route('admin.privacy.policies.index'), 'v'.$document->version => route('admin.privacy.policies.show', $document), 'Bandingkan' => null];
    $options = $versions->mapWithKeys(fn ($v) => [$v->id => 'Versi '.$v->version.($v->isDraft() ? ' (draf)' : '')])->all();
@endphp

<x-app-layout :title="'Bandingkan '.$document->type_label">
    <x-ui.page-header title="Bandingkan versi" :breadcrumbs="$crumbs"
        :subtitle="$document->type_label.': perubahan dari versi '.($against?->version ?? '—').' ke versi '.$document->version.'.'">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="arrow-left" :href="route('admin.privacy.policies.show', $document)">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($options !== [])
        <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
            <x-ui.field label="Bandingkan dengan" for="dengan" class="w-full sm:w-64">
                <x-ui.select name="dengan" :options="$options" :value="$against?->id" />
            </x-ui.field>
            <x-ui.button type="submit" variant="secondary">Tampilkan</x-ui.button>
        </form>
    @endif

    @if (! $against)
        <x-ui.empty-state size="page" icon="arrows-right-left" title="Belum ada versi lain untuk dibandingkan."
            description="Perbandingan tersedia setelah dokumen jenis ini memiliki lebih dari satu versi." />
    @else
        <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
            <x-ui.badge tone="success">+{{ $stats['add'] }} baris</x-ui.badge>
            <x-ui.badge tone="danger">−{{ $stats['del'] }} baris</x-ui.badge>
            <span class="text-fg-subtle">Baris yang tidak berubah diringkas.</span>
        </div>

        @if ($stats['add'] === 0 && $stats['del'] === 0)
            <x-ui.alert tone="info">Isi kedua versi sama persis.</x-ui.alert>
        @else
            <div class="overflow-hidden rounded-lg border border-line bg-surface">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse font-mono text-[0.8125rem] leading-6">
                        <caption class="sr-only">Perbedaan isi antara versi {{ $against->version }} dan {{ $document->version }}</caption>
                        <tbody>
                            @foreach ($hunks as $line)
                                @if ($line['type'] === 'skip')
                                    <tr class="bg-surface-2 text-fg-subtle">
                                        <td colspan="2" class="px-4 py-1.5 font-sans text-xs">… {{ $line['count'] }} baris tidak berubah</td>
                                    </tr>
                                @else
                                    <tr @class(['bg-success-soft' => $line['type'] === 'add', 'bg-danger-soft' => $line['type'] === 'del'])>
                                        <td class="w-8 select-none px-2 text-center align-top {{ $line['type'] === 'add' ? 'text-success' : ($line['type'] === 'del' ? 'text-danger' : 'text-fg-subtle') }}">
                                            <span aria-hidden="true">{{ $line['type'] === 'add' ? '+' : ($line['type'] === 'del' ? '−' : '') }}</span>
                                            <span class="sr-only">{{ $line['type'] === 'add' ? 'ditambahkan' : ($line['type'] === 'del' ? 'dihapus' : '') }}</span>
                                        </td>
                                        <td class="whitespace-pre-wrap break-words py-0.5 pr-4 text-fg">{{ $line['text'] === '' ? ' ' : $line['text'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</x-app-layout>
