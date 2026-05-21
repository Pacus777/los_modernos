# SMTP — credenciales emprendedor (E-03)

WAYNA envía las credenciales por correo. El emprendedor puede usar **cualquier email válido** (`usuario@wayna.com`, `@unifranz.edu.bo`, Gmail, etc.).

## 1. Configurar envío (`.env`)

Ejemplo con Gmail como servidor de salida:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-cuenta@gmail.com
MAIL_PASSWORD=contraseña-de-aplicacion-16-caracteres
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="${APP_NAME}"

WAYNA_EMPRENDEDOR_CUENTA_ENVIAR_CORREO=true
```

Otro proveedor SMTP: cambiá `MAIL_HOST`, usuario y contraseña según tu servicio.

## 2. Probar envío

```powershell
php artisan config:clear
php artisan wayna:probar-correo destino@wayna.com
```

## 3. En el panel admin

- **Correo de acceso:** debe ser un email válido (con `@`).
- Tras «Crear cuenta y enviar credenciales», el mail llega a esa bandeja.
