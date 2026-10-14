<?php

namespace App\Http\Requests\Admin;

use App\Models\ClassName;
use App\Models\Enrollment;
use Illuminate\Validation\Rule;

/**
 * Validation for creating and editing an enrollment (Admin › Pendaftaran).
 *
 * The student is fixed once the enrollment exists (moving a student is done
 * by changing the class); capacity is checked by ClassRosterService.
 */
class EnrollmentRequest extends AdminRequest
{
    /** Payment status labels. */
    public const PAYMENT_STATUSES = [
        'unpaid' => 'Belum bayar',
        'pending' => 'Menunggu verifikasi',
        'paid' => 'Lunas',
        'refunded' => 'Dikembalikan',
    ];

    /** Create needs create permission; update needs edit permission. */
    public function authorize(): bool
    {
        $enrollment = $this->route('enrollment');

        return $enrollment instanceof Enrollment
            ? $this->user()->can('update', $enrollment)
            : $this->user()->can('create', Enrollment::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $enrollment = $this->route('enrollment');
        $creating = ! $enrollment instanceof Enrollment;
        $currentClassId = $creating ? null : (int) $enrollment->class_id;

        $rules = [
            // Reason: keeping the current class is always allowed (e.g. marking an
            // enrollment of a finished class as completed); a new class must be open.
            'class_id' => ['required', 'integer', function (string $attribute, mixed $value, \Closure $fail) use ($currentClassId) {
                if ($currentClassId !== null && (int) $value === $currentClassId) {
                    return;
                }
                $open = ClassName::whereKey((int) $value)
                    ->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])
                    ->exists();
                if (! $open) {
                    $fail('Pilih kelas yang masih direncanakan atau berlangsung.');
                }
            }],
            'enrollment_date' => ['required', 'date', 'before_or_equal:'.now()->addYear()->toDateString()],
            'status' => ['required', Rule::in(array_keys(Enrollment::statuses()))],
            'payment_status' => ['required', Rule::in(array_keys(self::PAYMENT_STATUSES))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($creating) {
            $rules['student_id'] = ['required', 'integer', function (string $attribute, mixed $value, \Closure $fail) {
                $isStudent = \App\Models\User::whereKey((int) $value)->whereHas('roles', fn ($q) => $q->where('name', 'student'))->exists();
                if (! $isStudent) {
                    $fail('Pilih akun siswa yang valid.');
                }
            }];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'siswa',
            'class_id' => 'kelas',
            'enrollment_date' => 'tanggal daftar',
            'status' => 'status',
            'payment_status' => 'status pembayaran',
            'notes' => 'catatan',
        ];
    }

}
