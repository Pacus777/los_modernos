# S2-07 — QR BCP estático WAYNA + instrucciones

## Alcance

- Un único QR de la cuenta WAYNA en BCP (no un QR distinto por donación).
- Instrucciones paso a paso en español/inglés para pagar desde la app BCP.
- Vista previa en el formulario de donación y pantalla completa en confirmación.

## Configuración

```env
WAYNA_BCP_QR_ENABLED=true
WAYNA_BCP_QR_IMAGE=/images/wayna-qr-bcp.svg
WAYNA_BCP_QR_TITULAR="WAYNA Conecta"
```

Reemplazar `public/images/wayna-qr-bcp.svg` por el **PNG oficial** que entregue el banco cuando esté disponible.

## Cuándo se muestra

- Tipo de pago con `proveedor = banco` (ej. código `qr` del seeder).
- No aplica a efectivo ni Libélula/tarjeta pasarela.

## Archivos

| Pieza | Ruta |
|-------|------|
| Soporte | `app/Support/WaynaBcpQr.php` |
| Componente | `resources/js/Components/Turista/WaynaQrBcpEstatico.jsx` |
| Imagen placeholder | `public/images/wayna-qr-bcp.svg` |
| i18n | `tourist.bcpQr.*` en `es.json` / `en.json` |

## Pruebas

```bash
php artisan test --filter=WaynaBcpQrTest
php artisan test --filter=DonacionConfirmacionBcpQrTest
```
