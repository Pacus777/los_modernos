# E-03 — Cuenta de emprendedor desde admin

## Objetivo

Permitir que un **administrador** cree la cuenta de acceso (`users` + rol `emprendedor`) para un emprendedor ya registrado y **envíe credenciales por correo**.

## Dónde está en el panel

| Momento | Pantalla |
|---------|----------|
| **Alta nueva** | Tras los 4 pasos → **Finalizar registro** (`/admin/emprendedores/{id}/finalizar`) |
| **Directorio / edición** | Botón **Credenciales** → modal en listado de emprendedores |

## Flujo de alta

1. Pasos 1–4 → **Continuar al acceso** (guarda perfil + QR).
2. Pantalla **Finalizar registro** (paso 5): credenciales con diseño admin.
3. Opcional: crear cuenta y enviar correo.
4. **Finalizar registro** u **Omitir acceso por ahora** → listado.

## Flujo técnico cuenta

1. Admin ingresa correo → `POST /admin/emprendedores/{id}/cuenta`.
2. Crea usuario, rol, vincula `user_id`, envía mail.
3. Si ya hay cuenta: **Regenerar contraseña y reenviar**.

## Servicio y correo

| Pieza | Ruta |
|-------|------|
| Lógica | `App\Services\EmprendedorCuentaService` |
| Mailable | `App\Mail\EmprendedorCuentaCredencialesMail` |
| Vista | `resources/views/mail/emprendedor-cuenta-credenciales.blade.php` |

## Correo del emprendedor

- Cualquier **email válido** con `@` (`@wayna.com`, `@unifranz.edu.bo`, Gmail, etc.).
- Envío vía **SMTP** configurado en `.env` (ver `docs/GMAIL-SMTP-WAYNA.md`).

Probar: `php artisan wayna:probar-correo destino@wayna.com`

## Auditoría (S3-09)

- `admin.entrepreneur.account_created`
- `admin.entrepreneur.credentials_resent`

## Pruebas

```bash
php artisan test --filter=EmprendedorCuenta
```

## Dependencias

- **E-01:** rol `emprendedor` y `user_id` en `emprendedores` (ya en el proyecto).
- Tras login, el emprendedor entra a **`/emprendedor/dashboard`** (`emprendedor.dashboard`). `/emprendedor/panel` redirige ahí. Detalle del panel en **E-05**.
