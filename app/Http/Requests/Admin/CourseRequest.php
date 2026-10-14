<?php

namespace App\Http\Requests\Admin;

use App\Models\Course;
use Illuminate\Validation\Rule;

/**
 * Validation for creating and editing a course (Admin › Kursus).
 */
class CourseRequest extends AdminRequest
{
    /** Course levels with Indonesian labels. */
    public const LEVELS = [
        'beginner' => 'Pemula',
        'intermediate' => 'Menengah',
        'advanced' => 'Lanjutan',
    ];

    /** Create needs create permission; update needs edit permission. */
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course
            ? $this->user()->can('update', $course)
            : $this->user()->can('create', Course::class);
    }

    /**
     * Normalise money, slug and booleans before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->normaliseMoney('price');
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_thumbnail' => $this->boolean('remove_thumbnail'),
            'slug' => is_string($this->input('slug')) && trim($this->input('slug')) !== ''
                ? \Illuminate\Support\Str::slug($this->input('slug'))
                : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $course = $this->route('course');

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash', Rule::unique('courses', 'slug')->ignore($course instanceof Course ? $course->id : null)],
            'description' => ['required', 'string', 'max:5000'],
            'level' => ['required', Rule::in(array_keys(self::LEVELS))],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'duration_weeks' => ['required', 'integer', 'min:1', 'max:104'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'benefits' => ['nullable', 'string', 'max:3000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
            'remove_thumbnail' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama kursus',
            'slug' => 'slug URL',
            'description' => 'deskripsi',
            'level' => 'tingkat',
            'price' => 'harga',
            'duration_weeks' => 'durasi (minggu)',
            'duration_hours' => 'total jam belajar',
            'benefits' => 'manfaat',
            'thumbnail' => 'gambar sampul',
            'is_active' => 'status',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.unique' => 'Slug ini sudah dipakai kursus lain (termasuk yang diarsipkan).',
            'slug.alpha_dash' => 'Slug hanya boleh berisi huruf, angka dan tanda hubung.',
            'price.integer' => 'Harga harus berupa angka rupiah tanpa desimal.',
        ];
    }
}
