<?php

namespace App\Http\Requests\Academic;

use App\Models\Curriculum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create or update a curriculum (admin).
 *
 * `objectives` is a textarea with one objective per line (stored as text, read as a
 * list through Curriculum::objectives_list).
 */
class CurriculumRequest extends FormRequest
{
    /** Only admins manage curricula. */
    public function authorize(): bool
    {
        $curriculum = $this->route('curriculum');

        return $curriculum instanceof Curriculum
            ? $this->user()->can('update', $curriculum)
            : $this->user()->can('create', Curriculum::class);
    }

    /**
     * Validation rules.
     *
     * Returns:
     *     array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Curriculum::getTypes()))],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'duration_weeks' => ['nullable', 'integer', 'min:1', 'max:104'],
            'description' => ['nullable', 'string', 'max:5000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** Indonesian field names for the messages. */
    public function attributes(): array
    {
        return [
            'course_id' => 'kursus',
            'title' => 'judul kurikulum',
            'type' => 'jenis',
            'status' => 'status',
            'duration_weeks' => 'durasi (minggu)',
            'description' => 'deskripsi',
            'objectives' => 'tujuan pembelajaran',
        ];
    }
}
