<?php

namespace Tests\Feature\Turista;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\EmprendedorPost;
use App\Enums\EmprendedorPostEstado;
use App\Enums\EmprendedorPostTipo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmprendedorFeedControllerTest extends TestCase
{
    use RefreshDatabase;

    private function crearEmprendedorActivo(array $atributos = []): Emprendedor
    {
        return Emprendedor::query()->create(array_merge([
            'nombre' => 'María',
            'apellidos' => 'Gómez',
            'descripcion' => 'Artesanías del altiplano.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 2000,
        ], $atributos));
    }

    public function test_feed_lista_solo_emprendedores_activos_con_paginacion(): void
    {
        $activo = $this->crearEmprendedorActivo(['nombre' => 'Activo']);
        $inactivo = $this->crearEmprendedorActivo(['nombre' => 'Inactivo', 'estado' => 'inactivos']); // o 'inactivo'

        $response = $this->get('/emprendedores');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Turista/Emprendedores/Index')
            ->has('emprendedores.data')
            ->where('emprendedores.total', 1)
            ->where('emprendedores.data.0.id', $activo->id)
        );
    }

    public function test_feed_filtro_por_busqueda_q(): void
    {
        $this->crearEmprendedorActivo(['nombre' => 'Carlos', 'descripcion' => 'Tejidos de lana']);
        $this->crearEmprendedorActivo(['nombre' => 'Luis', 'descripcion' => 'Artesanías en madera']);

        $response = $this->get('/emprendedores?q=tejidos');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('emprendedores.total', 1)
            ->where('emprendedores.data.0.nombre', 'Carlos')
        );
    }

    public function test_feed_filtro_por_departamento_y_tipo(): void
    {
        $this->crearEmprendedorActivo(['departamento' => 'la_paz', 'tipo_emprendimiento' => 'artesania', 'nombre' => 'LaPazArtesania']);
        $this->crearEmprendedorActivo(['departamento' => 'oruro', 'tipo_emprendimiento' => 'turismo', 'nombre' => 'OruroTurismo']);

        $response = $this->get('/emprendedores?departamento=la_paz&tipo_emprendimiento=artesania');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('emprendedores.total', 1)
            ->where('emprendedores.data.0.nombre', 'LaPazArtesania')
        );
    }

    public function test_feed_ordenamiento_por_recientes(): void
    {
        $primero = $this->crearEmprendedorActivo(['created_at' => now()->subDays(5)]);
        $segundo = $this->crearEmprendedorActivo(['created_at' => now()]);

        $response = $this->get('/emprendedores?orden=recientes');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('emprendedores.data.0.id', $segundo->id)
            ->where('emprendedores.data.1.id', $primero->id)
        );
    }

    public function test_feed_ordenamiento_por_mas_publicaciones(): void
    {
        $emp1 = $this->crearEmprendedorActivo(['nombre' => 'Pocos']);
        $emp2 = $this->crearEmprendedorActivo(['nombre' => 'Muchos']);

        // Crear posts para emp2 (estado publicado)
        EmprendedorPost::query()->create([
            'emprendedor_id' => $emp2->id,
            'tipo' => EmprendedorPostTipo::Texto,
            'contenido' => 'Post 1',
            'estado' => EmprendedorPostEstado::Publicado,
            'publicado_en' => now(),
        ]);
        EmprendedorPost::query()->create([
            'emprendedor_id' => $emp2->id,
            'tipo' => EmprendedorPostTipo::Texto,
            'contenido' => 'Post 2',
            'estado' => EmprendedorPostEstado::Publicado,
            'publicado_en' => now(),
        ]);

        // Post borrador para emp1
        EmprendedorPost::query()->create([
            'emprendedor_id' => $emp1->id,
            'tipo' => EmprendedorPostTipo::Texto,
            'contenido' => 'Borrador 1',
            'estado' => EmprendedorPostEstado::Borrador,
            'publicado_en' => null,
        ]);

        $response = $this->get('/emprendedores?orden=mas_publicaciones');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('emprendedores.data.0.id', $emp2->id)
            ->where('emprendedores.data.0.publicaciones_count', 2)
            ->where('emprendedores.data.1.id', $emp1->id)
            ->where('emprendedores.data.1.publicaciones_count', 0)
        );
    }

    public function test_feed_ordenamiento_por_mas_apoyados(): void
    {
        $emp1 = $this->crearEmprendedorActivo(['nombre' => 'Menos']);
        $emp2 = $this->crearEmprendedorActivo(['nombre' => 'Mas']);

        // Campañas
        $camp1 = Campana::query()->create([
            'emprendedor_id' => $emp1->id,
            'titulo' => 'Campaña 1',
            'fecha_inicio' => now()->subDays(1),
            'fecha_fin' => now()->addDays(5),
            'meta_apoyo' => 1000,
            'monto_recaudado' => 100,
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $camp2 = Campana::query()->create([
            'emprendedor_id' => $emp2->id,
            'titulo' => 'Campaña 2',
            'fecha_inicio' => now()->subDays(1),
            'fecha_fin' => now()->addDays(5),
            'meta_apoyo' => 2000,
            'monto_recaudado' => 900,
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $response = $this->get('/emprendedores?orden=mas_apoyados');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('emprendedores.data.0.id', $emp2->id)
            ->where('emprendedores.data.1.id', $emp1->id)
        );
    }

    public function test_feed_ordenamiento_por_cerca_meta(): void
    {
        $emp1 = $this->crearEmprendedorActivo(['nombre' => 'Cerca']); // 90% (900/1000)
        $emp2 = $this->crearEmprendedorActivo(['nombre' => 'Lejos']); // 10% (200/2000)

        // Campaña emp1
        Campana::query()->create([
            'emprendedor_id' => $emp1->id,
            'titulo' => 'Campaña Cerca',
            'fecha_inicio' => now()->subDays(1),
            'fecha_fin' => now()->addDays(5),
            'meta_apoyo' => 1000,
            'monto_recaudado' => 900,
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        // Campaña emp2
        Campana::query()->create([
            'emprendedor_id' => $emp2->id,
            'titulo' => 'Campaña Lejos',
            'fecha_inicio' => now()->subDays(1),
            'fecha_fin' => now()->addDays(5),
            'meta_apoyo' => 2000,
            'monto_recaudado' => 200,
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $response = $this->get('/emprendedores?orden=cerca_meta');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('emprendedores.data.0.id', $emp1->id)
            ->where('emprendedores.data.1.id', $emp2->id)
        );
    }
}
