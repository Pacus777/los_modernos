import LogoutButton from '@/Components/LogoutButton';
import WaynaNavBar from '@/Components/WaynaNavBar';
import { useReloadOnHistoryRestore } from '@/hooks/useReloadOnHistoryRestore';
import { usePage } from '@inertiajs/react';

export default function EmprendedorLayout({ header, children }) {
    const user = usePage().props.auth.user;

    useReloadOnHistoryRestore();

    return (
        <div className="min-h-screen bg-surface">
            <WaynaNavBar href={route('emprendedor.dashboard')} logoSize="nav">
                <div className="flex shrink-0 items-center gap-2 sm:gap-3">
                    <span className="max-w-[7rem] truncate text-xs font-medium text-white/90 sm:max-w-[9rem] sm:text-sm">
                        {user?.name}
                    </span>
                    <LogoutButton
                        aria-label="Cerrar sesión"
                        className="touch-target inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-3 py-2 text-xs font-bold text-wayna-700 shadow-sm transition hover:bg-wayna-50 sm:px-4 sm:text-sm"
                    >
                        Cerrar sesión
                    </LogoutButton>
                </div>
            </WaynaNavBar>

            {header ? (
                <div className="border-b border-wayna-200/80 bg-surface-card px-4 py-4 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-4xl border-l-4 border-wayna-500 pl-3">{header}</div>
                </div>
            ) : null}

            <main className="px-4 py-6 sm:px-6 lg:px-8">{children}</main>
        </div>
    );
}
