<?php

namespace App\Services;

use App\Models\Emprendedor;

class EmprendedorOnboardingService
{
    /**
     * S4-09/E-12: Genera el checklist de onboarding del emprendedor.
     *
     * @return array{
     *     completo: bool,
     *     porcentaje: int,
     *     items: list<array{
     *         id: string,
     *         titulo: string,
     *         descripcion: string,
     *         completo: bool,
     *         bloqueante: bool,
     *         url: string
     *     }>
     * }
     */
    public function checklist(Emprendedor $emprendedor): array
    {
        $items = [
            [
                'id' => 'datos_basicos',
                'titulo' => 'Datos principales',
                'descripcion' => 'Completa tu nombre, apellidos, departamento y tipo de emprendimiento.',
                'completo' => $this->validarDatosBasicos($emprendedor),
                'bloqueante' => true,
                'url' => route('emprendedor.perfil.edit'),
            ],
            [
                'id' => 'descripcion',
                'titulo' => 'Descripción o historia',
                'descripcion' => 'Escribe una historia o descripción detallada sobre tu negocio (mínimo 10 caracteres).',
                'completo' => $this->validarDescripcion($emprendedor),
                'bloqueante' => true,
                'url' => route('emprendedor.perfil.edit'),
            ],
            [
                'id' => 'fotografia',
                'titulo' => 'Fotografía de perfil',
                'descripcion' => 'Sube una foto que te represente a ti o a tu emprendimiento en la plataforma.',
                'completo' => $this->validarFotografia($emprendedor),
                'bloqueante' => true,
                'url' => route('emprendedor.perfil.edit'),
            ],
            [
                'id' => 'redes_sociales',
                'titulo' => 'Redes sociales',
                'descripcion' => 'Recomendado. Agrega al menos una red social (WhatsApp, Instagram, Facebook o TikTok).',
                'completo' => $this->validarRedesSociales($emprendedor),
                'bloqueante' => false,
                'url' => route('emprendedor.perfil.edit'),
            ],
            [
                'id' => 'campana_activa',
                'titulo' => 'Meta de apoyo activa',
                'descripcion' => 'Recomendado. Crea tu primera meta de apoyo para empezar a recibir donaciones.',
                'completo' => $this->validarCampanaActiva($emprendedor),
                'bloqueante' => false,
                'url' => route('emprendedor.mis-metas.index'),
            ],
        ];

        $bloqueantesCompletos = collect($items)
            ->where('bloqueante', true)
            ->every('completo', true);

        $completadosCount = collect($items)
            ->where('completo', true)
            ->count();

        $totalCount = count($items);
        $porcentaje = $totalCount > 0
            ? (int) round(($completadosCount / $totalCount) * 100)
            : 0;

        return [
            'completo' => $bloqueantesCompletos,
            'porcentaje' => $porcentaje,
            'items' => $items,
        ];
    }

    /**
     * Determina de manera ágil si el perfil está completo (pasa los bloqueantes).
     */
    public function estaCompleto(Emprendedor $emprendedor): bool
    {
        return $this->validarDatosBasicos($emprendedor)
            && $this->validarDescripcion($emprendedor)
            && $this->validarFotografia($emprendedor);
    }

    private function validarDatosBasicos(Emprendedor $emprendedor): bool
    {
        return ! empty($emprendedor->nombre)
            && ! empty($emprendedor->apellidos)
            && ! empty($emprendedor->tipo_emprendimiento)
            && ! empty($emprendedor->departamento);
    }

    private function validarDescripcion(Emprendedor $emprendedor): bool
    {
        return ! empty($emprendedor->descripcion)
            && strlen(trim($emprendedor->descripcion)) >= 10;
    }

    private function validarFotografia(Emprendedor $emprendedor): bool
    {
        return ! empty($emprendedor->fotografia);
    }

    private function validarRedesSociales(Emprendedor $emprendedor): bool
    {
        return ! empty($emprendedor->whatsapp)
            || ! empty($emprendedor->instagram)
            || ! empty($emprendedor->facebook)
            || ! empty($emprendedor->tiktok)
            || ! empty($emprendedor->sitio_web);
    }

    private function validarCampanaActiva(Emprendedor $emprendedor): bool
    {
        return $emprendedor->campanas()
            ->where('estado', 'activa')
            ->exists();
    }
}
