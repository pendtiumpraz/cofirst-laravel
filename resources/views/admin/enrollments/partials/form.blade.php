{{--
    Shared enrollment form. Expects $enrollment, $classOptions, $statuses, $paymentStatuses;
    on create also $student (preselected User|null).
--}}
@php
    $editing = $enrollment->exists;
    $preselected = [];
    if (! $editing) {
        $oldStudent = old('student_id') ? App\Models\User::find((int) old('student_id'), ['id', 'name']) : null;
        $pre = $oldStudent ?? ($student ?? null);
        $preselected = $pre ? [['id' => $pre->id, 'name' => $pre->name, 'hint' => '']] : [];
    }
@endphp

<section class="form-section" aria-labelledby="sec-siswa">
    <div class="form-section-intro">
        <h2 id="sec-siswa" class="card-title">Siswa & kelas</h2>
        <p class="mt-1 text-sm text-fg-muted">Hanya kelas yang direncanakan atau berlangsung yang dapat dipilih. Kelas penuh tidak dapat dipilih.</p>
    </div>
    <div class="form-section-body">
        @if ($editing)
            <div>
                <p class="label">Siswa</p>
                <div class="mt-1 flex items-center gap-3 rounded-md border border-line bg-surface-2 px-3 py-2.5">
                    <x-ui.avatar :user="$enrollment->student" size="sm" decorative />
                    <span class="font-medium text-fg">{{ $enrollment->student?->name ?? 'Siswa' }}</span>
                </div>
                <p class="hint mt-1.5">Siswa tidak dapat diganti. Buat pendaftaran baru untuk siswa lain.</p>
            </div>
        @else
            <x-ui.field label="Siswa" for="student-picker" :error="'student_id'" required>
                @include('admin.users.partials.user-picker', ['name' => 'student_id', 'id' => 'student-picker', 'role' => 'student',
                    'multiple' => false, 'selected' => $preselected, 'placeholder' => 'Cari nama siswa…'])
            </x-ui.field>
        @endif

        <x-ui.field label="Kelas" for="class_id" required :hint="$editing ? 'Mengganti kelas memindahkan siswa beserta jadwal mingguannya.' : null">
            <x-ui.select name="class_id" :options="$classOptions" :value="$enrollment->class_id" placeholder="Pilih kelas" required />
        </x-ui.field>
    </div>
</section>

<section class="form-section" aria-labelledby="sec-status">
    <div class="form-section-intro">
        <h2 id="sec-status" class="card-title">Status & pembayaran</h2>
        <p class="mt-1 text-sm text-fg-muted">Status Aktif dan Menunggu memakai kursi kelas. Pembayaran dicatat terpisah oleh tim keuangan.</p>
    </div>
    <div class="form-section-body">
        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.field label="Tanggal daftar" for="enrollment_date" required>
                <x-ui.input name="enrollment_date" type="date" :value="$enrollment->enrollment_date?->format('Y-m-d')" required />
            </x-ui.field>
            <x-ui.field label="Status" for="status" required>
                <x-ui.select name="status" :options="$statuses" :value="$enrollment->status" required />
            </x-ui.field>
            <x-ui.field label="Status pembayaran" for="payment_status" required>
                <x-ui.select name="payment_status" :options="$paymentStatuses" :value="$enrollment->payment_status" required />
            </x-ui.field>
        </div>
        <x-ui.field label="Catatan" for="notes" optional hint="Mis. alasan penangguhan atau kebutuhan khusus. Jangan menulis data kesehatan kecuali perlu.">
            <x-ui.textarea name="notes" rows="3" :value="$enrollment->notes" maxlength="1000" />
        </x-ui.field>
    </div>
</section>
