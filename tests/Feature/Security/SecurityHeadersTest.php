<?php

namespace Tests\Feature\Security;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_aplica_csp_y_cabeceras(): void
    {
        config([
            'security.csp_enabled' => true,
            'security.csp_report_only' => false,
            'security.vite_dev_origin' => 'http://127.0.0.1:5173',
        ]);

        $middleware = new SecurityHeaders;
        $request = Request::create('/ejemplo', 'GET');

        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString('fonts.bunny.net', $csp);
    }

    public function test_middleware_omite_csp_si_esta_desactivado(): void
    {
        config(['security.csp_enabled' => false]);

        $middleware = new SecurityHeaders;
        $request = Request::create('/ejemplo', 'GET');

        $response = $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Content-Security-Policy'));
    }

    public function test_landing_publica_incluye_cabeceras_con_bd(): void
    {
        config(['security.csp_enabled' => true]);

        $response = $this->get('/');

        $response->assertOk();
        $this->assertTrue(
            $response->headers->has('Content-Security-Policy')
            || $response->headers->has('Content-Security-Policy-Report-Only'),
        );
    }
}
