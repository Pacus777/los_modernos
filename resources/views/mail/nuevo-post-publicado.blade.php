<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva publicación — WAYNA</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; line-height: 1.5; color: #1c1917; max-width: 32rem; margin: 0 auto; padding: 1.5rem;">
    <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #b45309;">
        {{ config('app.name', 'WAYNA') }}
    </p>

    <h1 style="font-size: 1.25rem; margin: 0 0 1rem;">Nueva publicación</h1>

    <p>
        <strong>{{ $emprendedor->nombreCompleto() }}</strong> compartió una nueva publicación en WAYNA.
    </p>

    @if($post->contenido)
        <div style="margin: 1.25rem 0; padding: 1rem; border-radius: 0.75rem; background: #fff7ed; border: 1px solid #fed7aa;">
            <p style="margin: 0;">{!! nl2br(e($post->contenido)) !!}</p>
        </div>
    @endif

    @if($post->enlace_externo)
        <p>
            Enlace compartido:
            <a href="{{ $post->enlace_externo }}" style="color: #b45309;">{{ $post->enlace_externo }}</a>
        </p>
    @endif

    <p>
        <a href="{{ $perfilUrl }}" style="display: inline-block; padding: 0.6rem 1.2rem; background: #d97706; color: #fff; text-decoration: none; border-radius: 0.5rem; font-weight: 600;">
            Ver publicación
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
