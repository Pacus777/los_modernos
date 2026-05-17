/**
 * Envía una pregunta al chat sin recargar la página Inertia (evita transiciones).
 */
export async function enviarPreguntaChat(payload) {
    const xsrf = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];

    const response = await fetch(route('chat.store'), {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(xsrf ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrf) } : {}),
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            pregunta: payload.pregunta,
            idioma: payload.idioma ?? 'es',
            context_emprendedor_id: payload.context_emprendedor_id || null,
        }),
    });

    if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        const message =
            data?.message ||
            (data?.errors?.pregunta?.[0] ?? null) ||
            'No se pudo enviar la pregunta.';

        throw new Error(message);
    }

    const data = await response.json();

    return data.rag ?? null;
}

/**
 * Si el enlace apunta a una sección del landing actual, devuelve el id (#explorar).
 */
export function seccionDesdeHref(href) {
    if (!href || typeof window === 'undefined') {
        return null;
    }

    try {
        const url = new URL(href, window.location.origin);
        const hash = url.hash.replace(/^#/, '');

        if (!hash) {
            return null;
        }

        const mismoPath =
            url.pathname === window.location.pathname ||
            (url.pathname === '/' && window.location.pathname === '/');

        return mismoPath ? hash : null;
    } catch {
        return null;
    }
}
