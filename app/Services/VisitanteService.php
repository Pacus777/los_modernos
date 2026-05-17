<?php

namespace App\Services;

use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitanteService
{
    private const SESSION_VISITANTE_ID = 'visitante_id';

    /**
     * Asocia una donación a un visitante cuando el turista indica su nombre (opcional).
     */
    public function resolverParaDonacion(Request $request, ?string $nombre): ?int
    {
        $nombre = trim((string) $nombre);

        if ($nombre === '') {
            return null;
        }

        $visitanteId = $request->session()->get(self::SESSION_VISITANTE_ID);
        $visitante = $visitanteId ? Visitante::query()->find($visitanteId) : null;

        if ($visitante) {
            $visitante->update([
                'nombre' => $nombre,
                'idioma' => $this->obtenerIdioma($request),
                'session_id' => $request->session()->getId(),
            ]);

            return $visitante->id;
        }

        $visitante = Visitante::query()->create([
            'codigo' => $this->generarCodigoUnico(),
            'nombre' => $nombre,
            'idioma' => $this->obtenerIdioma($request),
            'session_id' => $request->session()->getId(),
        ]);

        $request->session()->put(self::SESSION_VISITANTE_ID, $visitante->id);

        return $visitante->id;
    }

    public function nombreEnSesion(Request $request): ?string
    {
        $visitanteId = $request->session()->get(self::SESSION_VISITANTE_ID);

        if (! $visitanteId) {
            return null;
        }

        return Visitante::query()->find($visitanteId)?->nombre;
    }

    private function generarCodigoUnico(): string
    {
        do {
            $codigo = 'VIS-'.strtoupper(Str::random(8));
        } while (Visitante::query()->where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function obtenerIdioma(Request $request): string
    {
        $locale = $request->session()->get('locale')
            ?? $request->session()->get('idioma')
            ?? app()->getLocale()
            ?? 'es';

        return in_array($locale, ['es', 'en'], true) ? $locale : 'es';
    }
}
