import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import AdminPaginator from '@/Components/Admin/AdminPaginator';
import { adminListCardOuter, adminPrimaryGradientBtn } from '@/Components/Admin/adminUi';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';

export default function NotificacionesIndex({ notificaciones, no_leidas = 0 }) {
    const { flash } = usePage().props;
    const filas = notificaciones?.data ?? [];

    return (
        <EmprendedorLayout
            header={
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p className="text-xs font-bold uppercase tracking-wider text-wayna-600">
                            Centro de avisos
                        </p>
                        <h1 className="text-xl font-black text-stone-900 sm:text-2xl">
                            Notificaciones en el panel
                        </h1>
                    </div>
                    {no_leidas > 0 && (
                        <button
                            type="button"
                            className={`${adminPrimaryGradientBtn} text-sm`}
                            onClick={() =>
                                router.post(route('emprendedor.notificaciones.marcar-todas'))
                            }
                        >
                            Marcar todas como leídas ({no_leidas})
                        </button>
                    )}
                </div>
            }
        >
            <Head title="Mis avisos — WAYNA" />
            <AdminFlashSuccess message={flash?.success} />

            <p className="mb-4 text-base text-stone-800">
                Aquí ves los aportes validados que suman a tu recaudación. Podés cambiar cómo te
                avisamos en{' '}
                <Link
                    href={route('emprendedor.preferencias.edit')}
                    className="font-semibold text-wayna-800 underline decoration-wayna-300 underline-offset-2"
                >
                    preferencias
                </Link>
                .
            </p>

            <div className={adminListCardOuter}>
                {filas.length === 0 ? (
                    <p className="py-8 text-center text-base text-stone-600">
                        Todavía no hay avisos. Cuando un aporte se valide, aparecerá aquí.
                    </p>
                ) : (
                    <ul className="divide-y divide-stone-100">
                        {filas.map((n) => (
                            <li
                                key={n.id}
                                className={`py-4 first:pt-0 last:pb-0 ${
                                    !n.leida ? 'rounded-lg bg-amber-50/50 px-3 -mx-3' : ''
                                }`}
                            >
                                <div className="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p className="text-base font-bold text-stone-900">{n.titulo}</p>
                                        <p className="mt-1 text-base leading-relaxed text-stone-800">
                                            {n.mensaje}
                                        </p>
                                        <p className="mt-2 text-sm text-stone-500">
                                            {n.created_at_humano}
                                        </p>
                                    </div>
                                    {!n.leida && (
                                        <button
                                            type="button"
                                            className="shrink-0 text-sm font-semibold text-wayna-800 hover:underline"
                                            onClick={() =>
                                                router.patch(
                                                    route('emprendedor.notificaciones.leer', n.id),
                                                )
                                            }
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
                <div className="mt-6">
                    <AdminPaginator links={notificaciones.links} />
                </div>
            )}
        </EmprendedorLayout>
    );
}
