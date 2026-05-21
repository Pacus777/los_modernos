# S3-08 — Backups automáticos + alerta Telegram

## Paquete

[spatie/laravel-backup](https://spatie.be/docs/laravel-backup) v10.

## Qué respalda

| Fuente | Ruta |
|--------|------|
| Base de datos | Conexión `DB_CONNECTION` (.env) |
| Archivos públicos | `storage/app/public` (fotos, QR, galería) |

Destino: disco `backups` → `storage/app/backups/`.

## Programación

En `bootstrap/app.php` (Laravel scheduler):

- `backup:clean` — diario a las `WAYNA_BACKUP_CLEANUP_AT` (default 01:00)
- `backup:run` — diario a las `WAYNA_BACKUP_RUN_AT` (default 01:30)

**Producción:** cron cada minuto:

```bash
* * * * * cd /ruta/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

**Windows / Laragon:** Programador de tareas con el mismo comando.

## Telegram

Variables `.env`:

```env
TELEGRAM_BOT_TOKEN=...
TELEGRAM_CHAT_ID=...
TELEGRAM_BACKUP_ALERTS_ENABLED=true
```

Eventos:

- `BackupWasSuccessful` → mensaje ✅
- `BackupHasFailed` → mensaje ❌

Listener: `App\Listeners\NotifyTelegramOnBackupResult`.

## Requisitos PHP (Laragon)

1. Extensión **zip** habilitada en `php.ini` (`extension=zip`).
2. **PostgreSQL:** `pg_dump` debe existir en el equipo. En Windows suele no estar en PATH; define en `.env`:
   ```env
   PG_DUMP_BINARY_PATH="C:/Program Files/PostgreSQL/16/bin"
   ```
   (carpeta `bin`, sin `pg_dump.exe`). Laragon no incluye PostgreSQL por defecto; instala el cliente desde [postgresql.org](https://www.postgresql.org/download/windows/) o usa la misma versión que el servidor.
3. **MySQL:** `mysqldump` en PATH o:
   ```env
   MYSQL_DUMP_BINARY_PATH="C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin"
   ```
4. **SQLite:** copia del archivo `.sqlite` (no requiere binarios extra).

## Comandos manuales

```bash
php artisan backup:run
php artisan backup:list
php artisan backup:clean
```

## Desactivar respaldos programados

```env
WAYNA_BACKUP_ENABLED=false
```
