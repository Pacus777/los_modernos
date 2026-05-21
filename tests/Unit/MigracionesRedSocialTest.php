<?php

namespace Tests\Unit;

use App\Enums\EmprendedorPostEstado;
use App\Enums\EmprendedorPostReaccionTipo;
use App\Enums\EmprendedorPostTipo;
use App\Models\Emprendedor;
use App\Models\EmprendedorPost;
use App\Models\EmprendedorPostReaccion;
use App\Models\EmprendedorSeguidor;
use App\Models\Visitante;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigracionesRedSocialTest extends TestCase
{
    use RefreshDatabase;

    public function test_tablas_red_social_existen(): void
    {
        $this->assertTrue(Schema::hasTable('emprendedor_posts'));
        $this->assertTrue(Schema::hasTable('emprendedor_post_reacciones'));
        $this->assertTrue(Schema::hasTable('emprendedor_seguidores'));
    }

    public function test_crea_post_publicado_con_relacion(): void
    {
        $emprendedor = $this->crearEmprendedor();

        $post = EmprendedorPost::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'tipo' => EmprendedorPostTipo::Imagen,
            'contenido' => 'Nueva producción en el mercado',
            'media_path' => 'emprendedores/posts/demo.webp',
            'estado' => EmprendedorPostEstado::Publicado,
            'publicado_en' => now(),
        ]);

        $this->assertSame($emprendedor->id, $post->emprendedor->id);
        $this->assertCount(1, $emprendedor->fresh()->posts);
    }

    public function test_reaccion_unica_por_visitante_y_post(): void
    {
        $post = $this->crearPost();
        $visitante = $this->crearVisitante();

        EmprendedorPostReaccion::query()->create([
            'emprendedor_post_id' => $post->id,
            'actor_type' => $visitante->getMorphClass(),
            'actor_id' => $visitante->id,
            'tipo' => EmprendedorPostReaccionTipo::MeGusta,
        ]);

        $this->expectException(QueryException::class);

        EmprendedorPostReaccion::query()->create([
            'emprendedor_post_id' => $post->id,
            'actor_type' => $visitante->getMorphClass(),
            'actor_id' => $visitante->id,
            'tipo' => EmprendedorPostReaccionTipo::Apoyo,
        ]);
    }

    public function test_seguimiento_unico_visitante_emprendedor(): void
    {
        $emprendedor = $this->crearEmprendedor();
        $visitante = $this->crearVisitante();

        $visitante->emprendedoresSeguidos()->attach($emprendedor->id);

        $this->assertCount(1, $emprendedor->fresh()->seguidoresVisitantes);
        $this->assertCount(1, $visitante->fresh()->emprendedoresSeguidos);

        $this->expectException(QueryException::class);

        EmprendedorSeguidor::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'visitante_id' => $visitante->id,
        ]);
    }

    private function crearEmprendedor(): Emprendedor
    {
        return Emprendedor::query()->create([
            'nombre' => 'María',
            'apellidos' => 'Red Social',
            'descripcion' => 'Prueba S4-01',
            'estado' => 'activo',
            'meta_monto' => 100,
        ]);
    }

    private function crearVisitante(): Visitante
    {
        return Visitante::query()->create([
            'codigo' => 'VIS-'.uniqid(),
            'idioma' => 'es',
        ]);
    }

    private function crearPost(): EmprendedorPost
    {
        return EmprendedorPost::query()->create([
            'emprendedor_id' => $this->crearEmprendedor()->id,
            'tipo' => EmprendedorPostTipo::Texto,
            'contenido' => 'Hola WAYNA',
            'estado' => EmprendedorPostEstado::Publicado,
            'publicado_en' => now(),
        ]);
    }
}
