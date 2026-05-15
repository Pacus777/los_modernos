import {
    IconCampanas,
    IconDonaciones,
    IconEmprendedores,
    IconPanel,
    IconTrazabilidad,
} from '@/Components/Admin/AdminNavIcons';
import { WaynaBrand } from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

const NAV_ITEMS = [
    {
        label: 'Panel',
        routeName: 'admin.dashboard',
        match: 'admin.dashboard',
        Icon: IconPanel,
    },
    {
        label: 'Emprendedores',
        routeName: 'admin.emprendedores.index',
        match: 'admin.emprendedores.*',
        Icon: IconEmprendedores,
    },
    {
        label: 'Campañas',
        routeName: 'admin.campanas.index',
        match: 'admin.campanas.*',
        Icon: IconCampanas,
    },
    {
        label: 'Donaciones',
        routeName: 'admin.donaciones.index',
        match: 'admin.donaciones.*',
        Icon: IconDonaciones,
    },
    {
        label: 'Trazabilidad',
        routeName: 'admin.transacciones.index',
        match: 'admin.transacciones.*',
        Icon: IconTrazabilidad,
    },
];

function AdminNavLink({ item, expanded, onNavigate }) {
    const active = route().current(item.match);

    return (
        <Link
            href={route(item.routeName)}
            title={item.label}
            onClick={onNavigate}
            className={`group/item flex items-center gap-3 rounded-xl px-2.5 py-2.5 text-sm font-semibold transition-all duration-200 ${
                active
                    ? 'bg-wayna-700 text-white shadow-md ring-1 ring-white/20'
                    : 'text-white/90 hover:bg-wayna-600/95 hover:text-white hover:shadow-sm'
            } ${expanded ? 'translate-x-0' : 'hover:scale-[1.02]'}`}
        >
            <span
                className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition-colors duration-200 ${
                    active
                        ? 'bg-white/20'
                        : 'bg-white/10 group-hover/item:bg-white/20'
                }`}
            >
                <item.Icon className="h-5 w-5" />
            </span>
            <span
                className={`admin-sidebar-nav-label ${expanded ? '!max-w-[12rem] !opacity-100' : ''}`}
            >
                {item.label}
            </span>
        </Link>
    );
}

/**
 * Panel administrador WAYNA — sidebar expandible al hover (escritorio).
 */
export default function AdminLayout({ header, children }) {
    const user = usePage().props.auth.user;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);

    const closeMobile = () => {
        setSidebarOpen(false);
        setUserMenuOpen(false);
    };

    const SidebarContent = ({ expanded = false }) => (
        <>
            <div className="flex w-full min-h-[5.25rem] shrink-0 items-center justify-center border-b border-wayna-600/40 bg-wayna-500 px-2 py-4">
                <WaynaBrand
                    href={route('admin.dashboard')}
                    size={expanded ? 'nav-lg' : 'nav'}
                    tone="on-brand"
                    onClick={closeMobile}
                    className="transition-transform duration-300 hover:scale-105"
                />
            </div>

            <nav className="flex flex-1 flex-col gap-1.5 p-2.5">
                {NAV_ITEMS.map((item) => (
                    <AdminNavLink
                        key={item.routeName}
                        item={item}
                        expanded={expanded}
                        onNavigate={closeMobile}
                    />
                ))}
            </nav>

            <div className="border-t border-wayna-600/50 bg-wayna-600/25 p-2.5">
                <div className="flex flex-col gap-1">
                    {userMenuOpen && (
                        <div
                            id="admin-sidebar-user-menu"
                            className="mb-1 flex flex-col overflow-hidden rounded-xl border border-wayna-700/40 bg-wayna-800/90 shadow-inner"
                        >
                            <Link
                                href={route('profile.edit')}
                                className="px-3 py-2.5 text-sm text-white/95 transition hover:bg-wayna-700"
                                onClick={closeMobile}
                            >
                                Perfil
                            </Link>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="w-full border-t border-wayna-700/60 px-3 py-2.5 text-left text-sm text-white/95 transition hover:bg-wayna-700"
                                onClick={closeMobile}
                            >
                                Cerrar sesión
                            </Link>
                        </div>
                    )}
                    <button
                        type="button"
                        aria-expanded={userMenuOpen}
                        aria-controls="admin-sidebar-user-menu"
                        className="group/user flex w-full items-center gap-2 rounded-xl bg-wayna-700/70 px-2.5 py-2.5 text-left text-sm text-white transition hover:bg-wayna-700"
                        onClick={() => setUserMenuOpen((o) => !o)}
                    >
                        <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-xs font-bold uppercase">
                            {user?.name?.charAt(0) ?? 'A'}
                        </span>
                        <span
                            className={`admin-sidebar-nav-label min-w-0 flex-1 truncate font-medium ${
                                expanded ? '!max-w-[12rem] !opacity-100' : ''
                            }`}
                        >
                            {user?.name}
                        </span>
                        <svg
                            className={`h-4 w-4 shrink-0 opacity-80 transition-transform duration-200 ${
                                userMenuOpen ? 'rotate-180' : ''
                            } ${expanded ? '' : 'admin-sidebar-nav-label !max-w-0 !opacity-0'}`}
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
                    onClick={closeMobile}
                />
            )}

            <div className="flex min-h-screen">
                {/* Escritorio: sidebar estrecho → ancho al hover */}
                <aside className="admin-sidebar group/sidebar sticky top-0 z-50 hidden h-screen w-[4.5rem] hover:w-64 lg:flex lg:flex-col">
                    <SidebarContent expanded={false} />
                </aside>

                {/* Móvil: drawer completo */}
                <aside
                    className={`admin-sidebar fixed inset-y-0 left-0 z-50 flex h-full w-64 flex-col transition-transform duration-300 ease-out lg:hidden ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    }`}
                >
                    <SidebarContent expanded />
                </aside>

                <div className="flex min-w-0 flex-1 flex-col">
                    <div className="nav-wayna-bar sticky top-0 z-30 lg:hidden">
                        <div className="flex h-[4.25rem] items-center justify-between gap-3 px-4 sm:h-[4.75rem]">
                            <button
                                type="button"
                                className="rounded-lg p-2 text-white transition hover:bg-wayna-600"
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
