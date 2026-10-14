{{-- Foto profil (POST profile.photo.upload / DELETE profile.photo.delete). Variables: $user, $photoMaxKb. --}}
@php
    $maxMb = rtrim(rtrim(number_format($photoMaxKb / 1024, 1, ',', '.'), '0'), ',');
@endphp

<section id="foto" class="form-section scroll-mt-24" aria-labelledby="foto-title">
    <div class="form-section-intro">
        <h2 id="foto-title" class="section-title">Foto profil</h2>
        <p class="mt-1.5 text-sm leading-6 text-fg-muted">
            Membantu pengajar dan teman sekelas mengenali Anda. Tanpa foto, inisial nama Anda yang ditampilkan.
        </p>
        <p class="mt-3 flex items-start gap-2 text-sm leading-6 text-fg-muted">
            <x-ui.icon name="shield-check" class="mt-0.5 size-[1.125rem] text-success" />
            <span>Metadata foto — termasuk lokasi GPS dan info perangkat — dihapus otomatis saat diunggah.</span>
        </p>
    </div>

    <div class="form-section-body">
        <x-ui.card>
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <div class="flex shrink-0 flex-col items-center gap-3">
                    <x-ui.avatar :user="$user" size="2xl" />
                    @if ($user->profile_photo_path)
                        <x-ui.confirm-delete :action="route('profile.photo.delete')" name="hapus-foto-profil"
                            label="Hapus foto" title="Hapus foto profil?"
                            message="Foto akan dihapus dari server dan diganti inisial nama Anda. Anda dapat mengunggah foto baru kapan saja."
                            confirm-text="Ya, hapus foto" />
                    @endif
                </div>

                <form method="POST" action="{{ route('profile.photo.upload') }}" enctype="multipart/form-data" class="min-w-0 flex-1"
                    x-data="{ missing: false, submitting: false }"
                    x-on:change="missing = false"
                    x-on:submit="if (! $el.querySelector('input[type=file]').files.length) { $event.preventDefault(); missing = true; return; } submitting = true"
                    x-on:pageshow.window="submitting = false">
                    @csrf

                    <x-ui.field label="{{ $user->profile_photo_path ? 'Ganti foto' : 'Unggah foto' }}" for="photo">
                        <x-ui.file name="photo" accept="image/jpeg,image/png,image/webp" :max-kb="$photoMaxKb"
                            hint="JPG, PNG, atau WebP · maks. {{ $maxMb }} MB · minimal 64×64 piksel" preview />
                    </x-ui.field>

                    <p class="error-text mt-2" x-show="missing" x-cloak role="alert">
                        <x-ui.icon name="m-exclamation-circle" />
                        <span>Pilih foto terlebih dahulu.</span>
                    </p>

                    <p class="mt-3 text-[0.8125rem] leading-5 text-fg-subtle">
                        Gunakan foto wajah yang jelas. Foto persegi tampil paling baik.
                    </p>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <x-ui.button type="submit" icon="arrow-up-tray" class="w-full sm:w-auto"
                            loading-when="submitting" loading-text="Mengunggah…">
                            Simpan foto
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.card>
    </div>
</section>
