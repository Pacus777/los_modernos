<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Departamento;
use App\Enums\TipoEmprendimiento;
use App\Http\Controllers\Controller;
use App\Models\Campana;
use App\Models\Emprendedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Admin\StoreEmprendedorRequest;
use App\Http\Requests\Admin\UpdateEmprendedorRequest;
use App\Services\ImageStorageService;
use App\Services\QrCodeService;
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
            ->with([
                'campanas' => fn ($q) => $q->latest('id'),
            ])
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
            'tiposEmprendimiento' => TipoEmprendimiento::opcionesParaFormulario(),
            'departamentos' => Departamento::opcionesParaFormulario(),
        ]);
    }

    /**
     * Guarda un nuevo emprendedor en la base de datos.
     */
    public function store(
        StoreEmprendedorRequest $request,
        QrCodeService $qrCodeService,
        ImageStorageService $imageStorageService,
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | 1. Obtener datos validados
        |--------------------------------------------------------------------------
        |
        | El FormRequest ya validó todos los campos, incluida la fotografía.
        |
        */

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | 2. Guardar fotografía si fue enviada
        |--------------------------------------------------------------------------
        |
        | JPG/PNG/WebP se optimizan a WebP (T-A11) en disco public.
        | En la base de datos se guarda solo la ruta relativa.
        |
        */

        if ($request->hasFile('fotografia')) {
            $data['fotografia'] = $imageStorageService->storePublicImageAsWebp(
                $request->file('fotografia'),
                'emprendedores/fotografias',
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Crear emprendedor
        |--------------------------------------------------------------------------
        |
        | Primero necesitamos crear el emprendedor para obtener su id.
        | Ese id se usa para construir la URL pública del QR.
        |
        */

        $emprendedor = Emprendedor::create($data);

        /*
        |--------------------------------------------------------------------------
        | 4. Generar QR automático
        |--------------------------------------------------------------------------
        |
        | El admin no genera el QR manualmente.
        | El sistema lo crea al registrar el emprendedor.
        |
        */

        $rutaQr = $qrCodeService->generarQrPerfil($emprendedor);

        /*
        |--------------------------------------------------------------------------
        | 5. Guardar la ruta del QR
        |--------------------------------------------------------------------------
        |
        | qr_url guarda la ruta relativa del archivo generado.
        |
        */

        $emprendedor->update([
            'qr_url' => $rutaQr,
        ]);

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
            'tiposEmprendimiento' => TipoEmprendimiento::opcionesParaFormulario(),
            'departamentos' => Departamento::opcionesParaFormulario(),
        ]);
    }

    /**
     * Actualiza los datos de un emprendedor.
     */
    /**
     * Actualiza los datos de un emprendedor.
     */
    public function update(
        UpdateEmprendedorRequest $request,
        Emprendedor $emprendedor,
        ImageStorageService $imageStorageService,
    ): RedirectResponse {
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

            $data['fotografia'] = $imageStorageService->storePublicImageAsWebp(
                $request->file('fotografia'),
                'emprendedores/fotografias',
            );
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