<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmar seguimiento — WAYNA</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.5; color: #1c1917; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #b45309;">
        {{ config('app.name', 'WAYNA') }}
    </p>

    <h1 style="font-size: 1.25rem; margin: 0 0 1rem;">Confirmá tu seguimiento</h1>

    <p>Querés recibir novedades de <strong>{{ $emprendedor->nombreCompleto() }}</strong> en WAYNA.</p>

    <p>Hacé clic en el botón para confirmar que este correo es tuyo:</p>

    <p>
        <a href="{{ $confirmarUrl }}" style="display: inline-block; padding: 0.6rem 1.2rem; background: #d97706; color: #fff; text-decoration: none; border-radius: 0.5rem; font-weight: 600;">
            Confirmar seguimiento
        </a>
    </p>

    <p style="font-size: 0.875rem; color: #57534e;">
        Si no pediste esto, ignorá este mensaje o
        <a href="{{ $bajaUrl }}" style="color: #b45309;">cancelá la solicitud</a>.
    </p>

    <p style="font-size: 0.75rem; color: #a8a29e; margin-top: 2rem;">
        Mensaje automático — {{ config('app.name') }}
    </p>
</body>
</html>
