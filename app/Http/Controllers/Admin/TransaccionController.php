<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransaccionController extends Controller
{
    /**
     * Listado paginado de trazabilidad (PB-12 / T-36).
     *
     * Vista reservada para superadmin (T-A20). Ruta comentada en routes/admin.php;
     * el registro en BD continúa vía TraceabilityService.
     *
     * Filtros vía query string: estado, origen, fecha_desde, fecha_hasta.
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'estado' => ['nullable', 'string', 'max:50'],
            'origen' => ['nullable', 'string', 'max:255'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
        ]);

        $filters = array_merge([
            'estado' => '',
            'origen' => '',
            'fecha_desde' => '',
            'fecha_hasta' => '',
        ], $validated);

        $transacciones = Transaccion::query()
            ->when(
                filled($filters['estado'] ?? null),
                fn ($q) => $q->where('estado', $filters['estado'])
            )
            ->when(
                filled($filters['origen'] ?? null),
                fn ($q) => $q->where('origen', 'like', '%'.$filters['origen'].'%')
            )
            ->when(
                filled($filters['fecha_desde'] ?? null),
                fn ($q) => $q->whereDate('created_at', '>=', $filters['fecha_desde'])
            )
            ->when(
                filled($filters['fecha_hasta'] ?? null),
                fn ($q) => $q->whereDate('created_at', '<=', $filters['fecha_hasta'])
            )
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Transacciones/Index', [
            'transacciones' => $transacciones,
            'filters' => [
                'estado' => $filters['estado'] ?? '',
                'origen' => $filters['origen'] ?? '',
                'fecha_desde' => $filters['fecha_desde'] ?? '',
                'fecha_hasta' => $filters['fecha_hasta'] ?? '',
            ],
        ]);
    }
}
