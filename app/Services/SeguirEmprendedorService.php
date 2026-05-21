<?php

namespace App\Services;

use App\Mail\ConfirmarSeguimientoEmprendedorMail;
use App\Models\Emprendedor;
use App\Models\EmprendedorSeguidor;
use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Seguir emprendedor por email sin cuenta (S4-06).
 */
class SeguirEmprendedorService
{
    private const SESSION_EMAIL = 'seguimiento_email';

    public function estadoEnPerfil(Request $request, int $emprendedorId): array
    {
        $registro = $this->buscarRegistroSesion($request, $emprendedorId);

        if (! $registro) {
            return ['estado' => 'ninguno', 'email' => null];
        }

        if ($registro->estaConfirmado()) {
            return [
                'estado' => 'confirmado',
                'email' => $this->enmascararEmail($registro->email),
            ];
        }

        return [
            'estado' => 'pendiente',
            'email' => $this->enmascararEmail($registro->email),
        ];
    }

    public function solicitar(Request $request, Emprendedor $emprendedor, string $email): void
    {
        $email = strtolower(trim($email));

        $existente = EmprendedorSeguidor::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->where('email', $email)
            ->first();

        if ($existente?->estaConfirmado()) {
            throw ValidationException::withMessages([
                'email' => __('seguimiento.ya_confirmado'),
            ]);
        }

        $visitanteId = $this->resolverVisitanteId($request);

        if ($existente) {
            $existente->update([
                'visitante_id' => $visitanteId,
                'token_confirm' => EmprendedorSeguidor::generarToken(),
                'token_unsub' => $existente->token_unsub ?? EmprendedorSeguidor::generarToken(),
                'confirmado_en' => null,
            ]);
            $registro = $existente->fresh();
        } else {
            $registro = EmprendedorSeguidor::query()->create([
                'emprendedor_id' => $emprendedor->id,
                'visitante_id' => $visitanteId,
                'email' => $email,
                'token_confirm' => EmprendedorSeguidor::generarToken(),
                'token_unsub' => EmprendedorSeguidor::generarToken(),
            ]);
        }

        $request->session()->put($this->claveSesion($emprendedor->id), $email);

        Mail::to($email)->send(new ConfirmarSeguimientoEmprendedorMail(
            $emprendedor,
            $registro,
        ));
    }

    public function confirmar(string $token): EmprendedorSeguidor
    {
        $registro = EmprendedorSeguidor::query()
            ->where('token_confirm', $token)
            ->with('emprendedor')
            ->first();

        if (! $registro) {
            throw ValidationException::withMessages([
                'token' => __('seguimiento.token_invalido'),
            ]);
        }

        $registro->update([
            'confirmado_en' => now(),
            'token_confirm' => null,
        ]);

        $registro = $registro->fresh(['emprendedor']);

        if ($registro->email) {
            session()->put($this->claveSesion($registro->emprendedor_id), $registro->email);
        }

        return $registro;
    }

    public function darDeBaja(string $tokenUnsub): Emprendedor
    {
        $registro = EmprendedorSeguidor::query()
            ->where('token_unsub', $tokenUnsub)
            ->with('emprendedor')
            ->first();

        if (! $registro) {
            throw ValidationException::withMessages([
                'token' => __('seguimiento.token_invalido'),
            ]);
        }

        $emprendedor = $registro->emprendedor;
        $registro->delete();

        return $emprendedor;
    }

    private function buscarRegistroSesion(Request $request, int $emprendedorId): ?EmprendedorSeguidor
    {
        $email = $request->session()->get($this->claveSesion($emprendedorId));

        if (is_string($email) && $email !== '') {
            return EmprendedorSeguidor::query()
                ->where('emprendedor_id', $emprendedorId)
                ->where('email', $email)
                ->first();
        }

        $visitanteId = $request->session()->get('visitante_id');

        if ($visitanteId) {
            return EmprendedorSeguidor::query()
                ->where('emprendedor_id', $emprendedorId)
                ->where('visitante_id', $visitanteId)
                ->whereNotNull('email')
                ->first();
        }

        return null;
    }

    private function resolverVisitanteId(Request $request): int
    {
        $visitanteId = $request->session()->get('visitante_id');

        if ($visitanteId && Visitante::query()->whereKey($visitanteId)->exists()) {
            return (int) $visitanteId;
        }

        $visitante = Visitante::query()->create([
            'codigo' => 'VIS-'.strtoupper(Str::random(8)),
            'nombre' => null,
            'idioma' => in_array($request->session()->get('locale'), ['es', 'en'], true)
                ? $request->session()->get('locale')
                : 'es',
            'session_id' => $request->session()->getId(),
        ]);

        $request->session()->put('visitante_id', $visitante->id);

        return $visitante->id;
    }

    private function claveSesion(int $emprendedorId): string
    {
        return self::SESSION_EMAIL.'_'.$emprendedorId;
    }

    private function enmascararEmail(?string $email): ?string
    {
        if (! filled($email) || ! str_contains($email, '@')) {
            return $email;
        }

        [$usuario, $dominio] = explode('@', $email, 2);
        $visible = substr($usuario, 0, min(2, strlen($usuario)));

        return $visible.'***@'.$dominio;
    }
}
