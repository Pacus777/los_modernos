<?php

namespace App\Http\Controllers\Admin;


use App\Models\Donacion;
use Illuminate\Support\Facades\DB;
use App\Enums\AuditAction;
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
use App\Services\AuditLogService;
use App\Services\EmprendedorMediosService;
use App\Services\ImageStorageService;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Storage;

class EmprendedorController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {
    }

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

        /*
        |--------------------------------------------------------------------------
        | T-A33: Top de emprendedores con mejores donaciones
        |--------------------------------------------------------------------------
        |
        | Este informe se calcula desde donaciones validadas, conectando:
        | donaciones -> campanas -> emprendedores.
        |
        | No se crea una tabla nueva porque es un reporte derivado.
        |
        */

        $topDonacionesEmprendedores = DB::table('donaciones')
            ->join('campanas', 'campanas.id', '=', 'donaciones.campana_id')
            ->join('emprendedores', 'emprendedores.id', '=', 'campanas.emprendedor_id')
            ->where('donaciones.estado_pago', Donacion::ESTADO_VALIDADO)
            ->select([
                'emprendedores.id',
                'emprendedores.nombre',
                'emprendedores.apellidos',
                'emprendedores.fotografia',
                DB::raw('COALESCE(SUM(donaciones.monto), 0) as total_donado'),
                DB::raw('COUNT(donaciones.id) as total_donaciones'),
                DB::raw('MAX(donaciones.updated_at) as ultima_donacion_at'),
            ])
            ->groupBy(
                'emprendedores.id',
                'emprendedores.nombre',
                'emprendedores.apellidos',
                'emprendedores.fotografia',
            )
            ->orderByDesc('total_donado')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'id' => (int) $item->id,
                'nombre' => $item->nombre,
                'apellidos' => $item->apellidos,
                'fotografia' => $item->fotografia,
                'total_donado' => (float) $item->total_donado,
                'total_donaciones' => (int) $item->total_donaciones,
                'ultima_donacion_at' => $item->ultima_donacion_at,
            ]);

        return Inertia::render('Admin/Emprendedores/Index', [
            'emprendedores' => $emprendedores,
            'topDonacionesEmprendedores' => $topDonacionesEmprendedores,
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
        EmprendedorMediosService $emprendedorMediosService,
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | 1. Obtener datos validados
        |--------------------------------------------------------------------------
        |
        | El FormRequest ya validó todos los campos, incluida la fotografía.
        |
        */

        $data = $this->datosEmprendedorSinMedios($request);

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

        $emprendedorMediosService->sincronizarDesdeRequest($emprendedor, $request);

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

        $this->auditLogService->registrar(
            AuditAction::AdminEntrepreneurCreated,
            subject: $emprendedor->fresh(),
            actor: $request->user(),
            metadata: ['nombre' => $emprendedor->nombreCompleto()],
            request: $request,
        );

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
     * Genera el QR de perfil si el emprendedor aún no tiene uno (qr_url vacío).
     */
    public function generarQr(Emprendedor $emprendedor, QrCodeService $qrCodeService): RedirectResponse
    {
        if (filled($emprendedor->qr_url)) {
            return redirect()
                ->back()
                ->with('error', 'Este emprendedor ya tiene un código QR. Usá «Ver QR» en el listado.');
        }

        $rutaQr = $qrCodeService->generarQrPerfil($emprendedor);

        $emprendedor->update([
            'qr_url' => $rutaQr,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Código QR de perfil generado correctamente.');
    }

    /**
     * Actualiza los datos de un emprendedor.
     */
    public function update(
        UpdateEmprendedorRequest $request,
        Emprendedor $emprendedor,
        ImageStorageService $imageStorageService,
        EmprendedorMediosService $emprendedorMediosService,
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | 1. Obtener datos validados
        |--------------------------------------------------------------------------
        |
        | Si no viene una nueva fotografía, se conserva la anterior.
        |
        */

        $data = $this->datosEmprendedorSinMedios($request);

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

        $emprendedorMediosService->sincronizarDesdeRequest($emprendedor->fresh(), $request);

        $this->auditLogService->registrar(
            AuditAction::AdminEntrepreneurUpdated,
            subject: $emprendedor->fresh(),
            actor: $request->user(),
            metadata: ['nombre' => $emprendedor->nombreCompleto()],
            request: $request,
        );

        return redirect()
            ->route('admin.emprendedores.index')
            ->with('success', 'Emprendedor actualizado correctamente.');
    }

    /**
     * Campos de texto/número del emprendedor, sin archivos ni metadatos de galería.
     *
     * @return array<string, mixed>
     */
    private function datosEmprendedorSinMedios(StoreEmprendedorRequest|UpdateEmprendedorRequest $request): array
    {
        return collect($request->validated())->except([
            'fotografia',
            'foto_empresa',
            'galeria',
            'galeria_nuevas',
            'galeria_conservar',
            'video',
            'video_enlace',
            'quitar_video',
        ])->all();
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
    public function destroy(Emprendedor $emprendedor, Request $request): RedirectResponse
    {
        $emprendedor->update([
            'estado' => 'inactivo',
        ]);

        $this->auditLogService->registrar(
            AuditAction::AdminEntrepreneurDeactivated,
            subject: $emprendedor->fresh(),
            actor: $request->user(),
            metadata: ['nombre' => $emprendedor->nombreCompleto()],
            request: $request,
        );

        return redirect()
            ->route('admin.emprendedores.index')
            ->with('success', 'Emprendedor desactivado correctamente.');
    }
}