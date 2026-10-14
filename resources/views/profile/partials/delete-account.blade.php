{{--
    Hapus akun: account erasure is handled by the Privacy Centre (UU PDP right to erasure,
    with retention rules), so this card only links there. Hidden until the centre exists.
    Variable: $privacyUrl.
--}}
@if ($privacyUrl)
    @php
        $slaHours = max(1, (int) (function_exists('setting') ? setting('privacy.dsr_sla_hours', 72) : 72));
        $slaText = $slaHours % 24 === 0 ? ($slaHours / 24).'×24 jam' : $slaHours.' jam';
    @endphp
    <section id="hapus-akun" class="form-section scroll-mt-24" aria-labelledby="hapus-akun-title">
        <div class="form-section-intro">
            <h2 id="hapus-akun-title" class="section-title">Hapus akun</h2>
            <p class="mt-1.5 text-sm leading-6 text-fg-muted">
                Anda berhak meminta penghapusan data pribadi (UU PDP Pasal 8). Permintaan ditanggapi paling lambat
                {{ $slaText }}.
            </p>
        </div>

        <div class="form-section-body">
            <x-ui.card variant="callout" style="--callout-c: var(--cf-danger)">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                    <span class="grid size-10 shrink-0 place-items-center rounded-md bg-danger-soft text-danger">
                        <x-ui.icon name="user-minus" class="size-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-fg">Ajukan penghapusan akun lewat Pusat Privasi</p>
                        <ul role="list" class="mt-2 space-y-1.5 text-sm leading-6 text-fg-muted">
                            <li class="flex gap-2"><x-ui.icon name="m-check" class="mt-1 size-4 text-fg-subtle" />Unduh salinan data Anda terlebih dahulu bila diperlukan.</li>
                            <li class="flex gap-2"><x-ui.icon name="m-check" class="mt-1 size-4 text-fg-subtle" />Data pribadi dihapus atau dianonimkan; catatan pembayaran disimpan sesuai kewajiban hukum.</li>
                            <li class="flex gap-2"><x-ui.icon name="m-check" class="mt-1 size-4 text-fg-subtle" />Orang tua/wali dapat mengajukan penghapusan akun anak.</li>
                        </ul>
                        <div class="mt-5">
                            <x-ui.button variant="danger-ghost" icon="arrow-top-right-on-square" :href="$privacyUrl" class="w-full sm:w-auto">
                                Buka Pusat Privasi
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </section>
@endif
