import { WaynaBrand } from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

/**
 * Panel administrador WAYNA — paleta naranja / tierra (referencia waynamercados.com).
 */
export default function AdminLayout({ header, children }) {
    const user = usePage().props.auth.user;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);

    const navItemClass = (active) =>
        `flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition ${
            active
                ? 'bg-wayna-700 text-white shadow-sm ring-1 ring-white/15'
                : 'text-white/90 hover:bg-wayna-600 hover:text-white'
        }`;

    const SidebarContent = () => (
        <>
            <div className="flex shrink-0 flex-col items-center justify-center border-b border-wayna-600/40 bg-wayna-500 px-4 py-5">
                <WaynaBrand
                    href={route('admin.dashboard')}
                    size="nav-lg"
                    tone="on-brand"
                    onClick={() => setSidebarOpen(false)}
                />
            </div>

            <nav className="flex flex-1 flex-col gap-1 p-3">
                <Link
                    href={route('admin.dashboard')}
                    className={navItemClass(
                        route().current('admin.dashboard'),
                    )}
                    onClick={() => setSidebarOpen(false)}
                >
                    <span>Panel</span>
                </Link>
                <Link
                    href={route('admin.emprendedores.index')}
                    className={navItemClass(
                        route().current('admin.emprendedores.*'),
                    )}
                    onClick={() => setSidebarOpen(false)}
                >
                    <span>Emprendedores</span>
                </Link>
                <Link
                    href={route('admin.campanas.index')}
                    className={navItemClass(route().current('admin.campanas.*'))}
                    onClick={() => setSidebarOpen(false)}
                >
                    <span>Campañas</span>
                </Link>
                <Link
                    href={route('admin.donaciones.index')}
                    className={navItemClass(
                        route().current('admin.donaciones.*'),
                    )}
                    onClick={() => setSidebarOpen(false)}
                >
                    <span>Donaciones</span>
                </Link>
                <Link
                    href={route('admin.transacciones.index')}
                    className={navItemClass(
                        route().current('admin.transacciones.*'),
                    )}
                    onClick={() => setSidebarOpen(false)}
                >
                    <span>Trazabilidad</span>
                </Link>
            </nav>

            <div className="border-t border-wayna-600/50 bg-wayna-600/30 p-3">
                <div className="flex flex-col gap-1">
                    {userMenuOpen && (
                        <div
                            id="admin-sidebar-user-menu"
                            className="mb-1 flex flex-col overflow-hidden rounded-lg border border-wayna-700/50 bg-wayna-800 shadow-inner"
                        >
                            <Link
                                href={route('profile.edit')}
                                className="px-3 py-2.5 text-sm text-white/95 transition hover:bg-wayna-700 hover:text-white"
                                onClick={() => {
                                    setUserMenuOpen(false);
                                    setSidebarOpen(false);
                                }}
                            >
                                Perfil
                            </Link>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="w-full border-t border-wayna-800/80 px-3 py-2.5 text-left text-sm text-orange-50/95 transition hover:bg-wayna-800/90 hover:text-white"
                                onClick={() => {
                                    setUserMenuOpen(false);
                                    setSidebarOpen(false);
                                }}
                            >
                                Cerrar sesión
                            </Link>
                        </div>
                    )}
                    <button
                        type="button"
                        aria-expanded={userMenuOpen}
                        aria-controls="admin-sidebar-user-menu"
                        className="flex w-full items-center justify-between rounded-lg bg-wayna-700/80 px-3 py-2 text-left text-sm text-white hover:bg-wayna-700"
                        onClick={() => setUserMenuOpen((o) => !o)}
                    >
                        <span className="truncate">{user?.name}</span>
                        <svg
                            className={`h-4 w-4 shrink-0 opacity-80 transition-transform duration-200 ${
                                userMenuOpen ? 'rotate-180' : ''
                            }`}
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden
                        >
                            <path
                                fillRule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clipRule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </>
    );

    return (
        <div className="min-h-screen bg-surface">
            {sidebarOpen && (
                <button
                    type="button"
                    className="fixed inset-0 z-40 bg-stone-900/50 backdrop-blur-[1px] lg:hidden"
                    aria-label="Cerrar menú"
                    onClick={() => {
                        setSidebarOpen(false);
                        setUserMenuOpen(false);
                    }}
                />
            )}

            <div className="flex min-h-screen">
                <aside className="relative z-50 hidden h-screen w-64 shrink-0 flex-col overflow-y-auto bg-gradient-to-b from-wayna-500 via-wayna-600 to-wayna-700 lg:flex lg:flex-col">
                    <SidebarContent />
                </aside>

                <aside
                    className={`fixed inset-y-0 left-0 z-50 flex h-full max-h-screen w-64 flex-col overflow-y-auto bg-gradient-to-b from-wayna-500 via-wayna-600 to-wayna-700 shadow-2xl shadow-wayna-950/40 transition-transform duration-200 ease-out lg:hidden ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    }`}
                >
                    <SidebarContent />
                </aside>

                <div className="flex min-w-0 flex-1 flex-col">
                    <div className="nav-wayna-bar sticky top-0 z-30 lg:hidden">
                        <div className="flex h-[4.25rem] items-center justify-between gap-3 px-4 sm:h-[4.75rem]">
                            <button
                                type="button"
                                className="rounded-lg p-2 text-white hover:bg-wayna-600"
                                onClick={() => setSidebarOpen(true)}
                                aria-label="Abrir menú"
                            >
                                <svg
                                    className="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                </svg>
                            </button>
                            <WaynaBrand
                                href={route('admin.dashboard')}
                                size="nav"
                                tone="on-brand"
                            />
                            <span className="w-10" aria-hidden />
                        </div>
                    </div>

                    {header && (
                        <header className="border-b border-wayna-100 bg-white shadow-sm">
                            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                                {header}
                            </div>
                        </header>
                    )}

                    <main className="flex-1">{children}</main>
                </div>
            </div>
        </div>
    );
}
