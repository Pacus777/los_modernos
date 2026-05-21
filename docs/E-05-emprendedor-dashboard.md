# E-05 — Dashboard del emprendedor

## Ruta

| URL | Nombre | Notas |
|-----|--------|-------|
| `GET /emprendedor/dashboard` | `emprendedor.dashboard` | Panel principal |
| `GET /emprendedor/panel` | — | Redirige a `/emprendedor/dashboard` (compatibilidad E-03) |

## Dependencias

- **E-01 / E-02:** rol `emprendedor`, `user_id` en `emprendedores`, login y redirección.
- **E-03:** cuenta y acceso al panel.
- **E-04:** middleware `emprendedor.force_password` antes del dashboard.

## Backend

- `App\Services\EmprendedorDashboardService` — perfil, campaña activa, progreso (misma lógica que perfil turista), estadísticas de donaciones, seguidores, puntos, últimos aportes.
- `App\Http\Controllers\Emprendedor\DashboardController` — Inertia `Emprendedor/Dashboard`.

## Frontend

- `resources/js/Pages/Emprendedor/Dashboard.jsx` — resumen visual, barra de progreso (`BarraProgreso`), enlaces a perfil público y QR.

## Pruebas

```bash
php artisan test --filter=DashboardTest
php artisan test --filter=ForcePasswordChange
```

## Próximas tareas

- **E-06:** edición de perfil desde el panel.
- **E-07:** publicaciones / muro del emprendedor.
