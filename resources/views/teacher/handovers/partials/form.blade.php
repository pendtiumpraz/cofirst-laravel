{{--
    Handover request form (create + edit).
    @param \App\Models\TeacherHandover $handover   new or pending handover
    @param \Illuminate\Support\Collection $classes  the teacher's classes (course loaded)
    @param \Illuminate\Support\Collection $teachers other active teachers (id, name)
    @param array $classTeachers                    class id => teacher ids (to disable choices)
    @param array $types                            type => label
    @param string $action, $method, $submitLabel
--}}
@php
    use App\Models\TeacherHandover;
    use App\Services\Teacher\TeachingSchedule;

    $classId = (string) old('class_id', $handover->class_id);
    $type = (string) old('type', $handover->type ?: TeacherHandover::TYPE_TEMPORARY);
    $toTeacher = (string) old('to_teacher_id', $handover->to_teacher_id);
    $from = old('effective_from', $handover->effective_from?->format('Y-m-d') ?? TeachingSchedule::today()->toDateString());
    $until = old('effective_until', $handover->effective_until?->format('Y-m-d'));
    $toError = $errors->first('to_teacher_id');
@endphp

<form method="POST" action="{{ $action }}"
    x-data="{ classId: @js($classId), type: @js($type), taken: @js((object) $classTeachers) }"
    x-on:change="if ($event.target.name === 'type') type = $event.target.value">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <x-ui.card>
        <section class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Kelas & pengganti</h2>
                <p class="mt-1 text-sm text-fg-muted">Pilih kelas yang ingin diserahterimakan dan pengajar yang akan menerimanya.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Kelas" for="class_id" required>
                    <x-ui.select name="class_id" :value="$classId" placeholder="Pilih kelas" required x-model="classId"
                        :options="$classes->mapWithKeys(fn ($c) => [$c->id => $c->name.' — '.($c->course?->name ?? 'Kursus')])" />
                </x-ui.field>

                <x-ui.field label="Pengajar penerima" for="to_teacher_id" required
                    hint="Pengajar yang sudah mengajar kelas terpilih tidak dapat dipilih.">
                    <select id="to_teacher_id" name="to_teacher_id" class="input" required
                        aria-describedby="to_teacher_id-hint{{ $toError ? ' to_teacher_id-error' : '' }}" @if ($toError) aria-invalid="true" @endif>
                        <option value="">Pilih pengajar</option>
                        @foreach ($teachers as $person)
                            <option value="{{ $person->id }}" @selected($toTeacher === (string) $person->id)
                                x-bind:disabled="(taken[classId] || []).includes({{ (int) $person->id }})">{{ $person->name }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
            </div>
        </section>

        <section class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Jenis & periode</h2>
                <p class="mt-1 text-sm text-fg-muted">Kelas berpindah setelah pengajar penerima menyetujui permintaan.</p>
            </div>
            <div class="form-section-body">
                <x-ui.radio-cards name="type" legend="Jenis serah terima" :cols="2" required :value="$type" :options="[
                    TeacherHandover::TYPE_TEMPORARY => ['label' => $types[TeacherHandover::TYPE_TEMPORARY], 'description' => 'Guru pengganti untuk satu pertemuan atau beberapa minggu. Anda tetap tercatat sebagai pengajar.', 'icon' => 'clock'],
                    TeacherHandover::TYPE_PERMANENT => ['label' => $types[TeacherHandover::TYPE_PERMANENT], 'description' => 'Kelas dipindahkan sepenuhnya kepada pengajar penerima.', 'icon' => 'arrows-right-left'],
                ]" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Mulai berlaku" for="effective_from" required>
                        <x-ui.input name="effective_from" type="date" :value="$from" min="{{ TeachingSchedule::today()->toDateString() }}" required />
                    </x-ui.field>
                    <div x-show="type === '{{ TeacherHandover::TYPE_TEMPORARY }}'" @if ($type !== TeacherHandover::TYPE_TEMPORARY) x-cloak @endif>
                        <x-ui.field label="Sampai" for="effective_until" hint="Pengganti otomatis dilepas setelah tanggal ini." required>
                            <x-ui.input name="effective_until" type="date" :value="$until" min="{{ TeachingSchedule::today()->toDateString() }}"
                                x-bind:required="type === '{{ TeacherHandover::TYPE_TEMPORARY }}'" x-bind:disabled="type !== '{{ TeacherHandover::TYPE_TEMPORARY }}'" />
                        </x-ui.field>
                    </div>
                </div>
            </div>
        </section>

        <section class="form-section">
            <div class="form-section-intro">
                <h2 class="card-title">Catatan</h2>
                <p class="mt-1 text-sm text-fg-muted">Bantu pengajar penerima melanjutkan pelajaran tanpa jeda.</p>
            </div>
            <div class="form-section-body">
                <x-ui.field label="Alasan" for="reason" required hint="Minimal 10 karakter, mis. cuti, tugas luar kota, perubahan jadwal.">
                    <x-ui.textarea name="reason" rows="3" :value="$handover->reason" required maxlength="1000" />
                </x-ui.field>

                <x-ui.field label="Pertemuan terakhir yang diajarkan" for="last_meeting_number" optional>
                    <x-ui.input name="last_meeting_number" type="number" min="0" max="500" inputmode="numeric" class="sm:max-w-[10rem]"
                        :value="$handover->last_meeting_number" />
                </x-ui.field>

                <x-ui.field label="Catatan serah terima" for="handover_notes" optional
                    hint="Materi terakhir, tugas yang belum selesai, atau hal yang perlu diperhatikan. Hindari data pribadi yang tidak perlu.">
                    <x-ui.textarea name="handover_notes" rows="5" :value="$handover->handover_notes" maxlength="3000" />
                </x-ui.field>
            </div>
        </section>

        <x-slot:footer class="flex-col-reverse gap-2 sm:flex-row">
            <x-ui.button variant="ghost" :href="$cancelUrl">Batal</x-ui.button>
            <x-ui.button type="submit" icon="paper-airplane">{{ $submitLabel }}</x-ui.button>
        </x-slot:footer>
    </x-ui.card>
</form>
