# E-06 — Edición de perfil público (emprendedor)

## Rutas

| Método | URL | Nombre |
|--------|-----|--------|
| GET | `/emprendedor/perfil/editar` | `emprendedor.perfil.edit` |
| PUT | `/emprendedor/perfil` | `emprendedor.perfil.update` |

Protegidas por: `auth`, `verified`, `check.role:emprendedor`, `emprendedor.force_password`.

## Qué puede editar el emprendedor

- Nombre, apellidos, descripción
- Tipo de emprendimiento y departamento
- Foto de perfil, foto empresa, galería (máx. 4), video
- Redes: WhatsApp, Instagram, Facebook, TikTok, sitio web

## Qué no puede editar (solo admin)

- `estado` (activo / inactivo)
- `meta_monto` de referencia
- Campañas y credenciales de acceso

## Backend

- `UpdatePerfilPublicoRequest` — mismas reglas de medios/redes que admin
- `EmprendedorPerfilService` — guardado de textos, foto y medios
- `PerfilController` — formulario Inertia + redirect al dashboard
- `AuditAction::EmprendedorProfileUpdated`

## Frontend

- `Emprendedor/PerfilEdit.jsx` — reutiliza componentes admin de medios y redes
- Enlace desde `Emprendedor/Dashboard` → **Editar perfil público**

## Pruebas

```bash
php artisan test --filter=PerfilEditTest
```
