<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * E-03: el correo de acceso del emprendedor debe ser @gmail.com.
 */
class CorreoGmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $normalizado = strtolower(trim($value));

        if (! filter_var($normalizado, FILTER_VALIDATE_EMAIL)) {
            $fail('El correo no tiene un formato válido.');

            return;
        }

        if (! str_ends_with($normalizado, '@gmail.com')) {
            $fail('Usá un correo Gmail del emprendedor (por ejemplo nombre@gmail.com).');
        }
    }
}
