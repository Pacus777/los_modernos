# E-03 — Cuenta de emprendedor desde admin

## Objetivo

Permitir que un **administrador** cree la cuenta de acceso (`users` + rol `emprendedor`) para un emprendedor ya registrado y **envíe credenciales por correo**.

## Dónde está en el panel

| Momento | Pantalla |
|---------|----------|
| **Alta nueva** | Tras los 4 pasos → **Finalizar registro** (`/admin/emprendedores/{id}/finalizar`) |
| **Edición** | Al final de **Editar emprendedor**, panel de cuenta |

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

## Gmail obligatorio

- Correo del emprendedor: solo **@gmail.com**
- Envío: **SMTP de Gmail** (ver `docs/GMAIL-SMTP-WAYNA.md`)

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-cuenta@gmail.com
MAIL_PASSWORD=contraseña-de-aplicacion-16-caracteres
WAYNA_EMPRENDEDOR_CUENTA_EXIGIR_GMAIL=true
```

Probar: `php artisan wayna:probar-correo destino@gmail.com`

## Auditoría (S3-09)

- `admin.entrepreneur.account_created`
- `admin.entrepreneur.credentials_resent`

## Pruebas

```bash
php artisan test --filter=EmprendedorCuenta
```

## Dependencias

- **E-01:** rol `emprendedor` y `user_id` en `emprendedores` (ya en el proyecto).
- Tras login, el emprendedor entra a **`/emprendedor/panel`** (`emprendedor.dashboard`). Funciones avanzadas en E-04 / E-07.
