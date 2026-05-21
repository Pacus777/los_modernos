<?php

namespace App\Services;

use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\EmprendedorNotificacion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmprendedorNotificacionService
{
    public function resumenParaNavbar(Emprendedor $emprendedor): array
    {
        $items = EmprendedorNotificacion::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn (EmprendedorNotificacion $n) => $this->formatear($n));

        return [
            'no_leidas' => $this->contarNoLeidas($emprendedor),
            'items' => $items,
        ];
    }

    public function listar(Emprendedor $emprendedor, int $porPagina = 15): LengthAwarePaginator
    {
        return EmprendedorNotificacion::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->orderByDesc('created_at')
            ->paginate($porPagina)
            ->through(fn (EmprendedorNotificacion $n) => $this->formatear($n));
    }

    public function contarNoLeidas(Emprendedor $emprendedor): int
    {
        return EmprendedorNotificacion::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->whereNull('leida_at')
            ->count();
    }

    public function registrarDonacionValidada(Donacion $donacion, Emprendedor $emprendedor): void
    {
        $monto = number_format((float) $donacion->monto, 2, ',', '.');
        $campana = $donacion->campana?->titulo ?? 'tu campaña';
        $apoyo = trim((string) ($donacion->visitante?->nombre ?? ''));

        $mensaje = $apoyo !== ''
            ? "{$apoyo} aportó Bs {$monto} a «{$campana}». Ya suma a tu recaudación."
            : "Recibiste un aporte de Bs {$monto} en «{$campana}». Ya suma a tu recaudación.";

        EmprendedorNotificacion::query()->firstOrCreate(
            [
                'emprendedor_id' => $emprendedor->id,
                'donacion_id' => $donacion->id,
                'tipo' => EmprendedorNotificacion::TIPO_DONACION_VALIDADA,
            ],
            [
                'titulo' => 'Nuevo aporte validado',
                'mensaje' => $mensaje,
                'monto' => $donacion->monto,
            ],
        );
    }

    public function marcarLeida(Emprendedor $emprendedor, int $notificacionId): bool
    {
        $notificacion = EmprendedorNotificacion::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->whereKey($notificacionId)
            ->first();

        if (! $notificacion || $notificacion->estaLeida()) {
            return false;
        }

        $notificacion->update(['leida_at' => now()]);

        return true;
    }

    public function marcarTodasLeidas(Emprendedor $emprendedor): int
    {
        return EmprendedorNotificacion::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatear(EmprendedorNotificacion $notificacion): array
    {
        return [
            'id' => $notificacion->id,
            'tipo' => $notificacion->tipo,
            'titulo' => $notificacion->titulo,
            'mensaje' => $notificacion->mensaje,
            'monto' => $notificacion->monto !== null ? (float) $notificacion->monto : null,
            'leida' => $notificacion->estaLeida(),
            'created_at' => $notificacion->created_at?->toIso8601String(),
            'created_at_humano' => $notificacion->created_at?->diffForHumans(),
        ];
    }
}
