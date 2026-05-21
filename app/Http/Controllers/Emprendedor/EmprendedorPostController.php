<?php

namespace App\Http\Controllers\Emprendedor;

use App\Enums\EmprendedorPostEstado;
use App\Enums\EmprendedorPostTipo;
use App\Http\Controllers\Controller;
use App\Models\Emprendedor;
use App\Models\EmprendedorPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmprendedorPostController extends Controller
{
    public function index(Request $request): Response
    {
        $emprendedor = $this->emprendedorActual($request);

        abort_unless($emprendedor, 403, 'Tu cuenta no está vinculada a un emprendedor.');

        $posts = EmprendedorPost::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->withCount('reacciones')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->through(fn (EmprendedorPost $post) => [
                'id' => $post->id,
                'tipo' => $post->tipo?->value ?? $post->tipo,
                'contenido' => $post->contenido,
                'media_path' => $post->media_path,
                'media_url' => $post->urlMediaPublica(),
                'enlace_externo' => $post->enlace_externo,
                'estado' => $post->estado?->value ?? $post->estado,
                'publicado_en' => $post->publicado_en?->toIso8601String(),
                'created_at' => $post->created_at?->toIso8601String(),
                'reacciones_count' => $post->reacciones_count,
            ])
            ->withQueryString();

        return Inertia::render('Emprendedor/Publicaciones/Index', [
            'emprendedor' => [
                'id' => $emprendedor->id,
                'nombre_completo' => $emprendedor->nombreCompleto(),
            ],
            'posts' => $posts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $emprendedor = $this->emprendedorActual($request);

        abort_unless($emprendedor, 403, 'Tu cuenta no está vinculada a un emprendedor.');

        $validated = $request->validate([
            'contenido' => ['nullable', 'string', 'max:3000'],
            'imagen' => ['nullable', 'image', 'max:4096'],
            'enlace_externo' => ['nullable', 'url', 'max:500'],
            'estado' => ['required', Rule::in(['borrador', 'publicado'])],
        ]);

        if (
            blank($validated['contenido'] ?? null)
            && ! $request->hasFile('imagen')
            && blank($validated['enlace_externo'] ?? null)
        ) {
            return back()
                ->withErrors([
                    'contenido' => 'Escribe un texto, sube una imagen o agrega un enlace.',
                ])
                ->withInput();
        }

        $mediaPath = null;

        if ($request->hasFile('imagen')) {
            $mediaPath = $request
                ->file('imagen')
                ->store("emprendedores/posts/{$emprendedor->id}", 'public');
        }

        $tipo = $this->resolverTipoPost($mediaPath, $validated['enlace_externo'] ?? null);

        EmprendedorPost::create([
            'emprendedor_id' => $emprendedor->id,
            'tipo' => $tipo,
            'contenido' => $validated['contenido'] ?? null,
            'media_path' => $mediaPath,
            'enlace_externo' => $validated['enlace_externo'] ?? null,
            'estado' => $validated['estado'],
        ]);

        return redirect()
            ->route('emprendedor.publicaciones.index')
            ->with(
                'success',
                $validated['estado'] === 'publicado'
                    ? 'Publicación creada y publicada correctamente.'
                    : 'Borrador guardado correctamente.',
            );
    }

    public function update(Request $request, EmprendedorPost $post): RedirectResponse
    {
        $emprendedor = $this->emprendedorActual($request);

        abort_unless($emprendedor, 403, 'Tu cuenta no está vinculada a un emprendedor.');
        abort_unless((int) $post->emprendedor_id === (int) $emprendedor->id, 403);

        $validated = $request->validate([
            'contenido' => ['nullable', 'string', 'max:3000'],
            'imagen' => ['nullable', 'image', 'max:4096'],
            'enlace_externo' => ['nullable', 'url', 'max:500'],
            'estado' => ['required', Rule::in(['borrador', 'publicado'])],
            'quitar_imagen' => ['nullable', 'boolean'],
        ]);

        if (
            blank($validated['contenido'] ?? null)
            && ! $request->hasFile('imagen')
            && blank($validated['enlace_externo'] ?? null)
            && blank($post->media_path)
        ) {
            return back()
                ->withErrors([
                    'contenido' => 'La publicación debe tener texto, imagen o enlace.',
                ])
                ->withInput();
        }

        $mediaPath = $post->media_path;

        if ($request->boolean('quitar_imagen') && filled($mediaPath)) {
            Storage::disk('public')->delete($mediaPath);
            $mediaPath = null;
        }

        if ($request->hasFile('imagen')) {
            if (filled($mediaPath)) {
                Storage::disk('public')->delete($mediaPath);
            }

            $mediaPath = $request
                ->file('imagen')
                ->store("emprendedores/posts/{$emprendedor->id}", 'public');
        }

        $tipo = $this->resolverTipoPost($mediaPath, $validated['enlace_externo'] ?? null);

        $post->update([
            'tipo' => $tipo,
            'contenido' => $validated['contenido'] ?? null,
            'media_path' => $mediaPath,
            'enlace_externo' => $validated['enlace_externo'] ?? null,
            'estado' => $validated['estado'],
        ]);

        return redirect()
            ->route('emprendedor.publicaciones.index')
            ->with('success', 'Publicación actualizada correctamente.');
    }

    public function destroy(Request $request, EmprendedorPost $post): RedirectResponse
    {
        $emprendedor = $this->emprendedorActual($request);

        abort_unless($emprendedor, 403, 'Tu cuenta no está vinculada a un emprendedor.');
        abort_unless((int) $post->emprendedor_id === (int) $emprendedor->id, 403);

        $post->delete();

        return redirect()
            ->route('emprendedor.publicaciones.index')
            ->with('success', 'Publicación eliminada correctamente.');
    }

    private function emprendedorActual(Request $request): ?Emprendedor
    {
        return $request->user()?->emprendedor;
    }

    private function resolverTipoPost(?string $mediaPath, ?string $enlaceExterno): string
    {
        if (filled($mediaPath)) {
            return EmprendedorPostTipo::Imagen->value;
        }

        if (filled($enlaceExterno)) {
            return EmprendedorPostTipo::Video->value;
        }

        return EmprendedorPostTipo::Texto->value;
    }
}