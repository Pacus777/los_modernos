<?php

namespace Tests\Feature\Turista;

use App\Models\Emprendedor;
use App\Services\EmprendedorMediosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EmprendedorPerfilMediosTest extends TestCase
{
    use RefreshDatabase;

    public function test_perfil_publico_expone_medios_y_foto_portada(): void
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'López',
            'descripcion' => 'Artesanías textiles hechas a mano en Santa Cruz.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'santa_cruz',
            'fotografia' => 'emprendedores/fotografias/perfil.webp',
            'foto_empresa' => 'emprendedores/empresa/local.webp',
            'galeria' => [
                'emprendedores/galeria/uno.webp',
                'emprendedores/galeria/dos.webp',
            ],
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'estado' => 'activo',
            'meta_monto' => 1000,
        ]);

        $response = $this->get(route('turista.emprendedores.show', $emprendedor->slug));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Turista/Perfil')
            ->where('emprendedor.foto_portada', '/storage/emprendedores/fotografias/perfil.webp')
            ->where('medios.foto_empresa', '/storage/emprendedores/empresa/local.webp')
            ->has('medios.galeria', 2)
            ->where('medios.video.tipo', 'embed')
            ->where(
                'medios.video.embed_url',
                'https://www.youtube.com/embed/dQw4w9WgXcQ',
            ));
    }

    public function test_embed_youtube_desde_enlace(): void
    {
        $embed = EmprendedorMediosService::embedDesdeEnlace(
            'https://youtu.be/abcdefghijk',
        );

        $this->assertSame('https://www.youtube.com/embed/abcdefghijk', $embed);
    }
}
