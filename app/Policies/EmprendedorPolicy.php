<?php

namespace App\Policies;

use App\Models\Emprendedor;
use App\Models\User;

class EmprendedorPolicy
{
    /**
     * El admin puede hacer cualquier acción administrativa.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->tieneRol('admin')) {
            return true;
        }

        return null;
    }

    /**
     * Permite listar o acceder a secciones generales del emprendedor.
     */
    public function viewAny(User $user): bool
    {
        return $user->tieneRol('emprendedor');
    }

    /**
     * El emprendedor solo puede ver su propio registro.
     */
    public function view(User $user, Emprendedor $emprendedor): bool
    {
        return $this->perteneceAlUsuario($user, $emprendedor);
    }

    /**
     * El emprendedor solo puede actualizar su propio perfil.
     */
    public function update(User $user, Emprendedor $emprendedor): bool
    {
        return $this->perteneceAlUsuario($user, $emprendedor);
    }

    /**
     * Permiso futuro para gestionar metas propias.
     */
    public function manageMeta(User $user, Emprendedor $emprendedor): bool
    {
        return $this->perteneceAlUsuario($user, $emprendedor);
    }

    /**
     * Permiso futuro para consultar historial propio.
     */
    public function viewDonations(User $user, Emprendedor $emprendedor): bool
    {
        return $this->perteneceAlUsuario($user, $emprendedor);
    }

    private function perteneceAlUsuario(User $user, Emprendedor $emprendedor): bool
    {
        return $user->tieneRol('emprendedor')
            && (int) $emprendedor->user_id === (int) $user->id;
    }
}