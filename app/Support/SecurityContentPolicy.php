<?php

namespace App\Support;

/**
 * Construye la directiva Content-Security-Policy para WAYNA (S3-07).
 */
class SecurityContentPolicy
{
    public static function build(bool $viteDev = false): string
    {
        $scriptSrc = ["'self'"];
        $connectSrc = ["'self'"];

        if ($viteDev) {
            $origin = rtrim((string) config('security.vite_dev_origin', 'http://127.0.0.1:5173'), '/');
            $wsOrigin = preg_replace('#^http#', 'ws', $origin);

            $scriptSrc[] = $origin;
            $scriptSrc[] = "'unsafe-inline'";
            $scriptSrc[] = "'unsafe-eval'";
            $connectSrc[] = $origin;

            if (is_string($wsOrigin) && $wsOrigin !== '') {
                $connectSrc[] = $wsOrigin;
            }
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            'script-src '.implode(' ', $scriptSrc),
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net data:",
            "img-src 'self' data: blob: https:",
            'connect-src '.implode(' ', $connectSrc),
            'frame-src '.implode(' ', [
                "'self'",
                'https://www.youtube.com',
                'https://www.youtube-nocookie.com',
            ]),
        ];

        return implode('; ', $directives);
    }

    public static function shouldUseViteDevPolicy(): bool
    {
        if (! app()->environment(['local', 'testing'])) {
            return false;
        }

        return (bool) config('app.debug');
    }
}
