<?php

namespace App\Services;

use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use PragmaRX\Google2FA\Google2FA;

/**
 * 2FA TOTP para administradores (S3-04, pragmarx/google2fa).
 */
class AdminTwoFactorService
{
    public function __construct(
        protected Google2FA $google2fa,
    ) {}

    public function generarSecreto(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function urlOtpAuth(User $user, string $secreto): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('wayna.two_factor.issuer', 'WAYNA Admin'),
            $user->email,
            $secreto,
        );
    }

    public function qrComoDataUri(string $contenidoQr): string
    {
        $resultado = (new Builder(
            writer: new PngWriter(),
            data: $contenidoQr,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 220,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        return 'data:image/png;base64,'.base64_encode($resultado->getString());
    }

    public function codigoValido(?string $secreto, string $codigo): bool
    {
        if (! filled($secreto)) {
            return false;
        }

        $codigo = preg_replace('/\s+/', '', $codigo) ?? '';

        if (! preg_match('/^\d{6}$/', $codigo)) {
            return false;
        }

        return $this->google2fa->verifyKey(
            $secreto,
            $codigo,
            config('wayna.two_factor.window', 1),
        );
    }

    public function codigoValidoParaUsuario(User $user, string $codigo): bool
    {
        return $this->codigoValido($user->google2fa_secret, $codigo);
    }

    public function activar(User $user, string $secretoPlano): void
    {
        $user->forceFill([
            'google2fa_secret' => $secretoPlano,
            'google2fa_confirmed_at' => now(),
        ])->save();
    }

    public function desactivar(User $user): void
    {
        $user->forceFill([
            'google2fa_secret' => null,
            'google2fa_confirmed_at' => null,
        ])->save();
    }

    /**
     * Solo para tests: OTP actual de un secreto en claro.
     */
    public function codigoActualParaSecreto(string $secretoPlano): string
    {
        return $this->google2fa->getCurrentOtp($secretoPlano);
    }
}
