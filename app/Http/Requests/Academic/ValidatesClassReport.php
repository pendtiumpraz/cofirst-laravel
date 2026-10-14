<?php

namespace App\Http\Requests\Academic;

use App\Models\ClassName;
use App\Models\Material;
use App\Models\Syllabus;
use App\Services\Academic\AcademicSettings;
use App\Services\Academic\ClassReportService;
use App\Services\Academic\Format;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

/**
 * Shared rules for creating and editing meeting reports: the meeting must belong to
 * the class's curriculum, the material to that meeting, the date cannot be in the
 * future, and documentation links must point to an approved host.
 */
trait ValidatesClassReport
{
    /**
     * Rules of the fields shared by every student of a meeting.
     *
     * Returns:
     *     array<string, mixed>
     */
    protected function sessionRules(): array
    {
        return [
            'report_date' => ['required', 'date', 'before_or_equal:'.Carbon::today(Format::timezone())->toDateString()],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'meeting_number' => ['required', 'integer', 'min:1', 'max:200'],
            'syllabus_id' => ['nullable', 'integer'],
            'material_id' => ['nullable', 'integer'],
            'learning_concept' => ['required', 'string', 'max:2000'],
            'learning_media_link' => ['nullable', 'url:https', 'max:500'],
        ];
    }

    /**
     * Rules of the per-student fields under a prefix ('' or 'students.*.').
     *
     * Returns:
     *     array<string, array<int, string>>
     */
    protected function studentFieldRules(string $prefix = ''): array
    {
        return collect(ClassReportService::STUDENT_FIELDS)
            ->mapWithKeys(fn ($field) => [$prefix.$field => ['nullable', 'string', 'max:2000']])
            ->all();
    }

    /** Fill syllabus_id from the meeting number when the teacher left it empty. */
    protected function defaultSyllabus(?ClassName $class): void
    {
        if (! $class?->curriculum_id || $this->filled('syllabus_id') || ! is_numeric($this->input('meeting_number'))) {
            return;
        }

        $id = Syllabus::where('curriculum_id', $class->curriculum_id)
            ->where('meeting_number', (int) $this->input('meeting_number'))
            ->value('id');

        if ($id) {
            $this->merge(['syllabus_id' => $id]);
        }
    }

    /** Relationship checks between class, meeting, material, date and link. */
    protected function validateSession(Validator $validator, ClassName $class): void
    {
        $syllabus = $this->filled('syllabus_id') ? Syllabus::find((int) $this->input('syllabus_id')) : null;

        if ($this->filled('syllabus_id')) {
            if (! $syllabus || (int) $syllabus->curriculum_id !== (int) $class->curriculum_id) {
                $validator->errors()->add('syllabus_id', 'Pertemuan tidak termasuk kurikulum kelas ini.');
            } elseif ((int) $syllabus->meeting_number !== (int) $this->input('meeting_number')) {
                $validator->errors()->add('meeting_number', "Nomor pertemuan harus {$syllabus->meeting_number} sesuai silabus yang dipilih.");
            }
        }

        if ($this->filled('material_id')) {
            $material = Material::find((int) $this->input('material_id'));

            if (! $material || ! $syllabus || (int) $material->syllabus_id !== (int) $syllabus->id) {
                $validator->errors()->add('material_id', 'Materi tidak termasuk pertemuan yang dipilih.');
            }
        }

        if ($class->start_date && $this->filled('report_date') && ! $validator->errors()->has('report_date')
            && Carbon::parse($this->input('report_date'))->lt($class->start_date->copy()->startOfDay())) {
            $validator->errors()->add('report_date', 'Tanggal sebelum kelas dimulai ('.Format::date($class->start_date).').');
        }

        $link = (string) $this->input('learning_media_link');

        if ($link !== '' && ! $validator->errors()->has('learning_media_link')) {
            $host = strtolower((string) parse_url($link, PHP_URL_HOST));

            if (! in_array($host, AcademicSettings::mediaLinkHosts(), true)) {
                $validator->errors()->add('learning_media_link', 'Gunakan tautan dari penyimpanan lembaga: '.implode(', ', AcademicSettings::mediaLinkHosts()).'.');
            }
        }
    }

    /** Indonesian field names. */
    public function attributes(): array
    {
        $names = [
            'class_id' => 'kelas',
            'schedule_id' => 'jadwal',
            'report_date' => 'tanggal pertemuan',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'meeting_number' => 'pertemuan ke',
            'syllabus_id' => 'silabus',
            'material_id' => 'materi',
            'learning_concept' => 'konsep yang dipelajari',
            'learning_media_link' => 'tautan dokumentasi',
            'students' => 'siswa',
            'notes_recommendations' => 'catatan & rekomendasi',
            'follow_up_notes' => 'tindak lanjut',
            'students.*.notes_recommendations' => 'catatan & rekomendasi',
            'students.*.follow_up_notes' => 'tindak lanjut',
        ];

        foreach (ClassReportService::bloomFields() as $field => [$label]) {
            $names[$field] = strtolower($label);
            $names['students.*.'.$field] = strtolower($label);
        }

        return $names;
    }
}
