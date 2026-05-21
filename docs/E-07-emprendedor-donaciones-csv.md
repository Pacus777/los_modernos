# E-07 — Historial de donaciones + Excel/PDF WAYNA (emprendedor)

## Rutas

| Método | URL | Nombre |
|--------|-----|--------|
| GET | `/emprendedor/donaciones` | `emprendedor.donaciones.index` |
| GET | `/emprendedor/donaciones/exportar` | `emprendedor.donaciones.exportar` |

Mismos filtros en listado y exportación (query string).

## Filtros

- Estado: validado / pendiente / rechazado
- Fecha desde / hasta
- Rango de monto (bajo / medio / alto, igual que admin)

## Excel (formato WAYNA)

- Archivo `.xls` con colores de marca (naranja #f07e26, filas zebra, estados en verde/ámbar/rojo)
- Cabecera con nombre del emprendedor, fecha y resumen de totales
- Nombre: `wayna-donaciones-{id}-{fecha}.xls`
- Ruta: `GET /emprendedor/donaciones/exportar`

## PDF (legible para el emprendedor)

- Archivo `.pdf` generado con Dompdf
- Logo WAYNA, texto introductorio, resumen en cajas, leyenda de estados y tabla clara
- Nombre: `wayna-donaciones-{id}-{fecha}.pdf`
- Ruta: `GET /emprendedor/donaciones/exportar/pdf`

- Solo donaciones de **sus** campañas
- Auditoría: `AuditAction::EmprendedorDonationsExported` (metadata `formato`: `excel` | `pdf`)

## Backend

- `EmprendedorDonacionHistorialService`
- `DonacionHistorialController`
- `FiltrarDonacionesRequest`

## Pruebas

```bash
php artisan test --filter=DonacionHistorialTest
```
