<?php

namespace App\Http\Requests\Admin;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Validation for adding a student from the class roster.
 */
class AddClassStudentRequest extends AdminRequest
{
    /** Roster changes need the class edit permission. */
    public function authorize(): bool
    {
        $class = $this->route('class');

        return $class instanceof ClassName && $this->user()->can('manageRoster', $class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', function (string $attribute, mixed $value, \Closure $fail) {
                if (! User::whereKey((int) $value)->whereHas('roles', fn ($q) => $q->where('name', 'student'))->exists()) {
                    $fail('Pilih akun siswa yang valid.');
                }
            }],
            'status' => ['nullable', Rule::in(Enrollment::SEAT_STATUSES)],
            'payment_status' => ['nullable', Rule::in(array_keys(EnrollmentRequest::PAYMENT_STATUSES))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'siswa',
            'status' => 'status',
            'payment_status' => 'status pembayaran',
        ];
    }
}
