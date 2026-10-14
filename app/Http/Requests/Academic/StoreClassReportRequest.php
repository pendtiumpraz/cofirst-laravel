<?php

namespace App\Http\Requests\Academic;

use App\Models\ClassName;
use App\Models\ClassReport;
use App\Models\Schedule;
use App\Models\User;
use App\Services\Academic\AcademicPrivacy;
use App\Services\Academic\AcademicScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * A teacher reports one meeting of a class: shared fields plus one entry per
 * present student (each becomes its own ClassReport).
 *
 * Only students actively enrolled in the class, a schedule of that class and a
 * meeting/material of the class curriculum are accepted. teacher_id and
 * curriculum_id are never taken from the request.
 */
class StoreClassReportRequest extends FormRequest
{
    use ValidatesClassReport;

    private ?ClassName $classModel = null;

    /** Teachers only; class ownership is validated below. */
    public function authorize(): bool
    {
        return $this->user()->can('create', ClassReport::class);
    }

    /**
     * Keep only the ticked students and default the syllabus.
     *
     * Reason: keys are preserved (no re-indexing) so error messages and old() input
     * still match the student rows of the form after a failed submit.
     */
    protected function prepareForValidation(): void
    {
        $students = collect($this->input('students', []))
            ->filter(fn ($entry) => is_array($entry) && filter_var($entry['include'] ?? false, FILTER_VALIDATE_BOOLEAN))
            ->all();

        $this->merge(['students' => $students]);
        $this->defaultSyllabus($this->classModel());
    }

    /**
     * Validation rules.
     *
     * Returns:
     *     array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge([
            'class_id' => ['required', 'integer'],
            'schedule_id' => ['nullable', 'integer'],
            'students' => ['required', 'array', 'min:1', 'max:60'],
            'students.*.student_id' => ['required', 'integer', 'distinct'],
        ], $this->sessionRules(), $this->studentFieldRules('students.*.'));
    }

    /** Custom messages. */
    public function messages(): array
    {
        return [
            'students.required' => 'Pilih minimal satu siswa yang hadir.',
            'students.min' => 'Pilih minimal satu siswa yang hadir.',
        ];
    }

    /** Ownership and relationship checks. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $class = $this->classModel();

            if (! $class || ! app(AcademicScope::class)->teacherTeaches($this->user(), $class)) {
                $validator->errors()->add('class_id', 'Anda tidak mengajar kelas ini.');

                return;
            }

            if ($this->filled('schedule_id') && ! Schedule::whereKey((int) $this->input('schedule_id'))->where('class_id', $class->id)->exists()) {
                $validator->errors()->add('schedule_id', 'Jadwal tidak termasuk kelas ini.');
            }

            $this->validateSession($validator, $class);

            $studentIds = collect($this->input('students', []))->pluck('student_id')->map(fn ($id) => (int) $id);
            $enrolled = $class->activeEnrollments()->whereIn('student_id', $studentIds)->pluck('student_id')->map(fn ($id) => (int) $id);

            if ($studentIds->diff($enrolled)->isNotEmpty()) {
                $validator->errors()->add('students', 'Ada siswa yang tidak terdaftar aktif di kelas ini.');

                return;
            }

            if ($this->filled('report_date') && ! $validator->errors()->has('report_date')) {
                $duplicates = ClassReport::where('class_id', $class->id)
                    ->whereIn('student_id', $studentIds)
                    ->whereDate('report_date', $this->input('report_date'))
                    ->with('student:id,name')
                    ->get();

                if ($duplicates->isNotEmpty()) {
                    $validator->errors()->add('students', 'Berita acara tanggal ini sudah ada untuk: '.$duplicates->pluck('student.name')->join(', ').'.');
                }
            }

            if ($this->filled('learning_media_link') && ! $validator->errors()->has('learning_media_link')) {
                $denied = User::whereIn('id', $studentIds)->get(['id', 'name'])
                    ->filter(fn (User $student) => AcademicPrivacy::mediaConsent($student) === false);

                if ($denied->isNotEmpty()) {
                    $validator->errors()->add('learning_media_link', 'Orang tua/wali belum mengizinkan dokumentasi foto/video untuk: '.$denied->pluck('name')->join(', ').'.');
                }
            }
        });
    }

    /** The class being reported. */
    public function classModel(): ?ClassName
    {
        if (! $this->classModel && is_numeric($this->input('class_id'))) {
            $this->classModel = ClassName::find((int) $this->input('class_id'));
        }

        return $this->classModel;
    }
}
