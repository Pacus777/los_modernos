<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cuenta WAYNA</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.5; color: #1c1917; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #b45309;">
        {{ config('app.name', 'WAYNA') }}
    </p>

    @if ($esReenvio)
        <h1 style="font-size: 1.25rem; margin: 0 0 1rem;">Nueva contraseña temporal</h1>
        <p>Hola <strong>{{ $usuario->name }}</strong>,</p>
        <p>El equipo de WAYNA generó una nueva contraseña para tu cuenta vinculada al emprendimiento <strong>{{ $emprendedor->nombreCompleto() }}</strong>.</p>
    @else
        <h1 style="font-size: 1.25rem; margin: 0 0 1rem;">Tu cuenta de emprendedor</h1>
        <p>Hola <strong>{{ $usuario->name }}</strong>,</p>
        <p>Se creó tu acceso al sistema WAYNA para gestionar el perfil de <strong>{{ $emprendedor->nombreCompleto() }}</strong>.</p>
    @endif

    <div style="margin: 1.25rem 0; padding: 1rem; border-radius: 0.75rem; background: #fff7ed; border: 1px solid #fed7aa;">
        <p style="margin: 0 0 0.5rem;"><strong>Correo:</strong> {{ $usuario->email }}</p>
        <p style="margin: 0;"><strong>Contraseña temporal:</strong> <code style="font-size: 1rem;">{{ $passwordPlano }}</code></p>
    </div>

    <p>
        Iniciá sesión en:
        <a href="{{ $loginUrl }}" style="color: #b45309;">{{ $loginUrl }}</a>
    </p>

    <p style="font-size: 0.875rem; color: #57534e;">
        Por seguridad, cambiá la contraseña en cuanto ingreses (cuando esté disponible en tu panel).
        No compartas este correo.
    </p>

    <p style="font-size: 0.75rem; color: #a8a29e; margin-top: 2rem;">
        Mensaje automático — {{ config('app.name') }}
    </p>
</body>
</html>
