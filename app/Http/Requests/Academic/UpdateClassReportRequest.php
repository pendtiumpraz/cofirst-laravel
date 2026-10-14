<?php

namespace App\Http\Requests\Academic;

use App\Models\ClassReport;
use App\Services\Academic\AcademicPrivacy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Edit one meeting report. Teacher, class, student and schedule are immutable;
 * only the meeting and narrative fields can change.
 */
class UpdateClassReportRequest extends FormRequest
{
    use ValidatesClassReport;

    /** Policy: owner within the edit window, or admin when corrections are enabled. */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->report());
    }

    /** Default the syllabus from the meeting number. */
    protected function prepareForValidation(): void
    {
        $this->defaultSyllabus($this->report()->className);
    }

    /**
     * Validation rules.
     *
     * Returns:
     *     array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->sessionRules(), $this->studentFieldRules());
    }

    /** Relationship, duplicate and consent checks. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $report = $this->report();
            $class = $report->className;

            if (! $class) {
                $validator->errors()->add('meeting_number', 'Kelas laporan ini sudah tidak tersedia.');

                return;
            }

            $this->validateSession($validator, $class);

            if ($this->filled('report_date') && ! $validator->errors()->has('report_date')
                && ClassReport::where('class_id', $report->class_id)
                    ->where('student_id', $report->student_id)
                    ->whereDate('report_date', $this->input('report_date'))
                    ->whereKeyNot($report->id)
                    ->exists()) {
                $validator->errors()->add('report_date', 'Sudah ada berita acara siswa ini pada tanggal tersebut.');
            }

            if ($this->filled('learning_media_link') && $report->student && AcademicPrivacy::mediaConsent($report->student) === false) {
                $validator->errors()->add('learning_media_link', 'Orang tua/wali belum mengizinkan dokumentasi foto/video siswa ini.');
            }
        });
    }

    /** The report being edited (route parameter class_report). */
    public function report(): ClassReport
    {
        return $this->route('class_report');
    }
}
