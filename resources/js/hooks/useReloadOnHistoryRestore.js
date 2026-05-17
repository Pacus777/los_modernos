import { router } from '@inertiajs/react';
import { useEffect } from 'react';

/**
 * Si el usuario vuelve con atrás/adelante desde caché del navegador, recarga desde el servidor.
 */
export function useReloadOnHistoryRestore() {
    useEffect(() => {
        const onPageShow = (event) => {
            if (event.persisted) {
                router.reload({ replace: true });
            }
        };

        window.addEventListener('pageshow', onPageShow);

        return () => window.removeEventListener('pageshow', onPageShow);
    }, []);
}
