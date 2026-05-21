/**
 * Alterna reacción en un post (JSON, sin recargar Inertia).
 */
export async function alternarReaccionPost(postId, tipo) {
    const xsrf = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];

    const response = await fetch(route('turista.posts.reaccion.store', postId), {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(xsrf ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrf) } : {}),
        },
        credentials: 'same-origin',
        body: JSON.stringify({ tipo }),
    });

    if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        const message =
            data?.message ||
            (data?.errors?.tipo?.[0] ?? null) ||
            'No se pudo guardar la reacción.';

        throw new Error(message);
    }

    return response.json();
}
