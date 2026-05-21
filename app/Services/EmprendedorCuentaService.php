<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Mail\EmprendedorCuentaCredencialesMail;
use App\Models\Emprendedor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class EmprendedorCuentaService
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {
    }

    /**
     * Crea usuario, asigna rol emprendedor, vincula al registro y envía credenciales por correo.
     *
     * @return array{user: User, password: string, correo_enviado: bool}
     */
    public function crearCuenta(
        Emprendedor $emprendedor,
        string $email,
        ?string $nombre = null,
        ?User $admin = null,
    ): array {
        if ($this->tieneCuentaActiva($emprendedor)) {
            throw new RuntimeException('Este emprendedor ya tiene una cuenta de acceso vinculada.');
        }

        $passwordPlano = Str::password(12);

        $resultado = DB::transaction(function () use ($emprendedor, $email, $nombre, $passwordPlano) {
            $user = User::query()->create([
                'name' => $nombre ?: $emprendedor->nombreCompleto(),
                'email' => strtolower(trim($email)),
                'password' => Hash::make($passwordPlano),
                'email_verified_at' => now(),
            ]);

            $this->asignarRolEmprendedor($user->id);

            $emprendedor->forceFill(['user_id' => $user->id])->save();

            return [
                'user' => $user->fresh(),
                'password' => $passwordPlano,
            ];
        });

        $correoEnviado = $this->enviarCredenciales(
            $resultado['user'],
            $emprendedor,
            $resultado['password'],
            esReenvio: false,
        );

        $this->auditLogService->registrar(
            AuditAction::AdminEntrepreneurAccountCreated,
            subject: $emprendedor->fresh(),
            actor: $admin,
            metadata: [
                'user_id' => $resultado['user']->id,
                'email' => $resultado['user']->email,
                'correo_enviado' => $correoEnviado,
            ],
        );

        return [
            'user' => $resultado['user'],
            'password' => $resultado['password'],
            'correo_enviado' => $correoEnviado,
        ];
    }

    /**
     * Genera una nueva contraseña temporal y la reenvía por correo.
     *
     * @return array{user: User, password: string, correo_enviado: bool}
     */
    public function reenviarCredenciales(Emprendedor $emprendedor, ?User $admin = null): array
    {
        $user = $emprendedor->user;

        if (! $user) {
            throw new RuntimeException('Este emprendedor no tiene cuenta vinculada.');
        }

        $passwordPlano = Str::password(12);

        $user->forceFill([
            'password' => Hash::make($passwordPlano),
        ])->save();

        $correoEnviado = $this->enviarCredenciales($user, $emprendedor, $passwordPlano, esReenvio: true);

        $this->auditLogService->registrar(
            AuditAction::AdminEntrepreneurCredentialsResent,
            subject: $emprendedor,
            actor: $admin,
            metadata: [
                'user_id' => $user->id,
                'email' => $user->email,
                'correo_enviado' => $correoEnviado,
            ],
        );

        return [
            'user' => $user,
            'password' => $passwordPlano,
            'correo_enviado' => $correoEnviado,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resumenCuenta(Emprendedor $emprendedor): array
    {
        $user = $emprendedor->user;

        return [
            'tiene_cuenta' => $this->tieneCuentaActiva($emprendedor),
            'user_id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'email_verified_at' => $user?->email_verified_at?->toIso8601String(),
            'created_at' => $user?->created_at?->toIso8601String(),
        ];
    }

    public function tieneCuentaActiva(Emprendedor $emprendedor): bool
    {
        return filled($emprendedor->user_id)
            && $emprendedor->user !== null;
    }

    private function enviarCredenciales(
        User $user,
        Emprendedor $emprendedor,
        string $passwordPlano,
        bool $esReenvio,
    ): bool {
        if (! config('wayna.emprendedor_cuenta.enviar_correo', true)) {
            return false;
        }

        if (! $this->smtpConfigurado()) {
            report(new RuntimeException(
                'SMTP no configurado: completá MAIL_USERNAME y MAIL_PASSWORD en .env',
            ));

            return false;
        }

        try {
            Mail::to($user->email)->send(new EmprendedorCuentaCredencialesMail(
                usuario: $user,
                emprendedor: $emprendedor,
                passwordPlano: $passwordPlano,
                esReenvio: $esReenvio,
            ));

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function asignarRolEmprendedor(int $userId): void
    {
        $roleId = $this->obtenerRolEmprendedorId();

        if (! $roleId) {
            throw new RuntimeException('No existe el rol «emprendedor» en la base de datos.');
        }

        $payload = ['role_id' => $roleId];

        if (Schema::hasColumn('user_roles', 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn('user_roles', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $userId],
            $payload,
        );
    }

    private function obtenerRolEmprendedorId(): ?int
    {
        return DB::table('roles')
            ->where('nombre', 'emprendedor')
            ->value('id');
    }

    private function smtpConfigurado(): bool
    {
        return filled(config('mail.mailers.smtp.username'))
            && filled(config('mail.mailers.smtp.password'));
    }
}
