<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emprendedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Admin\StoreEmprendedorRequest;
use App\Http\Requests\Admin\UpdateEmprendedorRequest;
use Illuminate\Support\Facades\Storage;
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
    public function store(StoreEmprendedorRequest $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Obtener datos validados
        |--------------------------------------------------------------------------
        |
        | El FormRequest ya verificó que los campos sean correctos.
        | Aquí el controller no repite validaciones.
        |
        */

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | 2. Guardar fotografía si fue enviada
        |--------------------------------------------------------------------------
        |
        | La imagen se guarda en el disco public, dentro de:
        |
        | storage/app/public/emprendedores/fotografias
        |
        | En la base de datos solo guardamos la ruta relativa.
        |
        */

        if ($request->hasFile('fotografia')) {
            $data['fotografia'] = $request
                ->file('fotografia')
                ->store('emprendedores/fotografias', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Crear emprendedor
        |--------------------------------------------------------------------------
        |
        | qr_url queda vacío por ahora.
        | Se llenará en T-13 cuando se integre QrCodeService.
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
    /**
     * Actualiza los datos de un emprendedor.
     */
    public function update(UpdateEmprendedorRequest $request, Emprendedor $emprendedor): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Obtener datos validados
        |--------------------------------------------------------------------------
        |
        | Si no viene una nueva fotografía, se conserva la anterior.
        |
        */

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | 2. Reemplazar fotografía si se subió una nueva
        |--------------------------------------------------------------------------
        |
        | Primero eliminamos la fotografía anterior para no dejar archivos
        | huérfanos en storage.
        |
        */

        if ($request->hasFile('fotografia')) {
            if ($emprendedor->fotografia) {
                Storage::disk('public')->delete($emprendedor->fotografia);
            }

            $data['fotografia'] = $request
                ->file('fotografia')
                ->store('emprendedores/fotografias', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Actualizar emprendedor
        |--------------------------------------------------------------------------
        |
        | Solo se actualizan los datos validados.
        |
        */

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