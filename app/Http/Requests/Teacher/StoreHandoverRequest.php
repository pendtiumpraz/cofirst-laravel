<?php

namespace App\Http\Requests\Teacher;

use App\Models\TeacherHandover;
use App\Models\User;
use App\Services\Teacher\TeachingSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Create or revise a handover request (serah terima kelas).
 *
 * The class must be one the teacher currently teaches; the receiving teacher
 * must be another active teacher who does not already teach that class; only
 * one pending request per class and teacher at a time.
 */
class StoreHandoverRequest extends FormRequest
{
    /**
     * Teachers only (route middleware); revisions are checked in the controller.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('teacher');
    }

    /**
     * Drop the end date of permanent handovers before validating.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('type') === TeacherHandover::TYPE_PERMANENT) {
            $this->merge(['effective_until' => null]);
        }
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $teacher = $this->user();
        $classIds = $teacher->taughtClasses()->pluck('class_names.id')->map(fn ($id) => (int) $id)->all();

        return [
            'class_id' => ['required', 'integer', Rule::in($classIds)],
            'to_teacher_id' => [
                'required', 'integer', Rule::notIn([$teacher->id]),
                Rule::exists('users', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::in(array_keys(TeacherHandover::types()))],
            'effective_from' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.TeachingSchedule::today()->toDateString()],
            'effective_until' => [
                Rule::requiredIf($this->input('type') === TeacherHandover::TYPE_TEMPORARY),
                'nullable', 'date_format:Y-m-d', 'after_or_equal:effective_from',
            ],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'handover_notes' => ['nullable', 'string', 'max:3000'],
            'last_meeting_number' => ['nullable', 'integer', 'min:0', 'max:500'],
        ];
    }

    /**
     * Cross-field checks: receiving teacher role and class, duplicate requests.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $target = User::find((int) $this->input('to_teacher_id'));

                if (! $target || ! $target->hasRole('teacher')) {
                    $validator->errors()->add('to_teacher_id', 'Pilih pengajar yang terdaftar.');

                    return;
                }

                if ($target->teachesClass((int) $this->input('class_id'))) {
                    $validator->errors()->add('to_teacher_id', $target->name.' sudah mengajar kelas ini.');

                    return;
                }

                $current = $this->route('handover');
                $duplicate = TeacherHandover::query()
                    ->pending()
                    ->where('class_id', (int) $this->input('class_id'))
                    ->where('from_teacher_id', $this->user()->id)
                    ->when($current instanceof TeacherHandover, fn ($q) => $q->whereKeyNot($current->id))
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add('class_id', 'Masih ada permintaan serah terima untuk kelas ini yang menunggu jawaban.');
                }
            },
        ];
    }

    /**
     * Indonesian attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'kelas',
            'to_teacher_id' => 'pengajar pengganti',
            'type' => 'jenis serah terima',
            'effective_from' => 'tanggal mulai',
            'effective_until' => 'tanggal selesai',
            'reason' => 'alasan',
            'handover_notes' => 'catatan serah terima',
            'last_meeting_number' => 'pertemuan terakhir',
        ];
    }

    /**
     * Custom messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_id.in' => 'Pilih salah satu kelas yang sedang Anda ajar.',
            'to_teacher_id.not_in' => 'Pilih pengajar lain, bukan diri sendiri.',
            'to_teacher_id.exists' => 'Pengajar yang dipilih tidak aktif.',
            'effective_from.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'effective_until.required' => 'Isi tanggal selesai untuk serah terima sementara.',
            'effective_until.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }
}
