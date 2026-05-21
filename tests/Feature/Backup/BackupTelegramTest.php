<?php

namespace Tests\Feature\Backup;

use App\Listeners\NotifyTelegramOnBackupResult;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Http;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Tests\TestCase;

class BackupTelegramTest extends TestCase
{
    public function test_notifica_telegram_cuando_respaldo_es_exitoso(): void
    {
        config([
            'services.telegram.backup_alerts' => true,
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_id' => '-100123',
            'backup.backup.name' => 'WAYNA-TEST',
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true]),
        ]);

        app(NotifyTelegramOnBackupResult::class)->onSuccess(
            new BackupWasSuccessful('backups', 'WAYNA-TEST'),
        );

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.telegram.org/bottest-token/sendMessage')
                && str_contains($request['text'], 'Respaldo WAYNA completado')
                && str_contains($request['text'], 'WAYNA-TEST');
        });
    }

    public function test_notifica_telegram_cuando_respaldo_falla(): void
    {
        config([
            'services.telegram.backup_alerts' => true,
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_id' => '-100123',
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true]),
        ]);

        $event = new BackupHasFailed(new \RuntimeException('mysqldump no encontrado'));
        app(NotifyTelegramOnBackupResult::class)->onFailure($event);

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'Falló el respaldo WAYNA')
                && str_contains($request['text'], 'mysqldump');
        });
    }

    public function test_sin_alerta_si_backup_telegram_desactivado(): void
    {
        config([
            'services.telegram.backup_alerts' => false,
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_id' => '-100123',
        ]);

        Http::fake();

        app(NotifyTelegramOnBackupResult::class)->onSuccess(
            new BackupWasSuccessful('backups', 'WAYNA-TEST'),
        );

        Http::assertNothingSent();
    }

    public function test_config_usa_disco_backups(): void
    {
        $this->assertContains('backups', config('backup.backup.destination.disks'));
    }
}
