<?php

namespace Tests\Unit;

use App\Enums\EmprendedorPostEstado;
use App\Enums\EmprendedorPostTipo;
use App\Events\CampanaMetaAlcanzada;
use App\Events\EmprendedorPostPublicado;
use App\Models\Campana;
use App\Models\Emprendedor;
use App\Models\EmprendedorPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class S4ObserversRedSocialTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_publicado_asigna_fecha_y_dispara_evento(): void
    {
        Event::fake([EmprendedorPostPublicado::class]);

        $emprendedor = $this->crearEmprendedor();

        $post = EmprendedorPost::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'tipo' => EmprendedorPostTipo::Texto,
            'contenido' => 'Hola feed',
            'estado' => EmprendedorPostEstado::Publicado,
        ]);

        $this->assertNotNull($post->fresh()->publicado_en);

        Event::assertDispatched(EmprendedorPostPublicado::class, function ($event) use ($post) {
            return $event->post->id === $post->id;
        });
    }

    public function test_campana_dispara_evento_al_alcanzar_meta(): void
    {
        Event::fake([CampanaMetaAlcanzada::class]);

        $campana = $this->crearCampana(metaApoyo: 100, recaudado: 80);

        $campana->update(['monto_recaudado' => 100]);

        Event::assertDispatched(CampanaMetaAlcanzada::class, function ($event) use ($campana) {
            return $event->campana->id === $campana->id;
        });
    }

    public function test_scope_publicados_solo_lista_posts_visibles(): void
    {
        $emprendedor = $this->crearEmprendedor();

        EmprendedorPost::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'tipo' => EmprendedorPostTipo::Texto,
            'contenido' => 'Borrador',
            'estado' => EmprendedorPostEstado::Borrador,
        ]);

        EmprendedorPost::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'tipo' => EmprendedorPostTipo::Texto,
            'contenido' => 'Visible',
            'estado' => EmprendedorPostEstado::Publicado,
        ]);

        $this->assertSame(1, EmprendedorPost::query()->publicados()->count());
    }

    private function crearEmprendedor(): Emprendedor
    {
        return Emprendedor::query()->create([
            'nombre' => 'Test',
            'apellidos' => 'S4-02',
            'descripcion' => 'Observer',
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);
    }

    private function crearCampana(float $metaApoyo, float $recaudado): Campana
    {
        $emprendedor = $this->crearEmprendedor();

        return Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Meta test',
            'meta_apoyo' => $metaApoyo,
            'monto_recaudado' => $recaudado,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);
    }
}
