<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Profil › Informasi pribadi (users.name/email + profiles.*).
 *
 * UU PDP: the data subject may correct their data (right to rectification). Two
 * fields are protected for students: the login email and the birth date of a minor
 * are managed by the parent/guardian or an admin, because they drive parental
 * consent and account recovery.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Signed-in users update their own profile only.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Normalise the email before validation (trimmed, lower case).
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9][0-9\s\-().]{6,19}$/'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'address' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];

        if (self::canChangeEmail($user)) {
            $rules['email'] = [
                'required', 'string', 'email', 'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ];

            // Reason: changing the login email without re-authentication would let a
            // hijacked session take over the account through "lupa kata sandi".
            if ($this->emailChanged()) {
                $rules['current_password'] = ['required', 'string', 'current_password'];
            }
        }

        if (self::canChangeBirthDate($user)) {
            $rules['birth_date'] = ['nullable', 'date', 'before:today', 'after:1900-01-01'];
        }

        return $rules;
    }

    /**
     * Indonesian attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'email',
            'current_password' => 'kata sandi saat ini',
            'phone_number' => 'nomor telepon',
            'birth_date' => 'tanggal lahir',
            'gender' => 'jenis kelamin',
            'address' => 'alamat',
            'bio' => 'bio',
        ];
    }

    /**
     * Custom messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'current_password.required' => 'Masukkan kata sandi saat ini untuk mengganti email.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'phone_number.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda +, -, atau tanda kurung.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
        ];
    }

    /**
     * Whether the submitted email differs from the current one.
     */
    public function emailChanged(): bool
    {
        $email = $this->input('email');

        return is_string($email) && mb_strtolower(trim($email)) !== mb_strtolower((string) $this->user()->email);
    }

    /**
     * Whether the user may change their own login email.
     *
     * Students who are minors (or whose age is unknown) cannot: their parent/guardian
     * or an admin manages it.
     */
    public static function canChangeEmail(User $user): bool
    {
        return ! self::isProtectedStudent($user);
    }

    /**
     * Whether the user may change their own birth date. Students cannot, because the
     * birth date decides whether parental consent is required (UU PDP Pasal 25).
     */
    public static function canChangeBirthDate(User $user): bool
    {
        return ! $user->hasRole('student');
    }

    /**
     * A student who is a child by the configured age threshold, or whose age is unknown.
     */
    private static function isProtectedStudent(User $user): bool
    {
        if (! $user->hasRole('student')) {
            return false;
        }

        $age = $user->profile?->age;
        $threshold = function_exists('setting') ? (int) setting('privacy.child_age_threshold', 18) : 18;

        return $age === null || $age < $threshold;
    }
}
