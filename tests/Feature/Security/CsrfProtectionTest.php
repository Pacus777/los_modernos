<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_libelula_no_exige_csrf(): void
    {
        config(['wayna.webhook_libelula_secret' => null]);

        $response = $this->postJson('/webhooks/libelula', [
            'event' => 'test',
        ]);

        $this->assertNotSame(419, $response->status());
    }

    public function test_chat_store_usa_grupo_web_con_csrf(): void
    {
        $route = app('router')->getRoutes()->getByName('chat.store');

        $this->assertNotNull($route);
        $this->assertContains('web', $route->middleware());
    }

    public function test_excepcion_csrf_solo_incluye_webhooks(): void
    {
        $this->assertSame(
            ['webhooks/*'],
            $this->csrfExcepcionesDesdeBootstrap(),
        );
    }

    /**
     * @return list<string>
     */
    private function csrfExcepcionesDesdeBootstrap(): array
    {
        $contenido = file_get_contents(base_path('bootstrap/app.php'));

        preg_match('/validateCsrfTokens\(except:\s*\[(.*?)\]\)/s', (string) $contenido, $coincidencias);

        $this->assertNotEmpty($coincidencias[1] ?? null);

        preg_match_all("/['\"]([^'\"]+)['\"]/", $coincidencias[1], $rutas);

        return $rutas[1] ?? [];
    }
}
