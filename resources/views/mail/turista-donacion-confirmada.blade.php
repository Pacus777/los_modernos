<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tu aporte fue confirmado — {{ config('app.name', 'WAYNA') }}</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.5; color: #1c1917; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #b45309;">
        {{ config('app.name', 'WAYNA') }}
    </p>

    <h1 style="font-size: 1.25rem; margin: 0 0 1rem;">¡Tu aporte fue confirmado!</h1>

    <p>Hola<strong>{{ $nombreApoyo ? ' ' . $nombreApoyo : '' }}</strong>,</p>

    <p>Te confirmamos que tu valioso aporte a la campaña <strong>{{ $campanaTitulo }}</strong> del emprendedor <strong>{{ $emprendedorNombre }}</strong> fue validado con éxito.</p>

    <div style="margin: 1.25rem 0; padding: 1rem; border-radius: 0.75rem; background: #ecfdf5; border: 1px solid #a7f3d0;">
        <p style="margin: 0 0 0.5rem;"><strong>Monto:</strong> Bs {{ number_format((float) $donacion->monto, 2, ',', '.') }}</p>
        <p style="margin: 0 0 0.5rem;"><strong>Estado:</strong> Confirmado / Validado</p>
        @if($donacion->referencia_pago)
            <p style="margin: 0;"><strong>Referencia de pago:</strong> {{ $donacion->referencia_pago }}</p>
        @endif
    </div>

    <p style="margin-top: 1.5rem;">¡Muchísimas gracias por apoyar el desarrollo de las comunidades!</p>

    <p style="margin-top: 1.5rem;">
        <a href="{{ $exitosaUrl }}" style="display: inline-block; padding: 0.6rem 1.2rem; background: #d97706; color: #fff; text-decoration: none; border-radius: 0.5rem; font-weight: 600;">
            Ver comprobante
        </a>
    </p>

    <p style="font-size: 0.75rem; color: #a8a29e; margin-top: 2rem;">
        Mensaje automático — {{ config('app.name') }}
    </p>
</body>
</html>
