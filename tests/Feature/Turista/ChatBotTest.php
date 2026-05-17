<?php

namespace Tests\Feature\Turista;

use App\Models\Campana;
use App\Models\Emprendedor;
use App\Services\RagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatBotTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_redirige_a_emprendedor_jaime_para_donar(): void
    {
        $jaime = Emprendedor::query()->create([
            'nombre' => 'Jaime',
            'apellidos' => 'Rojas',
            'descripcion' => 'Tejidos andinos.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 500,
        ]);

        $response = $this->from('/')
            ->post(route('chat.store'), [
                'pregunta' => 'Vi el producto de Jaime, quiero donarle',
                'idioma' => 'es',
            ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('rag');

        $rag = session('rag');

        $this->assertSame('emprendedor:'.$jaime->id, $rag['matched_id']);
        $this->assertSame('emprendedor_unico', $rag['intent']);
        $this->assertNotEmpty($rag['actions']);
        $this->assertStringContainsString('Jaime', $rag['answer']);

        $hrefs = collect($rag['actions'])->pluck('href')->all();
        $this->assertContains(route('turista.emprendedor.show', $jaime->id), $hrefs);
    }

    public function test_chat_con_contexto_de_perfil_responde_sin_nombre(): void
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'María',
            'apellidos' => 'Choque',
            'descripcion' => 'Cerámica.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'cochabamba',
            'estado' => 'activo',
            'meta_monto' => 300,
        ]);

        $response = $this->from(route('turista.emprendedor.show', $emprendedor))
            ->post(route('chat.store'), [
                'pregunta' => 'Quiero donar ahora',
                'idioma' => 'es',
                'context_emprendedor_id' => $emprendedor->id,
            ]);

        $rag = session('rag');

        $this->assertSame('emprendedor:'.$emprendedor->id, $rag['matched_id']);
        $this->assertSame('contexto_perfil', $rag['intent']);
    }

    public function test_chat_resuelve_campana_por_titulo(): void
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Lucía',
            'apellidos' => 'Vega',
            'descripcion' => 'Miel orgánica.',
            'tipo_emprendimiento' => 'gastronomia',
            'departamento' => 'santa_cruz',
            'estado' => 'activo',
            'meta_monto' => 800,
        ]);

        Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Apoyo a colmenas Wayna',
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'estado' => 'activa',
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addMonth(),
        ]);

        $rag = app(RagService::class)->responder(
            'Quiero apoyar la campaña Apoyo a colmenas Wayna',
            'es',
        );

        $this->assertSame('redirigir_campana', $rag['intent']);
        $this->assertStringContainsString('colmenas', $rag['answer']);
    }

    public function test_chat_responde_json_sin_redireccion(): void
    {
        $response = $this->postJson(route('chat.store'), [
            'pregunta' => '¿Cómo puedo donar?',
            'idioma' => 'es',
        ]);

        $response->assertOk();
        $response->assertJsonPath('rag.intent', 'como_donar');
        $response->assertJsonStructure(['rag' => ['answer', 'intent', 'actions']]);
    }

    public function test_pregunta_general_sigue_usando_base_conocimiento(): void
    {
        $rag = app(RagService::class)->responder('¿Cómo puedo donar?', 'es');

        $this->assertSame('como_donar', $rag['intent']);
        $this->assertSame('proceso_donacion', $rag['category']);
    }
}
