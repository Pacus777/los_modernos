<?php

namespace App\DataTransferObjects;

readonly class LibelulaDeudaRespuesta
{
    public function __construct(
        public string $idTransaccion,
        public string $urlPasarela,
        public ?string $qrSimpleUrl = null,
        public ?string $mensaje = null,
        public bool $simulada = false,
    ) {}
}
