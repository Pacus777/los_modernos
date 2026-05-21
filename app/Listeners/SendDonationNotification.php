<?php

namespace App\Listeners;

use App\Events\DonacionConfirmada;
use App\Services\SendDonationNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendDonationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public bool $afterCommit = true;

    public function __construct(
        protected SendDonationNotificationService $notificationService,
    ) {
    }

    public function handle(DonacionConfirmada $event): void
    {
        $donacion = $event->donacion;

        // Validar que la donación sigue validada antes de proceder
        if (!$donacion->estaValidada()) {
            return;
        }

        $this->notificationService->notificar($donacion);
    }
}
