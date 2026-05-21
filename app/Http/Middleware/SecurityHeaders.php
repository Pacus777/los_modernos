<?php

namespace App\Http\Middleware;

use App\Support\SecurityContentPolicy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras HTTP de endurecimiento (S3-07): CSP y aliadas.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (! config('security.csp_enabled', true)) {
            return $response;
        }

        $policy = SecurityContentPolicy::build(
            SecurityContentPolicy::shouldUseViteDevPolicy(),
        );

        $header = config('security.csp_report_only', false)
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        $response->headers->set($header, $policy);

        return $response;
    }
}
