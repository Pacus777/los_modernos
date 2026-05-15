import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import {
    adminBackdropShort,
    adminListCardHeader,
    adminListCardOuter,
    adminPaginationBtnActive,
    adminPaginationBtnIdle,
    adminPrimaryGradientBtn,
    adminTableActionDanger,
    adminTableActionEdit,
    adminTableHeadRow,
    adminTableRowHover,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { useConfirmDialog } from '@/hooks/useConfirmDialog';
import { Head, Link, router, usePage } from '@inertiajs/react';

function IconoUbicacion({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden>
            <path
                stroke="currentColor"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M12 21s7-4.35 7-10a7 7 0 10-14 0c0 5.65 7 10 7 10z"
            />
            <circle cx="12" cy="11" r="2.5" stroke="currentColor" strokeWidth="1.5" />
        </svg>
    );
}

function IconoMas({ className }) {
    return (
        <svg className={className} viewBox="0 0 20 20" fill="currentColor" aria-hidden>
            <path d="M10 3.25a.75.75 0 01.75.75v5.25H16a.75.75 0 010 1.5h-5.25V16a.75.75 0 01-1.5 0v-5.25H4a.75.75 0 010-1.5h5.25V4a.75.75 0 01.75-.75z" />
        </svg>
    );
}

/**
 * Listado de puntos físicos — T-A28.
 */
export default function Index({ puntos }) {
    const { flash } = usePage().props;
    const { requestConfirm, ConfirmDialogPortal } = useConfirmDialog();

    const estiloEstado = (estado) => {
        const map = {
            activo: 'bg-wayna-100 text-wayna-900 ring-1 ring-wayna-300/80',
            inactivo: 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
        };
        return map[estado] || 'bg-stone-100 text-stone-700';
    };

    const desactivarPunto = (punto) => {
        requestConfirm({
            title: 'Desactivar punto físico',
            message: `¿Desactivar "${punto.nombre}"? La página pública dejará de mostrarse, pero conservamos el registro.`,
            confirmLabel: 'Sí, desactivar',
            variant: 'danger',
            onConfirm: ({ close }) => {
                close();
                router.delete(route('admin.puntos.destroy', punto.id), {
                    preserveScroll: true,
                });
            },
        });
    };

    const totalLista = puntos?.data?.length ?? 0;

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                            Wayna admin
                        </p>
                        <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                            Puntos físicos
                        </h2>
                        <p className="mt-2 max-w-xl text-sm leading-relaxed text-stone-600">
                            Gestioná ubicaciones físicas y qué emprendedores activos aparecen cuando el
                            turista entra por el enlace del punto.
                        </p>
                    </div>

                    <Link
                        href={route('admin.puntos.create')}
                        className={`group self-start sm:self-auto ${adminPrimaryGradientBtn}`}
                    >
                        <IconoMas className="h-4 w-4 opacity-95" />
                        Nuevo punto
                    </Link>
                </div>
            }
        >
            <Head title="Puntos físicos — Wayna" />

            <div className="relative py-8">
                <div className={adminBackdropShort} aria-hidden />

                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <AdminFlashSuccess message={flash?.success} />

                    <div className="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-wayna-200/80 bg-white/90 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div className="flex items-center gap-2 text-sm text-stone-600">
                            <IconoUbicacion className="h-5 w-5 text-wayna-600" />
                            <span>
                                Mostrando{' '}
                                <strong className="font-semibold text-wayna-900">{totalLista}</strong>{' '}
                                en esta página
                            </span>
                        </div>
                    </div>

                    <div className={adminListCardOuter}>
                        <div className={adminListCardHeader}>
                            <h3 className="text-lg font-bold text-wayna-950">Todos los puntos</h3>
                            <p className="mt-1 text-sm text-stone-600">
                                El enlace público (slug) no cambia al editar el nombre.
                            </p>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-wayna-100">
                                <thead>
                                    <tr className={adminTableHeadRow}>
                                        <th className="px-6 py-4 sm:px-8">Punto</th>
                                        <th className="hidden px-4 py-4 lg:table-cell">Ubicación</th>
                                        <th className="px-4 py-4 text-center">Emprendedores</th>
                                        <th className="px-4 py-4">Estado</th>
                                        <th className="px-6 py-4 text-right sm:px-8">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-wayna-100 bg-white">
                                    {puntos.data.length === 0 && (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-16 text-center sm:px-8">
                                                <div className="mx-auto max-w-md rounded-2xl border border-dashed border-wayna-200 bg-wayna-50/50 px-6 py-10">
                                                    <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-wayna-100 to-surface-muted text-wayna-600">
                                                        <IconoUbicacion className="h-7 w-7" />
                                                    </div>
                                                    <p className="text-base font-semibold text-wayna-950">
                                                        Aún no hay puntos físicos
                                                    </p>
                                                    <p className="mt-2 text-sm leading-relaxed text-stone-600">
                                                        Creá el primer punto y asociá emprendedores activos.
                                                    </p>
                                                    <Link
                                                        href={route('admin.puntos.create')}
                                                        className={`mt-6 inline-flex ${adminPrimaryGradientBtn}`}
                                                    >
                                                        Crear punto
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    )}

                                    {puntos.data.map((punto) => (
                                            <tr key={punto.id} className={adminTableRowHover}>
                                                <td className="max-w-xs px-6 py-4 sm:max-w-sm sm:px-8">
                                                    <div className="font-semibold text-wayna-950">
                                                        {punto.nombre}
                                                    </div>
                                                    {punto.slug && (
                                                        <p className="mt-1 font-mono text-xs text-stone-500">
                                                            /punto/{punto.slug}
                                                        </p>
                                                    )}
                                                    <p className="mt-1 text-xs text-stone-500 lg:hidden">
                                                        {punto.ubicacion || 'Sin ubicación'}
                                                    </p>
                                                </td>
                                                <td className="hidden max-w-[12rem] truncate px-4 py-4 text-sm text-stone-700 lg:table-cell">
                                                    {punto.ubicacion || (
                                                        <span className="text-stone-400">—</span>
                                                    )}
                                                </td>
                                                <td className="whitespace-nowrap px-4 py-4 text-center">
                                                    <span className="inline-flex min-w-[2rem] justify-center rounded-full bg-wayna-50 px-2.5 py-1 text-sm font-bold text-wayna-900 ring-1 ring-wayna-200">
                                                        {punto.emprendedores_count ?? 0}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-4 py-4">
                                                    <span
                                                        className={`inline-flex rounded-full px-3 py-1 text-xs font-bold capitalize ${estiloEstado(punto.estado)}`}
                                                    >
                                                        {punto.estado}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right sm:px-8">
                                                    <div className="flex flex-wrap justify-end gap-2">
                                                        <Link
                                                            href={route('admin.puntos.edit', punto.id)}
                                                            className={adminTableActionEdit}
                                                        >
                                                            Editar
                                                        </Link>
                                                        {punto.estado === 'activo' && (
                                                            <button
                                                                type="button"
                                                                onClick={() => desactivarPunto(punto)}
                                                                className={adminTableActionDanger}
                                                            >
                                                                Desactivar
                                                            </button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {puntos.links && puntos.links.length > 3 && (
                            <div className="border-t border-wayna-100 bg-wayna-50/30 px-6 py-4 sm:px-8">
                                <div className="flex flex-wrap gap-2">
                                    {puntos.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            preserveScroll
                                            className={`${link.active ? adminPaginationBtnActive : adminPaginationBtnIdle} ${!link.url ? 'pointer-events-none opacity-40' : ''}`}
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
            <ConfirmDialogPortal />
        </AdminLayout>
    );
}
