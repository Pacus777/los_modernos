const STORAGE_KEY = 'wayna-enter-skip';

/**
 * Evita la intro Wayna en la siguiente navegación o montaje de página.
 * Usado tras acciones del chat (enviar pregunta, enlaces sugeridos).
 */
export function setWaynaEnterSkip() {
    try {
        sessionStorage.setItem(STORAGE_KEY, '1');
    } catch {
        // sessionStorage no disponible
    }
}

export function consumeWaynaEnterSkip() {
    try {
        if (sessionStorage.getItem(STORAGE_KEY) === '1') {
            sessionStorage.removeItem(STORAGE_KEY);

            return true;
        }
    } catch {
        // ignore
    }

    return false;
}
