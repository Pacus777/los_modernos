<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva meta — WAYNA</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.5; color: #1c1917; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #b45309;">
        {{ config('app.name', 'WAYNA') }}
    </p>

    <h1 style="font-size: 1.25rem; margin: 0 0 1rem;">Nueva meta de apoyo</h1>

    <p>
        <strong>{{ $emprendedor->nombreCompleto() }}</strong> publicó una nueva meta en WAYNA.
    </p>

    <div style="margin: 1.25rem 0; padding: 1rem; border-radius: 0.75rem; background: #fff7ed; border: 1px solid #fed7aa;">
        <p style="margin: 0 0 0.5rem;">
            <strong>Meta:</strong> {{ $campana->titulo }}
        </p>
        <p style="margin: 0 0 0.5rem;">
            <strong>Monto objetivo:</strong> Bs {{ number_format((float) $campana->meta_apoyo, 2, ',', '.') }}
        </p>
        @if($campana->fecha_fin)
            <p style="margin: 0;">
                <strong>Disponible hasta:</strong> {{ $campana->fecha_fin->format('d/m/Y') }}
            </p>
        @endif
    </div>

    <p>
        <a href="{{ $perfilUrl }}" style="display: inline-block; padding: 0.6rem 1.2rem; background: #d97706; color: #fff; text-decoration: none; border-radius: 0.5rem; font-weight: 600;">
            Ver meta
        </a>
    </p>

    <p style="font-size: 0.875rem; color: #57534e;">
        Recibís este correo porque seguís a este emprendimiento.
        <a href="{{ $bajaUrl }}" style="color: #b45309;">Dejar de recibir novedades</a>.
    </p>

    <p style="font-size: 0.75rem; color: #a8a29e; margin-top: 2rem;">
        Mensaje automático — {{ config('app.name') }}
    </p>
</body>
</html>
