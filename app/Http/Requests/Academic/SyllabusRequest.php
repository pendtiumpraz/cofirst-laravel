<?php

namespace App\Http\Requests\Academic;

use App\Models\Syllabus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create or update a syllabus = one meeting of a curriculum (admin).
 *
 * The meeting number is unique per curriculum (archived meetings excluded).
 * Learning objectives and activities are textareas with one item per line.
 */
class SyllabusRequest extends FormRequest
{
    /** Only admins manage meetings. */
    public function authorize(): bool
    {
        $syllabus = $this->route('syllabus');

        return $syllabus instanceof Syllabus
            ? $this->user()->can('update', $syllabus)
            : $this->user()->can('create', Syllabus::class);
    }

    /**
     * Validation rules.
     *
     * Returns:
     *     array<string, mixed>
     */
    public function rules(): array
    {
        $syllabus = $this->route('syllabus');

        return [
            'curriculum_id' => ['required', 'integer', Rule::exists('curriculums', 'id')->whereNull('deleted_at')],
            'meeting_number' => [
                'required', 'integer', 'min:1', 'max:200',
                Rule::unique('syllabuses', 'meeting_number')
                    ->where('curriculum_id', (int) $this->input('curriculum_id'))
                    ->whereNull('deleted_at')
                    ->ignore($syllabus instanceof Syllabus ? $syllabus->id : null),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'learning_objectives' => ['nullable', 'string', 'max:5000'],
            'activities' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /** Indonesian messages for the custom cases. */
    public function messages(): array
    {
        return [
            'meeting_number.unique' => 'Pertemuan ke-:input sudah ada di kurikulum ini.',
        ];
    }

    /** Indonesian field names. */
    public function attributes(): array
    {
        return [
            'curriculum_id' => 'kurikulum',
            'meeting_number' => 'nomor pertemuan',
            'title' => 'judul pertemuan',
            'description' => 'deskripsi',
            'learning_objectives' => 'tujuan pembelajaran',
            'activities' => 'kegiatan',
            'duration_minutes' => 'durasi (menit)',
            'status' => 'status',
        ];
    }
}
