<?php

namespace App\Support;

/**
 * Reglas de correo Gmail para cuentas emprendedor (E-03).
 */
class CorreoGmailEmprendedor
{
    public static function exigido(): bool
    {
        return (bool) config('wayna.emprendedor_cuenta.exigir_email_gmail', true);
    }

    public static function esGmail(?string $email): bool
    {
        if (! is_string($email) || $email === '') {
            return false;
        }

        return str_ends_with(strtolower(trim($email)), '@gmail.com')
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
