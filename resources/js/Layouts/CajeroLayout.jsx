import { Link, usePage } from '@inertiajs/react';

/**
 * Cajero — cabecera clara con acento WAYNA (naranja).
 */
export default function CajeroLayout({ header, children }) {
    const user = usePage().props.auth.user;

    return (
        <div className="min-h-screen bg-wayna-50/90">
            <header className="border-b border-wayna-200 bg-white shadow-sm">
                <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div className="min-w-0 flex-1 border-l-4 border-wayna-500 pl-3">
                        {header ?? (
                            <h1 className="truncate text-lg font-semibold text-wayna-950 sm:text-xl">
                                Pagos en efectivo
                            </h1>
                        )}
                    </div>
                    <div className="flex shrink-0 items-center gap-3">
                        <span className="hidden max-w-[10rem] truncate text-sm text-stone-600 sm:inline">
                            {user?.name}
                        </span>
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="rounded-lg bg-wayna-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-wayna-700 focus:outline-none focus:ring-2 focus:ring-wayna-500 focus:ring-offset-2"
                        >
                            Salir
                        </Link>
                    </div>
                </div>
            </header>

            <main className="px-4 py-6 sm:px-6 lg:px-8">{children}</main>
        </div>
    );
}
