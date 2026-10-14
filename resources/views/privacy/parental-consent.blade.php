{{--
    Guardian consent page reached from the signed e-mail link (UU PDP Pasal 25 ayat (2); PP 33/2026 Pasal 38).
    Variables: $consentRequest, $child, $required, $optional (ConsentPurpose collections),
    $notice, $noticeHtml (children's notice), $action (signed URL to post to).
--}}
@php
    use App\Services\Privacy\PolicyService;

    $firstName = strtok((string) $child->name, ' ');
    $age = $child->profile?->birth_date?->age;
    $docLink = fn ($route, $label) => Route::has($route)
        ? '<a href="'.e(route($route)).'" target="_blank" rel="noopener" class="link font-medium">'.e($label).'</a>'
        : e($label);
@endphp

<x-public-layout title="Persetujuan orang tua/wali" description="Tinjau dan setujui pendaftaran anak Anda di Coding First.">
    <div class="mx-auto max-w-2xl px-4 pb-16 pt-10 sm:px-6 lg:pb-24 lg:pt-14">
        <header>
            <p class="eyebrow">Persetujuan orang tua/wali</p>
            <h1 class="mt-3 font-display text-[2rem] font-title leading-tight tracking-display text-fg sm:text-[2.5rem]">
                {{ $firstName }} ingin bergabung di {{ setting('general.site_name', 'Coding First') }}
            </h1>
            <p class="mt-4 text-[1.0625rem] leading-relaxed text-fg-muted">
                Karena {{ $firstName }} berusia di bawah {{ (int) setting('privacy.child_age_threshold', 18) }} tahun, akunnya baru aktif setelah Anda menyetujui.
                Tinjau informasinya, lalu pilih <strong class="text-fg">Setujui</strong> atau <strong class="text-fg">Tolak</strong>.
                Tautan ini berlaku sampai {{ PolicyService::date($consentRequest->expires_at, true) }}.
            </p>
        </header>

        <x-ui.card class="mt-8" title="Data pendaftaran" subtitle="Hanya data yang diperlukan untuk kegiatan belajar.">
            <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-fg-subtle">Nama anak</dt>
                    <dd class="mt-0.5 font-medium text-fg">{{ $child->name }}</dd>
                </div>
                <div>
                    <dt class="text-fg-subtle">Email akun anak</dt>
                    <dd class="mt-0.5 break-all font-mono text-fg">{{ $child->email }}</dd>
                </div>
                <div>
                    <dt class="text-fg-subtle">Usia</dt>
                    <dd class="mt-0.5 font-medium text-fg">{{ $age !== null ? $age.' tahun' : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-fg-subtle">Didaftarkan</dt>
                    <dd class="mt-0.5 font-medium text-fg">{{ PolicyService::date($consentRequest->created_at, true) }}</dd>
                </div>
            </dl>
        </x-ui.card>

        @if ($noticeHtml)
            <details class="group mt-6 rounded-lg border border-line bg-surface">
                <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 px-card py-3 text-sm font-semibold text-fg">
                    <span class="flex items-center gap-2"><x-ui.icon name="document-text" class="size-5 text-fg-subtle" />{{ $notice->title }}</span>
                    <x-ui.icon name="chevron-down" class="size-4 text-fg-subtle transition-transform duration-fast group-open:rotate-180 motion-reduce:transition-none" />
                </summary>
                <div class="prose-cf max-h-[60vh] overflow-y-auto border-t border-line px-card py-5 text-[0.9375rem] scrollbar-thin">
                    {!! $noticeHtml !!}
                </div>
            </details>
        @endif

        <form method="POST" action="{{ $action }}" class="mt-8 space-y-8" x-data="{ decision: @js(old('decision', 'grant')), busy: false }" x-on:submit="busy = true">
            @csrf
            <input type="hidden" name="decision" value="grant" x-bind:value="decision">

            @if ($errors->any())
                <x-ui.alert tone="danger" title="Periksa kembali isian Anda">Ada {{ $errors->count() }} isian yang perlu dilengkapi.</x-ui.alert>
            @endif

            <fieldset class="space-y-5">
                <legend class="eyebrow mb-4">Tentang Anda</legend>
                <x-ui.field label="Nama Anda" for="guardian_name" required>
                    <x-ui.input name="guardian_name" autocomplete="name" maxlength="120" :value="old('guardian_name', $consentRequest->guardian_name)" />
                </x-ui.field>
                <x-ui.field for="guardian_declaration" error="guardian_declaration">
                    <x-ui.checkbox name="guardian_declaration" value="1"
                        :label="'Saya adalah orang tua atau wali sah dari '.$child->name.'.'"
                        description="Kami mencatat pernyataan ini beserta waktu dan alamat IP sebagai bukti persetujuan (UU PDP Pasal 24)." />
                </x-ui.field>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="eyebrow mb-4">Persetujuan yang diperlukan</legend>
                <x-ui.field for="required_consent" error="required_consent">
                    <x-ui.checkbox name="required_consent" value="1">
                        Saya menyetujui pemrosesan data {{ $firstName }} untuk kegiatan belajar sebagaimana dijelaskan dalam
                        {!! $docLink('privacy.notice', 'Kebijakan Privasi') !!} dan {!! $docLink('legal.terms', 'Syarat & Ketentuan') !!}.
                    </x-ui.checkbox>
                </x-ui.field>
                @foreach ($required as $purpose)
                    <p class="pl-8 text-[0.8125rem] leading-relaxed text-fg-subtle">{{ $purpose->description }}</p>
                @endforeach
            </fieldset>

            @if ($optional->isNotEmpty())
                <fieldset class="space-y-4">
                    <legend class="eyebrow mb-1">Persetujuan opsional</legend>
                    <p class="text-[0.8125rem] text-fg-subtle">Tidak wajib dan tidak memengaruhi kelas {{ $firstName }}. Dapat ditarik kapan saja.</p>
                    @foreach ($optional as $purpose)
                        <x-ui.checkbox name="consents[]" :value="$purpose->key" :label="$purpose->name" :description="$purpose->description" />
                    @endforeach
                </fieldset>
            @endif

            <div class="grid gap-3 border-t border-line pt-6 sm:grid-cols-2">
                <x-ui.button type="submit" name="decision" value="grant" size="lg" block icon="check" x-on:click="decision = 'grant'" loading-when="busy && decision === 'grant'" loading-text="Menyimpan…">
                    Setujui &amp; aktifkan akun
                </x-ui.button>
                <x-ui.button type="submit" name="decision" value="decline" size="lg" block variant="secondary" icon="x-mark" x-on:click="decision = 'decline'"
                    data-confirm="Data pendaftaran {{ $firstName }} akan dihapus dan akunnya tidak diaktifkan." data-confirm-title="Tolak pendaftaran?" data-confirm-ok="Ya, tolak" data-confirm-tone="danger">
                    Tolak pendaftaran
                </x-ui.button>
            </div>
            <p class="text-[0.8125rem] leading-relaxed text-fg-subtle">
                Bila Anda menolak, akun tidak diaktifkan dan data pendaftaran {{ $firstName }} kami hapus. Bila Anda tidak mengenal pendaftaran ini, pilih Tolak.
            </p>
        </form>
    </div>
</x-public-layout>
