<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emprendedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmprendedorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EmprendedorController
    |--------------------------------------------------------------------------
    |
    | Este controller pertenece al panel administrador.
    |
    | Importante:
    | - No retorna JSON.
    | - Retorna páginas Inertia.
    | - React recibe los datos como props.
    |
    | Flujo general:
    | Laravel Controller
    | ↓
    | Inertia::render()
    | ↓
    | resources/js/Pages/Admin/Emprendedores/
    |
    */

    /**
     * Muestra el listado de emprendedores registrados.
     *
     * Esta pantalla será usada por el administrador para revisar,
     * editar o desactivar emprendedores.
     */
    public function index(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Consulta paginada de emprendedores
        |--------------------------------------------------------------------------
        |
        | Usamos paginación para evitar cargar demasiados registros en una sola
        | pantalla cuando el sistema crezca.
        |
        | latest() ordena del más reciente al más antiguo.
        |
        */

        $emprendedores = Emprendedor::query()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Emprendedores/Index', [
            'emprendedores' => $emprendedores,
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo emprendedor.
     */
    public function create(): Response
    {
        /*
        |--------------------------------------------------------------------------
        | Página de creación
        |--------------------------------------------------------------------------
        |
        | Usaremos la misma página Form.jsx para crear y editar.
        | Por eso enviamos modo = crear y emprendedor = null.
        |
        */

        return Inertia::render('Admin/Emprendedores/Form', [
            'modo' => 'crear',
            'emprendedor' => null,
        ]);
    }

    /**
     * Guarda un nuevo emprendedor en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validación inicial
        |--------------------------------------------------------------------------
        |
        | En esta tarea todavía no manejamos upload real de imagen.
        | La fotografía se trabajará en T-10.
        |
        | qr_url queda nullable porque se llenará luego con QrCodeService
        | en la tarea T-13.
        |
        */

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', 'string', 'in:activo,inactivo'],
            'meta_monto' => ['required', 'numeric', 'min:0'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Crear emprendedor
        |--------------------------------------------------------------------------
        |
        | Solo guardamos datos básicos.
        | La imagen y el QR se integrarán en tareas posteriores.
        |
        */

        Emprendedor::create($data);

        return redirect()
            ->route('admin.emprendedores.index')
            ->with('success', 'Emprendedor creado correctamente.');
    }

    /**
     * Muestra un emprendedor específico.
     *
     * De momento no usaremos una vista show detallada.
     * Si más adelante se necesita, se puede implementar.
     */
    public function show(Emprendedor $emprendedor): RedirectResponse
    {
        return redirect()->route('admin.emprendedores.edit', $emprendedor);
    }

    /**
     * Muestra el formulario para editar un emprendedor existente.
     */
    public function edit(Emprendedor $emprendedor): Response
    {
        return Inertia::render('Admin/Emprendedores/Form', [
            'modo' => 'editar',
            'emprendedor' => $emprendedor,
        ]);
    }

    /**
     * Actualiza los datos de un emprendedor.
     */
    public function update(Request $request, Emprendedor $emprendedor): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validación de actualización
        |--------------------------------------------------------------------------
        |
        | Mantenemos las mismas reglas básicas que en store.
        | La fotografía todavía no se procesa aquí porque corresponde a T-10.
        |
        */

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', 'string', 'in:activo,inactivo'],
            'meta_monto' => ['required', 'numeric', 'min:0'],
        ]);

        $emprendedor->update($data);

        return redirect()
            ->route('admin.emprendedores.index')
            ->with('success', 'Emprendedor actualizado correctamente.');
    }

    /**
     * Desactiva un emprendedor.
     *
     * Aunque el método se llama destroy por convención de Resource Controller,
     * no eliminaremos físicamente el registro.
     *
     * Para WAYNA es mejor desactivar:
     * - Se conserva historial.
     * - No se rompen futuras relaciones con campañas o donaciones.
     * - El turista ya no lo ve porque estado = inactivo.
     */
    public function destroy(Emprendedor $emprendedor): RedirectResponse
    {
        $emprendedor->update([
            'estado' => 'inactivo',
        ]);

        return redirect()
            ->route('admin.emprendedores.index')
            ->with('success', 'Emprendedor desactivado correctamente.');
    }
}