{{--
    One policy version: preview, metadata, publish form (drafts), compare link.
    Variables: $document, $html, $previous (PolicyDocument|null), $isCurrent, $reacceptable.
--}}
@php
    use App\Services\Privacy\PolicyService;

    [$tone, $status] = match (true) {
        $document->isDraft() => ['warning', 'Draf'],
        $document->isScheduled() => ['info', 'Terjadwal'],
        $isCurrent => ['success', 'Berlaku'],
        default => ['neutral', 'Digantikan'],
    };
    $crumbs = (Route::has('admin.dashboard') ? ['Admin' => route('admin.dashboard')] : [])
        + ['Kebijakan & versi' => route('admin.privacy.policies.index'), $document->type_label.' v'.$document->version => null];
    $tz = (string) setting('general.timezone', 'Asia/Jakarta');
@endphp

<x-app-layout :title="$document->title.' v'.$document->version">
    <x-ui.page-header :title="$document->title" :breadcrumbs="$crumbs" :subtitle="$document->summary">
        <x-slot:badge>
            <x-ui.badge mono>v{{ $document->version }}</x-ui.badge>
            <x-ui.badge :tone="$tone" dot>{{ $status }}</x-ui.badge>
        </x-slot:badge>
        <x-slot:meta>
            <span>{{ $document->type_label }}</span>
            <span>Revisi #{{ $document->revision }}</span>
            @if ($document->isPublished())
                <span>Terbit {{ PolicyService::date($document->published_at, true) }}{{ $document->publisher ? ' oleh '.$document->publisher->name : '' }}</span>
            @else
                <span>Diubah {{ PolicyService::date($document->updated_at, true) }}</span>
            @endif
        </x-slot:meta>
        <x-slot:actions>
            @if ($previous)
                <x-ui.button variant="secondary" icon="arrows-right-left" :href="route('admin.privacy.policies.diff', $document)">Bandingkan</x-ui.button>
            @endif
            @if ($document->isDraft())
                <x-ui.button variant="secondary" icon="pencil-square" :href="route('admin.privacy.policies.edit', $document)">Ubah</x-ui.button>
            @else
                <x-ui.button variant="secondary" icon="document-duplicate" :href="route('admin.privacy.policies.create', ['jenis' => $document->type])">Buat versi baru</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-gutter lg:grid-cols-[minmax(0,1fr)_20rem]">
        <x-ui.card padding="none" class="min-w-0 lg:order-1">
            <div class="border-b border-line px-card py-3">
                <p class="eyebrow">Pratinjau</p>
            </div>
            <article class="prose-cf max-w-none px-card py-6">
                {!! $html !!}
            </article>
        </x-ui.card>

        <div class="space-y-gutter lg:order-2">
            @if ($document->isDraft())
                <x-ui.card title="Terbitkan" subtitle="Setelah terbit, isi tidak dapat diubah.">
                    <form method="POST" action="{{ route('admin.privacy.policies.publish', $document) }}" class="space-y-5"
                        data-confirm="Versi {{ $document->version }} akan menjadi bukti hukum dan tidak dapat diubah lagi." data-confirm-title="Terbitkan versi ini?" data-confirm-ok="Terbitkan" data-confirm-tone="primary">
                        @csrf
                        <x-ui.field label="Mulai berlaku" for="effective_at" optional
                            hint="Kosongkan untuk berlaku sekarang. Isi tanggal mendatang agar pengguna dapat membaca perubahan sebelum berlaku (Pasal 21 ayat (2)).">
                            <x-ui.input name="effective_at" type="datetime-local" min="{{ now()->setTimezone($tz)->format('Y-m-d\TH:i') }}" />
                        </x-ui.field>
                        @if ($reacceptable)
                            <x-ui.toggle name="requires_reacceptance" value="1" unchecked-value="0" label="Minta persetujuan ulang"
                                description="Pengguna yang masuk diarahkan untuk menyetujui versi ini. Gunakan untuk perubahan penting, misalnya tujuan atau penerima data baru."
                                :checked="(bool) setting('privacy.policy_reacceptance_required', true)" />
                        @endif
                        <x-ui.button type="submit" icon="paper-airplane" block>Terbitkan versi {{ $document->version }}</x-ui.button>
                    </form>
                </x-ui.card>
            @else
                <x-ui.card title="Status">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-fg-muted">{{ $document->isScheduled() ? 'Berlaku mulai' : 'Berlaku sejak' }}</dt>
                            <dd class="text-right font-medium text-fg">{{ PolicyService::date($document->effective_at, true) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-fg-muted">Persetujuan ulang</dt>
                            <dd class="text-right font-medium text-fg">{{ $document->requires_reacceptance ? 'Diminta' : 'Tidak' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-fg-muted">Penerimaan tercatat</dt>
                            <dd class="num font-medium text-fg">{{ number_format($document->acceptances_count, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                    @if ($url = $document->publicUrl())
                        <x-slot:footer>
                            <x-ui.button variant="ghost" size="sm" icon="arrow-top-right-on-square" :href="Route::has('privacy.history.show') ? route('privacy.history.show', $document->id) : $url" target="_blank" rel="noopener">Buka halaman publik</x-ui.button>
                        </x-slot:footer>
                    @endif
                </x-ui.card>
            @endif

            @if (filled($document->changelog))
                <x-ui.card title="Catatan perubahan">
                    <p class="whitespace-pre-line text-sm leading-relaxed text-fg-muted">{{ $document->changelog }}</p>
                </x-ui.card>
            @endif

            @if ($document->isDraft())
                <div class="flex justify-end">
                    <x-ui.confirm-delete :action="route('admin.privacy.policies.destroy', $document)" label="Hapus draf"
                        title="Hapus draf ini?" message="Draf versi {{ $document->version }} akan dihapus. Versi yang sudah terbit tidak terpengaruh." />
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
