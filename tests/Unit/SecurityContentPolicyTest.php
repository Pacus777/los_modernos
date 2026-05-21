<?php

namespace Tests\Unit;

use App\Support\SecurityContentPolicy;
use Tests\TestCase;

class SecurityContentPolicyTest extends TestCase
{
    public function test_politica_produccion_sin_vite(): void
    {
        $csp = SecurityContentPolicy::build(false);

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString('https://www.youtube.com', $csp);
        $this->assertStringNotContainsString('5173', $csp);
    }

    public function test_politica_desarrollo_incluye_vite(): void
    {
        config(['security.vite_dev_origin' => 'http://127.0.0.1:5173']);

        $csp = SecurityContentPolicy::build(true);

        $this->assertStringContainsString('127.0.0.1:5173', $csp);
        $this->assertStringContainsString('unsafe-eval', $csp);
        $this->assertStringContainsString('ws://127.0.0.1:5173', $csp);
    }
}
