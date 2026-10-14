<?php

namespace App\Http\Requests\Academic;

use App\Services\Academic\ProgressService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A teacher sets the status of one meeting for the whole class
 * (e.g. "sedang berjalan" before reports are written).
 */
class MeetingProgressRequest extends FormRequest
{
    /** Teachers only; class ownership is checked in the controller. */
    public function authorize(): bool
    {
        return $this->user()->hasRole('teacher');
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
            'meeting_number' => ['required', 'integer', 'min:1', 'max:200'],
            'status' => ['required', Rule::in(array_keys(ProgressService::statuses()))],
        ];
    }

    /** Indonesian field names. */
    public function attributes(): array
    {
        return ['meeting_number' => 'pertemuan', 'status' => 'status'];
    }
}
