<?php

namespace Tests\Feature\Turista;

use App\Enums\EmprendedorPostEstado;
use App\Enums\EmprendedorPostReaccionTipo;
use App\Enums\EmprendedorPostTipo;
use App\Models\Emprendedor;
use App\Models\EmprendedorPost;
use App\Models\EmprendedorPostReaccion;
use App\Models\Visitante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostReaccionTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_reaccion_y_devuelve_totales(): void
    {
        $post = $this->crearPostPublicado();

        $this->postJson(route('turista.posts.reaccion.store', $post->id), [
            'tipo' => 'me_gusta',
        ])
            ->assertOk()
            ->assertJson([
                'post_id' => $post->id,
                'mi_reaccion' => 'me_gusta',
                'totales' => [
                    'me_gusta' => 1,
                    'aplauso' => 0,
                    'apoyo' => 0,
                    'inspirado' => 0,
                ],
            ]);

        $this->assertSame(1, EmprendedorPostReaccion::query()->count());
    }

    public function test_cambiar_tipo_mantiene_una_sola_reaccion(): void
    {
        $post = $this->crearPostPublicado();

        $this->postJson(route('turista.posts.reaccion.store', $post->id), [
            'tipo' => 'me_gusta',
        ])->assertOk();

        $this->postJson(route('turista.posts.reaccion.store', $post->id), [
            'tipo' => 'aplauso',
        ])
            ->assertOk()
            ->assertJson([
                'mi_reaccion' => 'aplauso',
                'totales' => [
                    'me_gusta' => 0,
                    'aplauso' => 1,
                    'apoyo' => 0,
                    'inspirado' => 0,
                ],
            ]);

        $this->assertSame(1, EmprendedorPostReaccion::query()->count());
    }

    public function test_mismo_tipo_quita_reaccion(): void
    {
        $post = $this->crearPostPublicado();

        $this->postJson(route('turista.posts.reaccion.store', $post->id), [
            'tipo' => 'apoyo',
        ])->assertOk();

        $this->postJson(route('turista.posts.reaccion.store', $post->id), [
            'tipo' => 'apoyo',
        ])
            ->assertOk()
            ->assertJson([
                'mi_reaccion' => null,
                'totales' => [
                    'me_gusta' => 0,
                    'aplauso' => 0,
                    'apoyo' => 0,
                    'inspirado' => 0,
                ],
            ]);

        $this->assertSame(0, EmprendedorPostReaccion::query()->count());
    }

    public function test_rechaza_tipo_invalido(): void
    {
        $post = $this->crearPostPublicado();

        $this->postJson(route('turista.posts.reaccion.store', $post->id), [
            'tipo' => 'fuego',
        ])->assertUnprocessable();
    }

    public function test_perfil_incluye_posts_publicados(): void
    {
        $emprendedor = $this->crearEmprendedor();
        $post = EmprendedorPost::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'tipo' => EmprendedorPostTipo::Texto,
            'contenido' => 'Hola desde el muro',
            'estado' => EmprendedorPostEstado::Publicado,
            'publicado_en' => now(),
        ]);

        $this->get(route('turista.emprendedor.show', $emprendedor->id))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Turista/Perfil')
                ->has('posts', 1)
                ->where('posts.0.id', $post->id)
                ->where('posts.0.contenido', 'Hola desde el muro'));
    }

    private function crearEmprendedor(): Emprendedor
    {
        return Emprendedor::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Reacciones',
            'descripcion' => 'Test S4-07',
            'estado' => 'activo',
            'meta_monto' => 100,
        ]);
    }

    private function crearPostPublicado(): EmprendedorPost
    {
        return EmprendedorPost::query()->create([
            'emprendedor_id' => $this->crearEmprendedor()->id,
            'tipo' => EmprendedorPostTipo::Texto,
            'contenido' => 'Post de prueba',
            'estado' => EmprendedorPostEstado::Publicado,
            'publicado_en' => now(),
        ]);
    }
}
