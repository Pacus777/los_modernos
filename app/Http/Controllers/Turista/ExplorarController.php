<?php

namespace App\Http\Controllers\Turista;

use App\Http\Controllers\Controller;
use App\Services\EmprendedorExplorarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class ExplorarController extends Controller
{
    public function __invoke(Request $request, EmprendedorExplorarService $explorar): Response
    {
        $user = $request->user();
        $panelUrl = null;

        if ($user) {
            $panelUrl = match ($user->rol?->nombre) {
                'admin' => route('admin.dashboard'),
                'cajero' => route('cajero.efectivo'),
                default => null,
            };
        }

        $desdeRequest = $explorar->filtrosDesdeRequest($request);
        $locale = $this->localeTurista($request);

        return Inertia::render('Landing', [
            'canLogin' => Route::has('login'),
            'panelUrl' => $panelUrl,
            'marketUrl' => 'https://www.waynamercados.com/',
            'emprendedores' => $explorar->listarTarjetas($desdeRequest['filtros'], $locale),
            'destacados' => $explorar->listarDestacados(8, $locale),
            'stats' => $explorar->estadisticasLanding(),
            'filtros' => $desdeRequest['filtros'],
            'catalogos' => $desdeRequest['catalogos'],
        ]);
    }

    private function localeTurista(Request $request): string
    {
        $locale = $request->session()->get('locale', 'es');

        return in_array($locale, ['es', 'en'], true) ? $locale : 'es';
    }
}
