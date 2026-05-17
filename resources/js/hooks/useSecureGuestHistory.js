import { useEffect } from 'react';

/**
 * En login: al usar Atrás/Adelante, recarga desde el servidor (no formulario en caché).
 */
export function useSecureGuestHistory() {
    useEffect(() => {
        if (typeof window === 'undefined') {
            return undefined;
        }

        window.history.replaceState({ secureGuest: true }, '', window.location.href);

        const onPopState = () => {
            window.location.reload();
        };

        window.addEventListener('popstate', onPopState);

        return () => window.removeEventListener('popstate', onPopState);
    }, []);
}
