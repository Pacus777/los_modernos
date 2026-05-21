<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Throwable;

class AuditLogService
{
    /**
     * Registra una acción crítica. No interrumpe el flujo principal si falla el insert.
     */
    public function registrar(
        AuditAction|string $action,
        ?Model $subject = null,
        ?User $actor = null,
        array $metadata = [],
        ?Request $request = null,
    ): ?AuditLog {
        if (! config('wayna.audit_log.enabled', true)) {
            return null;
        }

        $actionValue = $action instanceof AuditAction ? $action->value : $action;

        $request ??= RequestFacade::instance();

        try {
            return AuditLog::query()->create([
                'user_id' => $actor?->id,
                'action' => $actionValue,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'ip_address' => $request?->ip(),
                'user_agent' => $this->truncarUserAgent($request?->userAgent()),
                'metadata' => $metadata === [] ? null : $metadata,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function truncarUserAgent(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        return mb_substr($userAgent, 0, 500);
    }
}
