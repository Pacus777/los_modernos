# S3-07 — Auditoría CSRF, queries Eloquent y CSP

## 1. CSRF

| Área | Estado |
|------|--------|
| Rutas `web` (Inertia, formularios) | Protegidas por `ValidateCsrfToken` (Laravel 11 en `bootstrap/app.php`) |
| Excepción | Solo `webhooks/*` (Libélula; validación por firma HMAC) |
| Chat turista (`fetch`) | Envía `X-XSRF-TOKEN` desde cookie `XSRF-TOKEN` (`resources/js/utils/chatApi.js`) |
| Axios global | `xsrfCookieName` / `xsrfHeaderName` en `resources/js/bootstrap.js` |

### Rutas POST principales (requieren CSRF)

- `POST /login`, `/logout`, registro, 2FA
- `POST /donaciones`, `/chat`, `/idioma`
- `POST /admin/*` (emprendedores, campañas, 2FA, sesión)
- `POST /cajero/*`

### Pruebas automáticas

- `tests/Feature/Security/CsrfProtectionTest.php`

## 2. Revisión queries Eloquent / SQL

Inventario en `App\Support\EloquentQueryAudit::hallazgosRevisados()`.

| Archivo | Riesgo | Conclusión |
|---------|--------|------------|
| `EmprendedorExplorarService` | `whereRaw` con término de búsqueda | OK — bindings `?` |
| `EmprendedorController` (top donaciones) | `DB::raw` agregaciones | OK — sin input de usuario |
| `ReporteController` | `selectRaw` / `orderByRaw` | OK — métricas internas |

**Mass assignment:** modelos usan `$fillable`; altas/ediciones pasan por FormRequest.

No se encontraron concatenaciones de request en SQL crudo.

## 3. Content-Security-Policy (CSP)

| Componente | Ubicación |
|------------|-----------|
| Config | `config/security.php` |
| Builder | `App\Support\SecurityContentPolicy` |
| Middleware | `App\Http\Middleware\SecurityHeaders` |

Cabeceras adicionales: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`.

### Variables `.env`

```env
WAYNA_CSP_ENABLED=true
WAYNA_CSP_REPORT_ONLY=false
WAYNA_VITE_DEV_ORIGIN=http://127.0.0.1:5173
```

En **local** con `APP_DEBUG=true` se permite el origen Vite (`5173`) para `npm run dev`.

En **producción** los assets van por `'self'` (build de Vite).

### Pruebas

- `tests/Feature/Security/SecurityHeadersTest.php`
