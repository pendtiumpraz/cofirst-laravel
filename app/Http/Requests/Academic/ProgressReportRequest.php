<?php

namespace App\Http\Requests\Academic;

use App\Models\ClassName;
use App\Models\Report;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\Format;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * A teacher's periodic progress report (laporan perkembangan) about one student.
 *
 * The teacher must teach the class and the student must be enrolled in it (active or
 * completed, so a final report can follow the last meeting).
 * On update the class and student cannot change (a report is never moved to another
 * child). teacher_id is set by the controller.
 */
class ProgressReportRequest extends FormRequest
{
    /** Teachers only; the author check for updates is in the controller. */
    public function authorize(): bool
    {
        return $this->user()->hasRole('teacher');
    }

    /** Accept the legacy field name report_type. */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('type') && $this->filled('report_type')) {
            $this->merge(['type' => $this->input('report_type')]);
        }
    }

    /**
     * Validation rules.
     *
     * Returns:
     *     array<string, mixed>
     */
    public function rules(): array
    {
        $creating = ! $this->route('report') instanceof Report;

        return [
            'class_id' => [$creating ? 'required' : 'prohibited', 'integer'],
            'student_id' => [$creating ? 'required' : 'prohibited', 'integer'],
            'type' => ['required', Rule::in(array_keys(Report::types()))],
            'title' => ['required', 'string', 'max:255'],
            'report_date' => ['required', 'date', 'before_or_equal:'.Carbon::today(Format::timezone())->toDateString()],
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'content' => ['required', 'string', 'max:10000'],
            'recommendations' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** The teacher teaches the class and the student is actively enrolled. */
    public function withValidator(Validator $validator): void
    {
        if ($this->route('report') instanceof Report) {
            return;
        }

        $validator->after(function (Validator $validator) {
            if ($validator->errors()->hasAny(['class_id', 'student_id'])) {
                return;
            }

            $class = ClassName::find((int) $this->input('class_id'));

            if (! $class || ! app(AcademicScope::class)->teacherTeaches($this->user(), $class)) {
                $validator->errors()->add('class_id', 'Anda tidak mengajar kelas ini.');

                return;
            }

            if (! $class->enrollments()->whereIn('status', AcademicScope::ACCESS_ENROLLMENT_STATUSES)->where('student_id', (int) $this->input('student_id'))->exists()) {
                $validator->errors()->add('student_id', 'Siswa ini tidak terdaftar (aktif atau lulus) di kelas tersebut.');
            }
        });
    }

    /** Indonesian field names. */
    public function attributes(): array
    {
        return [
            'class_id' => 'kelas',
            'student_id' => 'siswa',
            'type' => 'jenis laporan',
            'title' => 'judul',
            'report_date' => 'tanggal laporan',
            'progress_percentage' => 'progres',
            'score' => 'nilai',
            'content' => 'isi laporan',
            'recommendations' => 'rekomendasi',
        ];
    }
}
