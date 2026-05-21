<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Emprendedor;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'must_change_password', 'google2fa_secret', 'google2fa_confirmed_at'])]
#[Hidden(['password', 'remember_token', 'google2fa_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'google2fa_confirmed_at' => 'datetime',
            'google2fa_secret' => 'encrypted',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * E-04: credencial temporal enviada por admin — debe elegir contraseña propia.
     */
    public function debeCambiarPassword(): bool
    {
        return (bool) $this->must_change_password;
    }
    public function emprendedor(): HasOne
    {
        return $this->hasOne(Emprendedor::class, 'user_id');
    }

        /**
     * Relación directa con la tabla intermedia user_roles.
     *
     * Esta relación permite conocer la asignación de rol del usuario.
     * No devuelve el rol directamente, sino el registro intermedio.
     *
     * Ejemplo:
     * User -> UserRol
     */
    public function userRol(): HasOne
    {
        return $this->hasOne(UserRol::class, 'user_id');
    }

    /**
     * Relación directa desde User hasta Rol pasando por UserRol.
     *
     * Esta relación permite acceder al rol de forma cómoda:
     *
     * $user->rol
     *
     * En lugar de:
     *
     * $user->userRol->rol
     *
     * Recorrido real:
     * users.id -> user_roles.user_id -> user_roles.role_id -> roles.id
     */
    public function rol(): HasOneThrough
    {
        return $this->hasOneThrough(
            Rol::class,      // Modelo final al que queremos llegar.
            UserRol::class,  // Modelo intermedio.
            'user_id',       // FK en user_roles que apunta a users.id.
            'id',            // PK en roles.
            'id',            // PK en users.
            'role_id'        // FK en user_roles que apunta a roles.id.
        );
    }

    /**
     * Devuelve el nombre del rol del usuario.
     *
     * Se usa para redireccionar después del login y para validar accesos.
     * Si el usuario no tiene rol asignado, devuelve null.
     */
    public function nombreRol(): ?string
    {
        return $this->rol?->nombre;
    }

    /**
     * Verifica si el usuario tiene un rol específico.
     *
     * Ejemplo:
     * $user->tieneRol('admin')
     */
    public function tieneRol(string $rol): bool
    {
        return $this->nombreRol() === $rol;
    }

    /**
     * Verifica si el usuario tiene alguno de varios roles permitidos.
     *
     * Ejemplo:
     * $user->tieneAlgunoDeEstosRoles(['admin', 'cajero'])
     */
    public function tieneAlgunoDeEstosRoles(array $roles): bool
    {
        return in_array($this->nombreRol(), $roles, true);
    }

    /**
     * Verifica de forma simple si el usuario es un emprendedor.
     */
    public function esEmprendedor(): bool
    {
        return $this->tieneRol('emprendedor');
    }

    /**
     * Verifica de forma simple si el usuario es administrador.
     */
    public function esAdmin(): bool
    {
        return $this->tieneRol('admin');
    }

    /**
     * S4-09: Verifica si el usuario es un turista con cuenta (autenticado sin rol de staff).
     */
    public function esTuristaCuenta(): bool
    {
        return ! $this->tieneAlgunoDeEstosRoles(['admin', 'cajero', 'emprendedor']);
    }

    /**
     * 2FA TOTP activo (solo aplica flujo admin, S3-04).
     */
    public function tieneDosFactoresActivo(): bool
    {
        return filled($this->google2fa_secret)
            && $this->google2fa_confirmed_at !== null;
    }
}
