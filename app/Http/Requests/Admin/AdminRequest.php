<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Base for admin-core form requests: Indonesian attribute names and the
 * password policy from Settings › Keamanan.
 */
abstract class AdminRequest extends FormRequest
{
    /**
     * Password rule built from the security.password_* settings.
     */
    protected function passwordRule(): Password
    {
        $rule = Password::min(max(8, (int) setting('security.password_min_length', 8)));

        if (setting('security.password_require_mixed_case', true)) {
            $rule->mixedCase();
        }
        if (setting('security.password_require_numbers', true)) {
            $rule->numbers();
        }
        if (setting('security.password_require_symbols', false)) {
            $rule->symbols();
        }

        return $rule;
    }

    /**
     * Strip thousands separators from a money input ("1.250.000" → "1250000").
     */
    protected function normaliseMoney(string $key): void
    {
        $value = $this->input($key);
        if (is_string($value) && $value !== '') {
            $this->merge([$key => preg_replace('/[^\d]/', '', $value)]);
        }
    }

    /**
     * Turn blank strings into null for optional fields.
     *
     * @param  array<int, string>  $keys
     */
    protected function blankToNull(array $keys): void
    {
        $values = [];
        foreach ($keys as $key) {
            if ($this->has($key) && is_string($this->input($key)) && trim($this->input($key)) === '') {
                $values[$key] = null;
            }
        }
        if ($values !== []) {
            $this->merge($values);
        }
    }
}
