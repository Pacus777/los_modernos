<?php

namespace App\Http\Controllers\Emprendedor;

use App\Http\Controllers\Controller;
use App\Services\EmprendedorDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, EmprendedorDashboardService $dashboardService): Response
    {
        $user = $request->user();
        $emprendedor = $user->emprendedor;

        return Inertia::render('Emprendedor/Dashboard', [
            'panel' => $emprendedor
                ? $dashboardService->datosParaPanel($emprendedor)
                : null,
            'usuario' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
