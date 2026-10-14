{{-- Permission form. Expects $permission, $roleOptions (name => label), $granted, $isSystem (edit). --}}
@php
    $system = $isSystem ?? false;
    $checked = array_values((array) old('roles', $granted));
@endphp

<section class="form-section" aria-labelledby="sec-izin-nama">
    <div class="form-section-intro">
        <h2 id="sec-izin-nama" class="card-title">Nama izin</h2>
        <p class="mt-1 text-sm text-fg-muted">Gunakan pola kata kerja + objek dalam bahasa Inggris, mis. <span class="font-mono">export enrollments</span>, agar konsisten dengan izin bawaan.</p>
    </div>
    <div class="form-section-body">
        <x-ui.field label="Nama izin" for="name" required :hint="$system ? 'Izin bawaan dipakai aplikasi; namanya tidak dapat diubah.' : null">
            <x-ui.input name="name" :value="$permission->name" class="font-mono" required maxlength="60" :readonly="$system" />
        </x-ui.field>
    </div>
</section>

<section class="form-section" aria-labelledby="sec-izin-peran">
    <div class="form-section-intro">
        <h2 id="sec-izin-peran" class="card-title">Diberikan ke peran</h2>
        <p class="mt-1 text-sm text-fg-muted">Superadmin selalu mendapat semua izin.</p>
    </div>
    <div class="form-section-body">
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($roleOptions as $value => $label)
                <x-ui.checkbox name="roles[]" :value="$value" :checked="in_array($value, $checked, true)" :label="$label" />
            @endforeach
        </div>
        @error('roles.*')<p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>@enderror
    </div>
</section>
