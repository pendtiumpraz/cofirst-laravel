{{-- Role form. Expects $role, $groups (AccessControl::group()), $granted (names). --}}
@php
    use App\Support\Admin\AccessControl;

    $system = $role->exists && AccessControl::isSystemRole($role->name);
    $isSuper = $role->name === 'superadmin';
    $checked = array_values((array) old('permissions', $granted));
@endphp

<section class="form-section" aria-labelledby="sec-nama-peran">
    <div class="form-section-intro">
        <h2 id="sec-nama-peran" class="card-title">Nama peran</h2>
        <p class="mt-1 text-sm text-fg-muted">Nama teknis dipakai di sistem: huruf kecil tanpa spasi, mis. <span class="font-mono">koordinator_kelas</span>.</p>
    </div>
    <div class="form-section-body">
        <x-ui.field label="Nama teknis" for="name" required :hint="$system ? 'Nama peran bawaan tidak dapat diubah.' : null">
            <x-ui.input name="name" :value="$role->name" class="font-mono" required maxlength="40" :readonly="$system" placeholder="koordinator_kelas" />
        </x-ui.field>
    </div>
</section>

<section class="form-section" aria-labelledby="sec-izin" x-data="{ checked: @js($checked) }">
    <div class="form-section-intro">
        <h2 id="sec-izin" class="card-title">Izin</h2>
        <p class="mt-1 text-sm text-fg-muted">Centang tindakan yang boleh dilakukan peran ini. Akses ke menu admin tetap hanya untuk Admin dan Superadmin.</p>
        <p class="mt-3 text-sm text-fg"><span class="num font-semibold" x-text="checked.length"></span> izin dipilih</p>
    </div>
    <div class="form-section-body">
        @if ($isSuper)
            <x-ui.alert tone="info" icon="shield-check">Superadmin selalu memiliki semua izin agar tidak ada yang terkunci dari sistem.</x-ui.alert>
        @else
            @foreach ($groups as $key => $group)
                @php $names = collect($group['permissions'])->pluck('name')->values(); @endphp
                <fieldset class="rounded-lg border border-line p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <legend class="text-sm font-semibold text-fg">{{ $group['label'] }}</legend>
                        <button type="button" class="btn btn-ghost btn-xs"
                            x-on:click="const all = @js($names); const on = all.every(n => checked.includes(n)); checked = on ? checked.filter(n => ! all.includes(n)) : [...new Set([...checked, ...all])]">
                            Pilih/hapus semua
                        </button>
                    </div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ($group['permissions'] as $permission)
                            <x-ui.checkbox name="permissions[]" :value="$permission['name']" :checked="in_array($permission['name'], $checked, true)"
                                :label="$permission['label']" :description="$permission['label'] !== $permission['name'] ? $permission['name'] : null" x-model="checked" />
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
            @error('permissions.*')<p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $message }}</span></p>@enderror
        @endif
    </div>
</section>
