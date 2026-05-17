<?php

namespace App\Support;

/**
 * Normaliza enlaces públicos de contacto del emprendedor.
 */
class RedesSocialesEmprendedor
{
    public const WHATSAPP_CODIGO_PAIS = '591';

    public const WHATSAPP_DIGITOS_LOCAL = 8;

    /**
     * @return array{whatsapp: ?string, instagram: ?string, facebook: ?string, tiktok: ?string, sitio_web: ?string}
     */
    public static function paraFrontend(
        ?string $whatsapp,
        ?string $instagram,
        ?string $facebook,
        ?string $tiktok = null,
        ?string $sitioWeb = null,
    ): array {
        return [
            'whatsapp' => self::urlWhatsApp($whatsapp),
            'instagram' => self::urlGenerica($instagram),
            'facebook' => self::urlGenerica($facebook),
            'tiktok' => self::urlGenerica($tiktok),
            'sitio_web' => self::urlGenerica($sitioWeb),
        ];
    }

    /**
     * Normaliza a 591 + 8 dígitos (celular boliviano) para guardar en BD.
     */
    public static function normalizarWhatsAppGuardado(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (preg_match('#^https?://(?:wa\.me|api\.whatsapp\.com)/(\d+)#i', $valor, $coincidencias)) {
            $valor = $coincidencias[1];
        }

        $digitos = preg_replace('/\D+/', '', $valor) ?? '';

        if ($digitos === '') {
            return null;
        }

        if (strlen($digitos) === 9 && str_starts_with($digitos, '0')) {
            $digitos = substr($digitos, 1);
        }

        if (strlen($digitos) === self::WHATSAPP_DIGITOS_LOCAL) {
            return self::WHATSAPP_CODIGO_PAIS.$digitos;
        }

        if (
            strlen($digitos) === self::WHATSAPP_DIGITOS_LOCAL + strlen(self::WHATSAPP_CODIGO_PAIS)
            && str_starts_with($digitos, self::WHATSAPP_CODIGO_PAIS)
        ) {
            return $digitos;
        }

        return $digitos;
    }

    public static function esCelularBoliviaLocal(string $digitos): bool
    {
        return (bool) preg_match('/^[67]\d{7}$/', $digitos);
    }

    public static function esWhatsAppBoliviaValido(?string $valor): bool
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return true;
        }

        if (
            preg_match('#^https?://#i', $valor)
            && ! preg_match('#^https?://(?:wa\.me|api\.whatsapp\.com)/\d+#i', $valor)
        ) {
            return false;
        }

        $normalizado = self::normalizarWhatsAppGuardado($valor);

        if ($normalizado === null) {
            return false;
        }

        $longitudEsperada = strlen(self::WHATSAPP_CODIGO_PAIS) + self::WHATSAPP_DIGITOS_LOCAL;

        if (strlen($normalizado) !== $longitudEsperada) {
            return false;
        }

        if (! str_starts_with($normalizado, self::WHATSAPP_CODIGO_PAIS)) {
            return false;
        }

        return self::esCelularBoliviaLocal(substr($normalizado, strlen(self::WHATSAPP_CODIGO_PAIS)));
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

        $normalizado = self::normalizarWhatsAppGuardado($valor);

        if ($normalizado !== null && self::esWhatsAppBoliviaValido($valor)) {
            return 'https://wa.me/'.$normalizado;
        }

        $digitos = preg_replace('/\D+/', '', $valor) ?? '';

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
