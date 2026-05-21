<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CrearEmprendedorCuentaRequest;
use App\Models\Emprendedor;
use App\Services\EmprendedorCuentaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class EmprendedorCuentaController extends Controller
{
    public function store(
        CrearEmprendedorCuentaRequest $request,
        Emprendedor $emprendedor,
        EmprendedorCuentaService $cuentaService,
    ): RedirectResponse {
        try {
            $resultado = $cuentaService->crearCuenta(
                $emprendedor,
                $request->validated('email'),
                $request->validated('name'),
                $request->user(),
            );
        } catch (RuntimeException $exception) {
            return $this->redirigirConError($request, $emprendedor, $exception->getMessage());
        }

        if ($resultado['correo_enviado']) {
            $mensaje = 'Cuenta creada. Enviamos usuario y contraseña a '.$resultado['user']->email.'.';
            $flash = 'success';
        } else {
            $mensaje = 'Cuenta creada para '.$resultado['user']->email.', pero no pudimos enviar el correo. Usá «Regenerar contraseña y reenviar» o avisá a soporte técnico.';
            $flash = 'error';
        }

        return redirect()
            ->to($this->rutaTrasCuenta($request, $emprendedor))
            ->with($flash, $mensaje);
    }

    public function reenviar(
        Request $request,
        Emprendedor $emprendedor,
        EmprendedorCuentaService $cuentaService,
    ): RedirectResponse {

        try {
            $resultado = $cuentaService->reenviarCredenciales($emprendedor, $request->user());
        } catch (RuntimeException $exception) {
            return $this->redirigirConError($request, $emprendedor, $exception->getMessage());
        }

        if ($resultado['correo_enviado']) {
            $mensaje = 'Nueva contraseña enviada a '.$resultado['user']->email.'.';
            $flash = 'success';
        } else {
            $mensaje = 'Se actualizó la contraseña, pero el correo no se pudo enviar. Probá «Regenerar contraseña y reenviar» de nuevo en unos minutos.';
            $flash = 'error';
        }

        return redirect()
            ->to($this->rutaTrasCuenta($request, $emprendedor))
            ->with($flash, $mensaje);
    }

    private function redirigirConError(
        CrearEmprendedorCuentaRequest|Request $request,
        Emprendedor $emprendedor,
        string $mensaje,
    ): RedirectResponse {
        return redirect()
            ->to($this->rutaTrasCuenta($request, $emprendedor))
            ->with('error', $mensaje);
    }

    private function rutaTrasCuenta(Request $request, Emprendedor $emprendedor): string
    {
        if ($request->boolean('desde_listado')) {
            return route('admin.emprendedores.index');
        }

        if ($request->boolean('finalizar_registro')) {
            return route('admin.emprendedores.finalizar', $emprendedor);
        }

        return route('admin.emprendedores.edit', $emprendedor);
    }
}
