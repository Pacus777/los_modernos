<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampanaRequest;
use App\Http\Requests\Admin\UpdateCampanaRequest;
use App\Models\Campana;
use App\Models\Emprendedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CampanaController extends Controller
{
    /**
     * Listado paginado de campañas para el panel admin (PB-13 / T-31).
     */
    public function index(): Response
    {
        $campanas = Campana::query()
            ->with('emprendedor:id,nombre,apellidos')
            ->withCount('donaciones')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Campanas/Index', [
            'campanas' => $campanas,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Campanas/Form', [
            'modo' => 'crear',
            'campana' => null,
            'emprendedores' => $this->listaEmprendedores(),
            'fechaHoy' => now()->toDateString(),
        ]);
    }

    public function store(StoreCampanaRequest $request): RedirectResponse
    {
        Campana::query()->create([
            ...$request->validated(),
            'monto_recaudado' => 0,
        ]);

        return redirect()
            ->route('admin.campanas.index')
            ->with('success', 'Campaña creada correctamente.');
    }

    public function show(Campana $campana): RedirectResponse
    {
        return redirect()->route('admin.campanas.edit', $campana);
    }

    public function edit(Campana $campana): Response
    {
        $campana->load('emprendedor:id,nombre,apellidos');

        return Inertia::render('Admin/Campanas/Form', [
            'modo' => 'editar',
            'campana' => $campana,
            'emprendedores' => $this->listaEmprendedores(),
            'fechaHoy' => now()->toDateString(),
        ]);
    }

    public function update(UpdateCampanaRequest $request, Campana $campana): RedirectResponse
    {
        $campana->update($request->validated());

        return redirect()
            ->route('admin.campanas.index')
            ->with('success', 'Campaña actualizada correctamente.');
    }

    /**
     * Si hay donaciones asociadas no se elimina el registro (FK): se finaliza la campaña.
     */
    public function destroy(Campana $campana): RedirectResponse
    {
        if ($campana->donaciones()->exists()) {
            $campana->update([
                'estado' => Campana::ESTADO_FINALIZADA,
            ]);

            return redirect()
                ->route('admin.campanas.index')
                ->with(
                    'success',
                    'La campaña tiene donaciones: se marcó como finalizada. No se eliminó el historial.'
                );
        }

        $campana->delete();

        return redirect()
            ->route('admin.campanas.index')
            ->with('success', 'Campaña eliminada correctamente.');
    }

    /**
     * Emprendedores para el selector del formulario (llegan en la misma respuesta Inertia).
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, label: string, nombre: string, descripcion: string|null, estado: string}>
     */
    private function listaEmprendedores()
    {
        return Emprendedor::query()
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos', 'descripcion', 'estado'])
            ->map(fn (Emprendedor $e) => [
                'id' => $e->id,
                'nombre' => trim($e->nombre.' '.$e->apellidos),
                'descripcion' => ($d = trim((string) $e->descripcion)) !== '' ? $d : null,
                'estado' => $e->estado,
                'label' => $this->etiquetaEmprendedorParaSelector($e),
            ]);
    }

    private function etiquetaEmprendedorParaSelector(Emprendedor $e): string
    {
        $nombre = trim($e->nombre.' '.$e->apellidos);
        $partes = [$nombre];

        $descripcion = trim((string) $e->descripcion);
        if ($descripcion !== '') {
            $partes[] = Str::limit($descripcion, 55);
        }

        if ($e->estado !== 'activo') {
            $partes[] = 'inactivo';
        }

        return implode(' · ', $partes);
    }
}
