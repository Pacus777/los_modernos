# E-10 — Notificación email al emprendedor por donación + preferencias

## Alcance

- Correo al emprendedor cuando una donación pasa a estado **validado** (suma a `monto_recaudado`).
- Preferencia `emprendedores.notificar_donaciones_email` (default `true`).
- Panel: `/emprendedor/preferencias`.

## Disparo

`DonacionObserver` → `EmprendedorDonacionNotificacionService` tras incrementar recaudación (creación ya validada o transición pendiente → validado).

## Archivos

| Pieza | Ruta |
|-------|------|
| Migración | `database/migrations/2026_05_22_100000_add_notificar_donaciones_email_to_emprendedores_table.php` |
| Mailable | `app/Mail/EmprendedorDonacionRecibidaMail.php` |
| Vista | `resources/views/mail/emprendedor-donacion-recibida.blade.php` |
| Servicio | `app/Services/EmprendedorDonacionNotificacionService.php` |
| UI | `resources/js/Pages/Emprendedor/Preferencias/Index.jsx` |
| Tests | `tests/Feature/Emprendedor/EmprendedorDonacionNotificacionTest.php` |

## Pruebas

```bash
php artisan migrate
php artisan test --filter=EmprendedorDonacionNotificacionTest
```
