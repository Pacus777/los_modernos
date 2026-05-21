<?php

namespace App\Exceptions;

use RuntimeException;

class LibelulaApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>|null  $respuesta
     */
    public function __construct(
        string $message,
        public readonly ?array $respuesta = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
