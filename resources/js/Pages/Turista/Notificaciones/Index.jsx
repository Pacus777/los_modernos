import GuestLayout from '@/Layouts/GuestLayout';
import WaynaEnterTransition from '@/Components/Wayna/WaynaEnterTransition';
import AdminPaginator from '@/Components/Admin/AdminPaginator';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function NotificacionesIndex({ notificaciones, no_leidas = 0 }) {
    const { t } = useTranslation();
    const { flash } = usePage().props;
    const filas = notificaciones?.data ?? [];

    const marcarLeida = (id) => {
        router.patch(route('turista.notificaciones.leer', id), {}, { preserveScroll: true });
    };

    const marcarTodasLeidas = () => {
        router.post(route('turista.notificaciones.marcar-todas'));
    };

    return (
        <WaynaEnterTransition variant="navigate">
            <GuestLayout
                variant="full"
                contentClassName="max-w-lg sm:max-w-xl lg:max-w-2xl"
            >
                <Head title="Mis Novedades — WAYNA" />

                <div className="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 pb-4">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-wider text-wayna-600">
                            Novedades in-app
                        </p>
                        <h1 className="text-2xl font-black text-stone-900">
                            Mis Novedades
                        </h1>
                    </div>
                    {no_leidas > 0 && (
                        <button
                            type="button"
                            onClick={marcarTodasLeidas}
                            className="rounded-xl bg-gradient-to-r from-wayna-600 to-wayna-500 px-4 py-2 text-sm font-bold text-white shadow-md transition hover:scale-[1.02] hover:from-wayna-500 hover:to-wayna-450 focus:outline-none"
                        >
                            Marcar todas como leídas ({no_leidas})
                        </button>
                    )}
                </div>

                {flash?.success && (
                    <div className="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-900 shadow-sm">
                        {flash.success}
                    </div>
                )}

                <p className="mb-6 text-sm text-stone-600 leading-relaxed">
                    Aquí encuentras los avisos de nuevas publicaciones y campañas de los emprendedores que sigues.
                </p>

                <div className="rounded-2xl border border-stone-200 bg-white p-5 shadow-lg shadow-wayna-900/5">
                    {filas.length === 0 ? (
                        <div className="py-10 text-center">
                            <svg className="mx-auto h-12 w-12 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4.5m12+3.5h.01" />
                            </svg>
                            <p className="mt-4 text-base font-medium text-stone-500">
                                No tienes novedades registradas.
                            </p>
                            <p className="mt-1 text-sm text-stone-400">
                                Sigue a tus emprendedores favoritos para enterarte cuando publiquen nuevo contenido.
                            </p>
                            <div className="mt-6">
                                <Link
                                    href="/"
                                    className="inline-flex items-center gap-1 text-sm font-bold text-wayna-700 underline decoration-wayna-300 underline-offset-4 hover:text-wayna-900"
                                >
                                    Ir a Explorar →
                                </Link>
                            </div>
                        </div>
                    ) : (
                        <ul className="divide-y divide-stone-100">
                            {filas.map((n) => (
                                <li
                                    key={n.id}
                                    className={`py-4 transition first:pt-0 last:pb-0 ${
                                        !n.leida ? 'rounded-xl bg-orange-50/30 px-3 -mx-3' : ''
                                    }`}
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-4">
                                        <div className="flex-1 min-w-0">
                                            <Link
                                                href={n.url || '#'}
                                                className="block hover:underline"
                                                onClick={() => {
                                                    if (!n.leida) {
                                                        marcarLeida(n.id);
                                                    }
                                                }}
                                            >
                                                <h3 className="text-base font-bold text-wayna-950">
                                                    {n.titulo}
                                                </h3>
                                                <p className="mt-1 text-sm text-stone-700 leading-relaxed">
                                                    {n.mensaje}
                                                </p>
                                            </Link>
                                            <p className="mt-2 text-xs text-stone-500 font-medium">
                                                {n.created_at_humano}
                                            </p>
                                        </div>
                                        {!n.leida && (
                                            <button
                                                type="button"
                                                className="text-xs font-bold text-wayna-750 hover:text-wayna-900 hover:underline shrink-0"
                                                onClick={() => marcarLeida(n.id)}
                                            >
                                                Marcar leída
                                            </button>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                {notificaciones?.links?.length > 3 && (
                    <div className="mt-6 flex justify-center">
                        <AdminPaginator links={notificaciones.links} />
                    </div>
                )}

                <div className="mt-8 text-center">
                    <Link
                        href="/"
                        className="inline-flex items-center gap-1.5 text-sm font-bold text-stone-700 underline decoration-stone-300 underline-offset-4 hover:text-stone-900"
                    >
                        ← Volver a la página principal
                    </Link>
                </div>
            </GuestLayout>
        </WaynaEnterTransition>
    );
}
