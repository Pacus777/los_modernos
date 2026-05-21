<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aporte recibido — WAYNA</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.5; color: #1c1917; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #b45309;">
        {{ config('app.name', 'WAYNA') }}
    </p>

    <h1 style="font-size: 1.25rem; margin: 0 0 1rem;">¡Recibiste un nuevo aporte!</h1>

    <p>Hola <strong>{{ $emprendedor->nombreCompleto() }}</strong>,</p>

    <p>Se confirmó un aporte a tu campaña <strong>{{ $campanaTitulo }}</strong>.</p>

    <div style="margin: 1.25rem 0; padding: 1rem; border-radius: 0.75rem; background: #ecfdf5; border: 1px solid #a7f3d0;">
        <p style="margin: 0 0 0.5rem;"><strong>Monto:</strong> Bs {{ number_format((float) $donacion->monto, 2, ',', '.') }}</p>
        <p style="margin: 0 0 0.5rem;"><strong>Estado:</strong> Validado (suma a tu recaudación)</p>
        @if($nombreApoyo)
            <p style="margin: 0 0 0.5rem;"><strong>Quien apoyó:</strong> {{ $nombreApoyo }}</p>
        @endif
        <p style="margin: 0;"><strong>Referencia:</strong> {{ $donacion->referencia_pago ?? '—' }}</p>
    </div>

    <p>
        <a href="{{ $panelUrl }}" style="display: inline-block; padding: 0.6rem 1.2rem; background: #d97706; color: #fff; text-decoration: none; border-radius: 0.5rem; font-weight: 600;">
            Ver en mi panel
        </a>
    </p>

    <p style="font-size: 0.875rem; color: #57534e;">
        Podés desactivar estos correos en
        <a href="{{ $preferenciasUrl }}" style="color: #b45309;">preferencias de notificaciones</a>.
    </p>

    <p style="font-size: 0.75rem; color: #a8a29e; margin-top: 2rem;">
        Mensaje automático — {{ config('app.name') }}
    </p>
</body>
</html>
