import WaynaNavBar from '@/Components/WaynaNavBar';
import { Link, usePage } from '@inertiajs/react';

export default function CajeroLayout({ header, children }) {
    const user = usePage().props.auth.user;

    return (
        <div className="min-h-screen bg-surface">
            <WaynaNavBar href={route('cajero.efectivo')} logoSize="nav">
                <div className="flex items-center gap-2 sm:gap-3">
                    <span className="hidden max-w-[9rem] truncate text-sm font-medium text-white/90 sm:inline">
                        {user?.name}
                    </span>
                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="rounded-full border border-white/60 bg-white/10 px-4 py-1.5 text-sm font-bold text-white transition hover:bg-white hover:text-wayna-700"
                    >
                        Salir
                    </Link>
                </div>
            </WaynaNavBar>

            {header && (
                <div className="border-b border-wayna-200/80 bg-surface-card px-4 py-4 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-7xl border-l-4 border-wayna-500 pl-3">
                        {header}
                    </div>
                </div>
            )}

            <main className="px-4 py-6 sm:px-6 lg:px-8">{children}</main>
        </div>
    );
}
