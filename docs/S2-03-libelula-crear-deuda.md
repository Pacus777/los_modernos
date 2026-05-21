# S2-03 — LibelulaService: crearDeuda() + transaction_id

## Alcance

- `App\Services\LibelulaService::crearDeuda()` llama a **REGISTRAR DEUDA** (`POST /rest/deuda/registrar`).
- Persiste en `donaciones`: `transaction_id` (`id_transaccion`), `checkout_url` (`url_pasarela_pagos`), `proveedor_pago`, `metadata_pago`.
- Integrado en `DonacionService::registrar()` cuando `tipos_pago.proveedor = libelula`.

## Configuración (.env)

```env
LIBELULA_APP_KEY=tu-appkey-de-libelula
LIBELULA_SANDBOX=true
LIBELULA_SANDBOX_BASE_URL=http://www.todotix.com:10888
LIBELULA_BASE_URL=https://api.todotix.com
```

Sin `LIBELULA_APP_KEY` y con `LIBELULA_FAKE_WHEN_UNCONFIGURED=true` (default) se simula un `transaction_id` para desarrollo/tests.

## Pruebas

```bash
php artisan test --filter=LibelulaServiceTest
php artisan test --filter=DonacionLibelulaIntegracionTest
```

## Flujo

1. Turista elige tipo de pago Libélula (ej. tarjeta en seeder).
2. `DonacionService` crea donación pendiente y llama `crearDeuda()`.
3. Turista ve confirmación con QR (si `qr_simple_url`) o enlace a pasarela (`checkout_url`).
4. El webhook (`S3-02`) confirma el pago usando el mismo `transaction_id`.
