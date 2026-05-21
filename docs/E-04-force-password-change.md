# E-04 — Cambio obligatorio de contraseña (emprendedor)

## Objetivo

Tras crear o reenviar credenciales desde admin (E-03), el emprendedor debe elegir una contraseña propia antes de usar el panel.

## Backend

| Pieza | Descripción |
|-------|-------------|
| `users.must_change_password` | `true` al crear/reenviar cuenta |
| `User::debeCambiarPassword()` | Consulta del flag |
| `ForcePasswordChange` middleware | Alias `emprendedor.force_password` — redirige a `/emprendedor/password/obligatorio` |
| `ForcePasswordChangeController` | GET formulario + POST guardar (reglas `Password::defaults()`) |
| `AuthRedirect` | Tras login, si flag activo → ruta de cambio |
| `AuditAction::EmprendedorPasswordChanged` | Al completar el cambio |

## Rutas

- `GET/POST /emprendedor/password/obligatorio` — sin middleware `emprendedor.force_password`
- Resto del panel — con middleware

## Frontend

- `resources/js/Pages/Emprendedor/CambiarPassword.jsx` — layout auth WAYNA

## Pruebas

```bash
php artisan test --filter=ForcePasswordChange
```

## Dependencias

- E-03: `EmprendedorCuentaService` marca `must_change_password` en `crearCuenta` y `reenviarCredenciales`
