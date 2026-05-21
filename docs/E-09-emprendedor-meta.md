# E-09 — Meta de apoyo del emprendedor (campaña activa)

El emprendedor gestiona su **meta de recaudación** mediante una campaña activa (`campanas`), sin depender solo del admin.

## Rutas

| Método | URL | Nombre |
|--------|-----|--------|
| GET | `/emprendedor/meta/crear` | `emprendedor.meta.create` |
| POST | `/emprendedor/meta` | `emprendedor.meta.store` |
| GET | `/emprendedor/meta/editar` | `emprendedor.meta.edit` |
| PUT | `/emprendedor/meta/{campana}` | `emprendedor.meta.update` |
| POST | `/emprendedor/meta/{campana}/cerrar` | `emprendedor.meta.close` |

Middleware: `auth`, `verified`, `check.role:emprendedor`, `emprendedor.force_password`.

## Reglas de negocio

- Solo **una campaña activa** por emprendedor (igual que admin PB-09).
- **store**: crea campaña en estado `activa` con `monto_recaudado = 0`.
- **update**: solo campañas propias y activas; título, meta en Bs y fechas.
- **close**: pasa a `finalizada`; no borra donaciones asociadas.

## Backend

- `EmprendedorMetaController` — create, edit, store, update, close
- `EmprendedorMetaService` — crear, actualizar, cerrar, resolver campaña activa
- `StoreEmprendedorMetaRequest` / `UpdateEmprendedorMetaRequest`
- `CampanaPolicy` — `create`, `update`, `close` (emprendedor dueño; admin bypass)

## Auditoría

- `EmprendedorMetaCreated`
- `EmprendedorMetaUpdated`
- `EmprendedorMetaClosed`

## Frontend

- `Emprendedor/Meta/Form.jsx` — crear / editar meta
- Panel y sidebar: enlaces **Mi meta de apoyo**, **Crear meta**, **Editar** y **Cerrar**

## Pruebas

```bash
php artisan test --filter=EmprendedorMetaTest
```
