<?php

namespace App\Http\Requests\Auth;

use App\Support\Auth\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/**
 * Public self-registration (siswa / orang tua only; staff accounts are created by admins).
 *
 * UU 27/2022: the privacy notice + terms acknowledgement is required (Pasal 21), optional
 * consents are separate and unticked (Pasal 22 ayat (4)), and a student younger than
 * setting('privacy.child_age_threshold') must name a parent/guardian (Pasal 25).
 */
class RegisterRequest extends FormRequest
{
    /** Roles that may ever self-register, whatever the settings say. */
    public const SELF_SERVICE_ROLES = ['student', 'parent'];

    /**
     * Anyone may submit; RegisteredUserController checks that registration is open.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise the e-mail addresses before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
            'guardian_email' => $this->filled('guardian_email') ? Str::lower(trim((string) $this->input('guardian_email'))) : null,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $password = class_exists(PasswordPolicy::class) ? PasswordPolicy::rule() : Password::defaults();

        return [
            'role' => ['required', 'string', Rule::in(self::allowedRoles())],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:191', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+ ()-]{8,20}$/'],
            'password' => ['required', 'string', 'confirmed', $password],
            'birth_date' => ['exclude_unless:role,student', 'required', 'date', 'before:today', 'after:1920-01-01'],
            'class_id' => ['exclude_unless:role,student', 'nullable', 'integer', Rule::exists('class_names', 'id')],
            'guardian_name' => ['nullable', 'string', 'min:2', 'max:120'],
            'guardian_email' => ['nullable', 'string', 'email:rfc', 'max:191', 'different:email'],
            'guardian_relationship' => ['nullable', Rule::in(['ibu', 'ayah', 'wali'])],
            'accept_policies' => ['accepted'],
            'consents' => ['nullable', 'array'],
            'consents.*' => ['string', 'max:64'],
            'undangan' => ['nullable', 'string', 'max:128'],
            // Honeypot: real people never see or fill this field.
            'website' => ['nullable', 'max:0'],
        ];
    }

    /**
     * A child (by birth date) must name a guardian when parental consent is required.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->input('role') !== 'student' || ! $this->needsGuardian()) {
                    return;
                }

                if (! $this->filled('guardian_name')) {
                    $validator->errors()->add('guardian_name', 'Nama orang tua/wali wajib diisi untuk pendaftar di bawah '.self::threshold().' tahun.');
                }

                if (! $this->filled('guardian_email')) {
                    $validator->errors()->add('guardian_email', 'Email orang tua/wali wajib diisi agar mereka dapat memberi persetujuan.');
                }
            },
        ];
    }

    /**
     * Whether the submitted birth date belongs to a child that needs parental consent.
     */
    public function needsGuardian(): bool
    {
        if ($this->input('role') !== 'student' || ! setting('privacy.require_parental_consent', true)) {
            return false;
        }

        try {
            $age = Carbon::parse((string) $this->input('birth_date'))->age;
        } catch (\Throwable) {
            return false;
        }

        return $age < self::threshold();
    }

    /**
     * Roles open for self-registration: setting('security.registration_roles') ∩ student/parent.
     *
     * Returns:
     *     array<int, string>
     */
    public static function allowedRoles(): array
    {
        return array_values(array_intersect(self::SELF_SERVICE_ROLES, (array) setting('security.registration_roles', self::SELF_SERVICE_ROLES)));
    }

    /** Child age threshold from settings. */
    public static function threshold(): int
    {
        return (int) setting('privacy.child_age_threshold', 18);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'role' => 'jenis akun',
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor WhatsApp',
            'password' => 'kata sandi',
            'birth_date' => 'tanggal lahir',
            'class_id' => 'kelas',
            'guardian_name' => 'nama orang tua/wali',
            'guardian_email' => 'email orang tua/wali',
            'guardian_relationship' => 'hubungan',
            'accept_policies' => 'persetujuan kebijakan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.in' => 'Jenis akun ini tidak dapat didaftarkan sendiri. Akun guru dan staf dibuat oleh admin.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk atau gunakan email lain.',
            'guardian_email.different' => 'Email orang tua/wali harus berbeda dari email Anda.',
            'accept_policies.accepted' => 'Anda perlu membaca dan menyetujui Kebijakan Privasi serta Syarat & Ketentuan untuk mendaftar.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'phone.regex' => 'Nomor WhatsApp hanya boleh berisi angka, spasi, +, - atau tanda kurung.',
            'website.max' => 'Pendaftaran tidak dapat diproses.',
        ];
    }
}
