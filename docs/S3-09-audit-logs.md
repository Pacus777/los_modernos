# S3-09 — Tabla `audit_logs` y acciones críticas

## Objetivo

Registrar en base de datos las acciones sensibles del sistema (autenticación, 2FA, revisión de donaciones, gestión de emprendedores) para auditoría y soporte, sin sustituir la trazabilidad de negocio en `transacciones` ni los `webhook_logs`.

## Tabla `audit_logs`

| Campo | Descripción |
|-------|-------------|
| `user_id` | Quién ejecutó la acción (nullable en login fallido) |
| `action` | Código estable (`auth.login.success`, `admin.donation.validated`, …) |
| `subject_type` / `subject_id` | Entidad afectada (morph: donación, emprendedor, …) |
| `ip_address` / `user_agent` | Contexto HTTP |
| `metadata` | JSON opcional (montos, email en fallo de login, ids masivos, etc.) |
| `created_at` | Solo creación (registro inmutable) |

## Servicio

`App\Services\AuditLogService::registrar()`:

- Respeta `WAYNA_AUDIT_LOG_ENABLED` (default `true`).
- Si el insert falla, reporta el error y **no** rompe el flujo principal.

Acciones definidas en `App\Enums\AuditAction`.

## Acciones registradas hoy

| Acción | Dónde |
|--------|--------|
| `auth.login.success` / `auth.login.failed` / `auth.logout` | Login / logout |
| `auth.two_factor.passed` | Desafío 2FA tras login admin |
| `admin.two_factor.enabled` / `disabled` | Panel seguridad admin |
| `admin.donation.validated` / `rejected` / `mass_review` | Revisión donaciones |
| `cajero.donation.cash_confirmed` | Confirmación efectivo en caja |
| `admin.entrepreneur.created` / `updated` / `deactivated` | CRUD emprendedores |

## Variables `.env`

```env
WAYNA_AUDIT_LOG_ENABLED=true
```

## Consulta rápida (SQL / tinker)

```sql
SELECT action, user_id, subject_type, subject_id, created_at
FROM audit_logs
ORDER BY id DESC
LIMIT 20;
```

## Pruebas

```bash
php artisan test --filter=AuditLog
```

## Próximos pasos (fuera de S3-09)

- Vista admin de solo lectura para superadmin (T-A20).
- Ampliar acciones (campañas, puntos, webhooks procesados) si el equipo lo prioriza.
