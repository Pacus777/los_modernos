<?php

namespace Tests\Feature\Turista;

use App\Models\Emprendedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExplorarEmprendedoresTest extends TestCase
{
    use RefreshDatabase;

    private function crearEmprendedorActivo(array $atributos = []): Emprendedor
    {
        return Emprendedor::query()->create(array_merge([
            'nombre' => 'Ana',
            'apellidos' => 'Turista',
            'descripcion' => 'Historia de prueba para el feed turista Wayna.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 1500,
        ], $atributos));
    }

    public function test_home_muestra_emprendedores_activos(): void
    {
        $activo = $this->crearEmprendedorActivo([
            'nombre' => 'Ana',
            'apellidos' => 'Visible',
        ]);

        $this->crearEmprendedorActivo([
            'nombre' => 'Oculto',
            'estado' => 'inactivo',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Landing')
            ->has('emprendedores', 1)
            ->where('emprendedores.0.id', $activo->id)
            ->where('emprendedores.0.nombre', 'Ana'));
    }

    public function test_filtro_busqueda_en_home(): void
    {
        $this->crearEmprendedorActivo([
            'nombre' => 'Karandai',
            'descripcion' => 'Pastelería artesanal del mercado',
        ]);

        $this->crearEmprendedorActivo([
            'nombre' => 'Otro',
            'descripcion' => 'Textiles andinos',
        ]);

        $response = $this->get('/?q=karandai');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('emprendedores', 1)
            ->where('emprendedores.0.nombre', 'Karandai'));
    }
}
