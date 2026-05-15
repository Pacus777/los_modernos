<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emprendedor;
use App\Models\Punto;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PuntoController extends Controller
{
    /**
     * Lista puntos físicos registrados.
     */
    public function index(): Response
    {
        $puntos = Punto::query()
            ->withCount('emprendedores')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Puntos/Index', [
            'puntos' => $puntos,
        ]);
    }

    /**
     * Formulario de creación.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Puntos/Form', [
            'modo' => 'crear',
            'punto' => null,
            'emprendedores' => $this->emprendedoresActivos(),
        ]);
    }

    /**
     * Guarda un punto físico y genera su QR.
     */
    public function store(Request $request, QrCodeService $qrCodeService): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', 'string', 'in:activo,inactivo'],
            'emprendedores' => ['nullable', 'array'],
            'emprendedores.*' => ['integer', 'exists:emprendedores,id'],
        ]);

        $emprendedorIds = $data['emprendedores'] ?? [];
        unset($data['emprendedores']);

        /*
        |--------------------------------------------------------------------------
        | Slug estable
        |--------------------------------------------------------------------------
        |
        | El slug se genera una vez al crear el punto.
        | No lo cambiamos automáticamente después porque el QR físico
        | podría estar impreso.
        |
        */

        $data['slug'] = $this->generarSlugUnico($data['nombre']);

        $punto = Punto::create($data);

        $punto->emprendedores()->sync($emprendedorIds);

        $rutaQr = $qrCodeService->generarQrPunto($punto);

        $punto->update([
            'qr_url' => $rutaQr,
        ]);

        return redirect()
            ->route('admin.puntos.index')
            ->with('success', 'Punto físico creado correctamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Punto $punto): Response
    {
        $punto->load('emprendedores:id');

        return Inertia::render('Admin/Puntos/Form', [
            'modo' => 'editar',
            'punto' => $punto,
            'emprendedores' => $this->emprendedoresActivos(),
            'emprendedoresSeleccionados' => $punto->emprendedores->pluck('id'),
        ]);
    }

    /**
     * Actualiza datos del punto.
     *
     * No regeneramos slug automáticamente para no romper QR físicos impresos.
     */
    public function update(Request $request, Punto $punto): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', 'string', 'in:activo,inactivo'],
            'emprendedores' => ['nullable', 'array'],
            'emprendedores.*' => ['integer', 'exists:emprendedores,id'],
        ]);

        $emprendedorIds = $data['emprendedores'] ?? [];
        unset($data['emprendedores']);

        $punto->update($data);

        $punto->emprendedores()->sync($emprendedorIds);

        return redirect()
            ->route('admin.puntos.index')
            ->with('success', 'Punto físico actualizado correctamente.');
    }

    /**
     * Desactiva el punto físico.
     *
     * No eliminamos físicamente para no perder trazabilidad ni romper registros.
     */
    public function destroy(Punto $punto): RedirectResponse
    {
        $punto->update([
            'estado' => 'inactivo',
        ]);

        return redirect()
            ->route('admin.puntos.index')
            ->with('success', 'Punto físico desactivado correctamente.');
    }

    /**
     * Obtiene emprendedores activos para asociarlos al punto.
     */
    private function emprendedoresActivos()
    {
        return Emprendedor::query()
            ->where('estado', 'activo')
            ->select('id', 'nombre', 'apellidos')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($emprendedor) => [
                'id' => $emprendedor->id,
                'nombre_completo' => $emprendedor->nombreCompleto(),
            ]);
    }

    /**
     * Genera un slug único a partir del nombre del punto.
     */
    private function generarSlugUnico(string $nombre): string
    {
        $base = Str::slug($nombre);

        if ($base === '') {
            $base = 'punto';
        }

        $slug = $base;
        $contador = 2;

        while (Punto::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }
}