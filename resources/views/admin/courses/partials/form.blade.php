{{-- Shared course form. Expects $course (Course, possibly unsaved) and $levels. --}}
@php
    $editing = $course->exists;
    $price = $editing ? number_format($course->price_rupiah, 0, ',', '.') : '';
@endphp

<section class="form-section" aria-labelledby="sec-info">
    <div class="form-section-intro">
        <h2 id="sec-info" class="card-title">Informasi kursus</h2>
        <p class="mt-1 text-sm text-fg-muted">Nama dan deskripsi tampil di katalog program dan landing page.</p>
    </div>
    <div class="form-section-body">
        <x-ui.field label="Nama kursus" for="name" required>
            <x-ui.input name="name" :value="$course->name" required maxlength="150" placeholder="cth. Python Dasar untuk Remaja" />
        </x-ui.field>
        <x-ui.field label="Slug URL" for="slug" optional hint="Dibuat otomatis dari nama bila dikosongkan. Hanya huruf kecil, angka dan tanda hubung.">
            <x-ui.input name="slug" :value="$course->slug" prefix="/program/" maxlength="160" class="font-mono" />
        </x-ui.field>
        <x-ui.field label="Deskripsi" for="description" required hint="Jelaskan untuk siapa kursus ini dan apa yang dipelajari.">
            <x-ui.textarea name="description" rows="5" :value="$course->description" required maxlength="5000" />
        </x-ui.field>
        <x-ui.field label="Tingkat" for="level" required>
            <x-ui.select name="level" :options="$levels" :value="$course->level ?? 'beginner'" required />
        </x-ui.field>
        <x-ui.field label="Manfaat" for="benefits" optional hint="Satu manfaat per baris, mis. “Sertifikat setelah lulus”.">
            <x-ui.textarea name="benefits" rows="4" :value="$course->benefits" maxlength="3000" />
        </x-ui.field>
    </div>
</section>

<section class="form-section" aria-labelledby="sec-harga">
    <div class="form-section-intro">
        <h2 id="sec-harga" class="card-title">Harga & durasi</h2>
        <p class="mt-1 text-sm text-fg-muted">Harga dalam rupiah tanpa desimal. Tagihan dibuat oleh tim keuangan berdasarkan harga ini.</p>
    </div>
    <div class="form-section-body">
        <x-ui.field label="Harga" for="price" required>
            <x-ui.input name="price" :value="$price" prefix="Rp" inputmode="numeric" class="num" required placeholder="1.500.000"
                x-data x-on:blur="$el.value = $el.value.replace(/[^0-9]/g, '') ? Number($el.value.replace(/[^0-9]/g, '')).toLocaleString('id-ID') : ''" />
        </x-ui.field>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.field label="Durasi" for="duration_weeks" required>
                <x-ui.input name="duration_weeks" type="number" min="1" max="104" :value="$course->duration_weeks" suffix="minggu" required />
            </x-ui.field>
            <x-ui.field label="Total jam belajar" for="duration_hours" optional>
                <x-ui.input name="duration_hours" type="number" min="1" max="1000" :value="$course->duration_hours" suffix="jam" />
            </x-ui.field>
        </div>
    </div>
</section>

<section class="form-section" aria-labelledby="sec-media">
    <div class="form-section-intro">
        <h2 id="sec-media" class="card-title">Gambar sampul <span class="text-sm font-normal text-fg-subtle">(opsional)</span></h2>
        <p class="mt-1 text-sm text-fg-muted">Rasio 16:9 disarankan. Jangan gunakan foto siswa tanpa persetujuan orang tua.</p>
    </div>
    <div class="form-section-body">
        @if ($editing && $course->thumbnail)
            <div class="flex items-center gap-4">
                <img src="{{ asset('storage/'.$course->thumbnail) }}" alt="Sampul {{ $course->name }}" class="h-20 w-36 rounded-md border border-line object-cover">
                <x-ui.checkbox name="remove_thumbnail" value="1" label="Hapus gambar saat ini" />
            </div>
        @endif
        <x-ui.field label="Unggah gambar" for="thumbnail">
            <x-ui.file name="thumbnail" accept="image/jpeg,image/png,image/webp" :max-kb="2048" hint="JPG/PNG/WEBP · maks. 2 MB" preview />
        </x-ui.field>
    </div>
</section>

<section class="form-section" aria-labelledby="sec-publikasi">
    <div class="form-section-intro">
        <h2 id="sec-publikasi" class="card-title">Publikasi</h2>
    </div>
    <div class="form-section-body">
        <x-ui.toggle name="is_active" unchecked-value="0" :checked="(bool) ($course->is_active ?? true)"
            label="Tampilkan di katalog" description="Kursus nonaktif tidak bisa dipilih untuk kelas baru dan tidak tampil di situs publik." />
    </div>
</section>
