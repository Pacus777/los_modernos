<?php

namespace App\Listeners;

use App\Services\TelegramService;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;

class NotifyTelegramOnBackupResult
{
    public function __construct(
        private readonly TelegramService $telegramService,
    ) {}

    public function onSuccess(BackupWasSuccessful $event): void
    {
        $destino = implode(', ', config('backup.backup.destination.disks', ['backups']));

        $this->telegramService->notificarRespaldoExitoso(
            config('backup.backup.name', 'WAYNA'),
            $destino,
        );
    }

    public function onFailure(BackupHasFailed $event): void
    {
        $exception = $event->exception;

        $this->telegramService->notificarRespaldoFallido(
            config('backup.backup.name', 'WAYNA'),
            $exception?->getMessage() ?? 'Error desconocido en backup:run',
        );
    }
}
