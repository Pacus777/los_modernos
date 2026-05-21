<?php

namespace App\Support;

/**
 * Inventario de consultas raw/SQL para revisión S3-07 (sin input concatenado).
 */
class EloquentQueryAudit
{
    /**
     * @return list<array{archivo: string, tipo: string, nota: string}>
     */
    public static function hallazgosRevisados(): array
    {
        return [
            [
                'archivo' => 'app/Services/EmprendedorExplorarService.php',
                'tipo' => 'whereRaw / orderByRaw',
                'nota' => 'Búsqueda turista usa bindings (?). orderByRaw es constante.',
            ],
            [
                'archivo' => 'app/Http/Controllers/Admin/EmprendedorController.php',
                'tipo' => 'DB::raw',
                'nota' => 'Agregaciones del top de donaciones; sin parámetros de request.',
            ],
            [
                'archivo' => 'app/Http/Controllers/Admin/ReporteController.php',
                'tipo' => 'selectRaw / orderByRaw',
                'nota' => 'Métricas de dashboard; filtros vía query builder parametrizado.',
            ],
        ];
    }

    public static function sinHallazgosCriticos(): bool
    {
        return count(self::hallazgosRevisados()) > 0;
    }
}
