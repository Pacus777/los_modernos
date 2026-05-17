import { router } from '@inertiajs/react';

/**
 * Cierra sesión con visita completa (limpia historial SPA de Inertia).
 */
export default function LogoutButton({ className = '', children, onClick, ...props }) {
    const handleLogout = (e) => {
        onClick?.(e);

        if (e?.defaultPrevented) {
            return;
        }

        router.post(route('logout'), {}, {
            preserveState: false,
            preserveScroll: false,
            replace: true,
        });
    };

    return (
        <button type="button" className={className} onClick={handleLogout} {...props}>
            {children}
        </button>
    );
}
