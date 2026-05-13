import ApplicationLogo from '@/Components/ApplicationLogo';
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
        `flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition ${
            active
                ? 'bg-wayna-500 text-white shadow-sm'
                : 'text-orange-100/90 hover:bg-wayna-900/80 hover:text-white'
        }`;

    const SidebarContent = () => (
        <>
            <div className="flex h-16 shrink-0 items-center gap-2 border-b border-wayna-800/80 px-4">
                <Link
                    href={route('admin.dashboard')}
                    className="flex items-center gap-2"
                    onClick={() => setSidebarOpen(false)}
                >
                    <ApplicationLogo className="h-8 w-auto fill-current text-wayna-200" />
                    <span className="text-sm font-semibold tracking-tight text-white">
                        WAYNA
                    </span>
                </Link>
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
            </nav>

            <div className="border-t border-wayna-800/80 p-3">
                <div className="flex flex-col gap-1">
                    {userMenuOpen && (
                        <div
                            id="admin-sidebar-user-menu"
                            className="mb-1 flex flex-col overflow-hidden rounded-lg border border-wayna-700/50 bg-wayna-950/95 shadow-inner"
                        >
                            <Link
                                href={route('profile.edit')}
                                className="px-3 py-2.5 text-sm text-orange-50/95 transition hover:bg-wayna-800/90 hover:text-white"
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
                        className="flex w-full items-center justify-between rounded-lg bg-wayna-900/60 px-3 py-2 text-left text-sm text-orange-50 hover:bg-wayna-900"
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
        <div className="min-h-screen bg-wayna-50/80">
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
                <aside className="relative z-50 hidden h-screen w-64 shrink-0 flex-col overflow-y-auto bg-gradient-to-b from-wayna-950 via-wayna-900 to-wayna-950 lg:flex lg:flex-col">
                    <SidebarContent />
                </aside>

                <aside
                    className={`fixed inset-y-0 left-0 z-50 flex h-full max-h-screen w-64 flex-col overflow-y-auto bg-gradient-to-b from-wayna-950 via-wayna-900 to-wayna-950 shadow-2xl shadow-wayna-950/40 transition-transform duration-200 ease-out lg:hidden ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    }`}
                >
                    <SidebarContent />
                </aside>

                <div className="flex min-w-0 flex-1 flex-col">
                    <div className="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-wayna-200/80 bg-white/95 px-4 backdrop-blur-sm lg:hidden">
                        <button
                            type="button"
                            className="rounded-lg p-2 text-wayna-800 hover:bg-wayna-50"
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
                        <span className="text-sm font-semibold text-wayna-900">
                            WAYNA
                        </span>
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
