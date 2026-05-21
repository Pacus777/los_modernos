<?php

namespace App\Policies;

use App\Models\Campana;
use App\Models\User;

class CampanaPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->tieneRol('admin')) {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        return $user->tieneRol('emprendedor') && $user->emprendedor !== null;
    }

    public function update(User $user, Campana $campana): bool
    {
        return $this->perteneceAlEmprendedor($user, $campana)
            && $campana->estado === Campana::ESTADO_ACTIVA;
    }

    public function close(User $user, Campana $campana): bool
    {
        return $this->perteneceAlEmprendedor($user, $campana)
            && in_array($campana->estado, [Campana::ESTADO_ACTIVA, Campana::ESTADO_INACTIVA], true);
    }

    private function perteneceAlEmprendedor(User $user, Campana $campana): bool
    {
        return $user->tieneRol('emprendedor')
            && (int) $user->emprendedor?->id === (int) $campana->emprendedor_id;
    }
}
