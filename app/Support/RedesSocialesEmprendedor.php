<?php

namespace App\Support;

/**
 * Normaliza enlaces públicos de contacto del emprendedor.
 */
class RedesSocialesEmprendedor
{
    /**
     * @return array{whatsapp: ?string, instagram: ?string, facebook: ?string, tiktok: ?string}
     */
    public static function paraFrontend(
        ?string $whatsapp,
        ?string $instagram,
        ?string $facebook,
        ?string $tiktok = null,
    ): array {
        return [
            'whatsapp' => self::urlWhatsApp($whatsapp),
            'instagram' => self::urlGenerica($instagram),
            'facebook' => self::urlGenerica($facebook),
            'tiktok' => self::urlGenerica($tiktok),
        ];
    }

    public static function urlWhatsApp(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (str_starts_with($valor, 'http://') || str_starts_with($valor, 'https://')) {
            return $valor;
        }

        $digitos = preg_replace('/\D+/', '', $valor);

        return $digitos !== '' ? 'https://wa.me/'.$digitos : null;
    }

    public static function urlGenerica(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (str_starts_with($valor, 'http://') || str_starts_with($valor, 'https://')) {
            return $valor;
        }

        return 'https://'.$valor;
    }
}
