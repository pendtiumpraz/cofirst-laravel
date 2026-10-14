<?php

namespace App\Http\Requests\Admin;

use App\Models\ClassName;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation for creating and editing a class (Admin › Kelas).
 *
 * The curriculum is optional but, when chosen, must belong to the selected
 * course. Capacity follows the class type (private = 1 seat, kids' group ≤ 5)
 * and can never drop below the seats already taken.
 */
class ClassRequest extends AdminRequest
{
    /** Indonesian labels of the class statuses. */
    public const STATUSES = [
        ClassName::STATUS_PLANNED => 'Direncanakan',
        ClassName::STATUS_ACTIVE => 'Berlangsung',
        ClassName::STATUS_COMPLETED => 'Selesai',
        ClassName::STATUS_CANCELLED => 'Dibatalkan',
    ];

    /** Delivery methods. */
    public const DELIVERY = [
        'offline' => 'Tatap muka',
        'online' => 'Online',
    ];

    /**
     * Maximum seats per class type (null = only the global maximum).
     *
     * @var array<string, int|null>
     */
    public const TYPE_CAPACITY = [
        'private_home_call' => 1,
        'private_office_1on1' => 1,
        'private_online_1on1' => 1,
        'free_trial_30min' => 1,
        'group_class_3_5_kids' => 5,
        'public_school_extracurricular' => null,
        'offline_seminar' => null,
        'online_webinar' => null,
        'free_webinar' => null,
    ];

    /** Suggested capacity per type for the form. */
    public const TYPE_SUGGESTED = [
        'private_home_call' => 1,
        'private_office_1on1' => 1,
        'private_online_1on1' => 1,
        'free_trial_30min' => 1,
        'group_class_3_5_kids' => 5,
        'public_school_extracurricular' => 20,
        'offline_seminar' => 30,
        'online_webinar' => 100,
        'free_webinar' => 100,
    ];

    /** Create needs create permission; update needs edit permission. */
    public function authorize(): bool
    {
        $class = $this->route('class');

        return $class instanceof ClassName
            ? $this->user()->can('update', $class)
            : $this->user()->can('create', ClassName::class);
    }

    /**
     * Normalise booleans.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['remove_photo' => $this->boolean('remove_photo')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = (string) $this->input('type');
        $typeMax = self::TYPE_CAPACITY[$type] ?? null;

        return [
            'name' => ['required', 'string', 'max:150'],
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')->whereNull('deleted_at')],
            'curriculum_id' => ['nullable', 'integer', Rule::exists('curriculums', 'id')->where('course_id', (int) $this->input('course_id'))],
            'type' => ['required', Rule::in(array_keys(ClassName::types()))],
            'delivery_method' => ['nullable', Rule::in(array_keys(self::DELIVERY))],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'max_students' => ['required', 'integer', 'min:1', 'max:'.($typeMax ?? 500)],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'teacher_ids' => ['required', 'array', 'min:1', 'max:5'],
            'teacher_ids.*' => ['integer', 'distinct', function (string $attribute, mixed $value, \Closure $fail) {
                if (! User::whereKey((int) $value)->where('is_active', true)->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))->exists()) {
                    $fail('Pengajar yang dipilih harus akun guru yang aktif.');
                }
            }],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
            'remove_photo' => ['boolean'],
        ];
    }

    /**
     * Capacity may not fall below the seats already taken.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $class = $this->route('class');
                if (! $class instanceof ClassName || $validator->errors()->has('max_students')) {
                    return;
                }

                $taken = $class->seatsTaken();
                if ((int) $this->input('max_students') < $taken) {
                    $validator->errors()->add('max_students', "Kapasitas tidak boleh lebih kecil dari jumlah siswa terdaftar ({$taken}).");
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama kelas',
            'course_id' => 'kursus',
            'curriculum_id' => 'kurikulum',
            'type' => 'tipe kelas',
            'delivery_method' => 'metode belajar',
            'description' => 'deskripsi',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal selesai',
            'max_students' => 'kapasitas',
            'status' => 'status',
            'teacher_ids' => 'pengajar',
            'teacher_ids.*' => 'pengajar',
            'photo' => 'foto kelas',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'teacher_ids.required' => 'Pilih minimal satu pengajar.',
            'curriculum_id.exists' => 'Kurikulum yang dipilih bukan milik kursus ini.',
            'max_students.max' => 'Kapasitas maksimal untuk tipe kelas ini adalah :max siswa.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }
}
