# Gmail SMTP — credenciales emprendedor (E-03)

WAYNA envía las credenciales **solo por Gmail** y el correo del emprendedor debe ser **@gmail.com**.

## 1. Contraseña de aplicación (Google)

1. Cuenta Google con **verificación en 2 pasos** activada.
2. Ir a [Contraseñas de aplicaciones](https://myaccount.google.com/apppasswords).
3. Crear una app (ej. «WAYNA») → copiar los **16 caracteres** (sin espacios).

## 2. `.env` del proyecto

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-cuenta@gmail.com
MAIL_PASSWORD=abcdefghijklmnop
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="${APP_NAME}"

WAYNA_EMPRENDEDOR_CUENTA_ENVIAR_CORREO=true
WAYNA_EMPRENDEDOR_CUENTA_EXIGIR_GMAIL=true
```

```powershell
php artisan config:clear
php artisan wayna:probar-correo otra-cuenta@gmail.com
```

## 3. En el panel admin

- Correo del emprendedor: **obligatorio @gmail.com**
- Tras «Crear cuenta y enviar credenciales», el mail llega a esa bandeja Gmail.

## Desactivar regla @gmail.com (solo tests)

```env
WAYNA_EMPRENDEDOR_CUENTA_EXIGIR_GMAIL=false
```

No usar en producción si el negocio exige Gmail.
