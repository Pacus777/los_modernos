import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import AdminRangoMontoBadge from '@/Components/Admin/AdminRangoMontoBadge';
import BarraProgresoMeta from '@/Components/BarraProgresoMeta';
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
import { etiquetaRangoMonto } from '@/utils/rangoMonto';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useEffect } from 'react';

function IconoCampana({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden>
            <path
                stroke="currentColor"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M4 19V5M8 19V10M12 19V8M16 19v-5M20 19V12"
            />
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
 * Listado de campañas — panel admin WAYNA (T-32 / PB-13).
 */
export default function Index({ campanas, filters = {}, rangosMonto = [] }) {
    const { flash } = usePage().props;
    const { requestConfirm, ConfirmDialogPortal } = useConfirmDialog();

    const filterForm = useForm({
        rango_monto: filters.rango_monto ?? '',
    });

    useEffect(() => {
        filterForm.reset({
            rango_monto: filters.rango_monto ?? '',
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filters]);

    const aplicarFiltros = (e) => {
        e.preventDefault();
        filterForm.get(route('admin.campanas.index'), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const limpiarFiltros = () => {
        filterForm.reset({ rango_monto: '' });
        router.get(route('admin.campanas.index'), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const estiloEstado = (estado) => {
        const map = {
            activa: 'bg-wayna-100 text-wayna-900 ring-1 ring-wayna-300/80 shadow-sm shadow-wayna-500/10',
            inactiva: 'bg-stone-100 text-stone-700 ring-1 ring-stone-200',
            finalizada: 'bg-orange-100 text-orange-950 ring-1 ring-orange-200/80',
        };
        return map[estado] || 'bg-stone-100 text-stone-700';
    };

    const pctAvance = (c) => {
        const meta = Number(c.meta_apoyo) || 0;
        const rec = Number(c.monto_recaudado) || 0;
        if (meta <= 0) {
            return 0;
        }
        return Math.min(100, Math.round((rec / meta) * 100));
    };

    const eliminarCampaña = (campana) => {
        const tieneDonaciones = (campana.donaciones_count ?? 0) > 0;

        requestConfirm({
            title: tieneDonaciones ? 'Finalizar campaña' : 'Eliminar campaña',
            message: tieneDonaciones
                ? `La campaña "${campana.titulo}" tiene donaciones registradas. Se marcará como finalizada y se conservará el historial.`
                : `¿Eliminar la campaña "${campana.titulo}"? Solo se permite si no tiene donaciones registradas.`,
            confirmLabel: tieneDonaciones ? 'Sí, finalizar' : 'Sí, eliminar',
            variant: 'danger',
            onConfirm: ({ close }) => {
                close();
                router.delete(route('admin.campanas.destroy', campana.id), {
                    preserveScroll: true,
                });
            },
        });
    };

    const totalLista = campanas?.data?.length ?? 0;

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                            Wayna admin
                        </p>
                        <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                            Campañas de apoyo
                        </h2>
                        <p className="mt-2 max-w-xl text-sm leading-relaxed text-stone-600">
                            Gestioná metas vinculadas a emprendedores. Recordá:{' '}
                            <span className="font-semibold text-wayna-800">
                                solo una campaña activa
                            </span>{' '}
                            por emprendedor.
                        </p>
                    </div>

                    <Link
                        href={route('admin.campanas.create')}
                        className={`group self-start sm:self-auto ${adminPrimaryGradientBtn}`}
                    >
                        <IconoMas className="h-4 w-4 opacity-95" />
                        Nueva campaña
                    </Link>
                </div>
            }
        >
            <Head title="Campañas — Wayna" />

            <div className="relative py-8">
                <div className={adminBackdropShort} aria-hidden />

                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <AdminFlashSuccess message={flash?.success} />

                    <div className="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-wayna-200/80 bg-white/90 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div className="flex items-center gap-2 text-sm text-stone-600">
                            <IconoCampana className="h-5 w-5 text-wayna-600" />
                            <span>
                                Mostrando{' '}
                                <strong className="font-semibold text-wayna-900">{totalLista}</strong>{' '}
                                en esta página
                            </span>
                        </div>
                    </div>

                    <div className={adminListCardOuter}>
                        <form
                            onSubmit={aplicarFiltros}
                            className="border-b border-wayna-100 bg-wayna-50/40 px-4 py-4 sm:px-6"
                        >
                            <p className="text-xs font-semibold uppercase tracking-wide text-wayna-800">
                                Rango de meta (ayuda visual)
                            </p>
                            <p className="mt-1 text-xs text-stone-500">
                                Bajo hasta Bs. 500 · Medio Bs. 501–2.000 · Alto más de Bs. 2.000
                            </p>
                            <div className="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end">
                                <div className="min-w-[14rem] flex-1 sm:max-w-xs">
                                    <label
                                        htmlFor="rango_monto_campanas"
                                        className="block text-xs font-semibold uppercase tracking-wide text-wayna-800"
                                    >
                                        Filtrar por meta
                                    </label>
                                    <select
                                        id="rango_monto_campanas"
                                        value={filterForm.data.rango_monto}
                                        onChange={(e) =>
                                            filterForm.setData('rango_monto', e.target.value)
                                        }
                                        className="mt-1 w-full rounded-lg border border-wayna-200 bg-white px-3 py-2 text-sm"
                                    >
                                        <option value="">Todos los rangos</option>
                                        {rangosMonto.map((opt) => (
                                            <option key={opt.value} value={opt.value}>
                                                {opt.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    <button
                                        type="submit"
                                        disabled={filterForm.processing}
                                        className="rounded-lg bg-wayna-500 px-4 py-2 text-sm font-semibold text-white hover:bg-wayna-700 disabled:opacity-60"
                                    >
                                        Aplicar
                                    </button>
                                    <button
                                        type="button"
                                        disabled={filterForm.processing}
                                        onClick={limpiarFiltros}
                                        className="rounded-lg border border-wayna-200 bg-white px-4 py-2 text-sm font-semibold text-wayna-900 hover:bg-wayna-50 disabled:opacity-60"
                                    >
                                        Limpiar
                                    </button>
                                </div>
                            </div>
                            {filters.rango_monto ? (
                                <p className="mt-2 text-xs text-wayna-800">
                                    Filtro activo:{' '}
                                    <span className="font-semibold">
                                        {etiquetaRangoMonto(filters.rango_monto)}
                                    </span>
                                </p>
                            ) : null}
                        </form>

                        <div className={adminListCardHeader}>
                            <h3 className="text-lg font-bold text-wayna-950">Todas las campañas</h3>
                            <p className="mt-1 text-sm text-stone-600">
                                Editá, revisá el avance respecto a la meta y mantené el estado alineado con el
                                turismo Wayna.
                            </p>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-wayna-100">
                                <thead>
                                    <tr className={adminTableHeadRow}>
                                        <th className="px-6 py-4 sm:px-8">Campaña</th>
                                        <th className="hidden px-4 py-4 md:table-cell">Emprendedor</th>
                                        <th className="px-4 py-4 text-right">Meta</th>
                                        <th className="px-4 py-4 text-right">Recaudado</th>
                                        <th className="px-4 py-4">Estado</th>
                                        <th className="px-6 py-4 text-right sm:px-8">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-wayna-100 bg-white">
                                    {campanas.data.length === 0 && (
                                        <tr>
                                            <td colSpan="6" className="px-6 py-16 text-center sm:px-8">
                                                <div className="mx-auto max-w-md rounded-2xl border border-dashed border-wayna-200 bg-wayna-50/50 px-6 py-10">
                                                    <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-wayna-100 to-surface-muted text-wayna-600">
                                                        <IconoCampana className="h-7 w-7" />
                                                    </div>
                                                    <p className="text-base font-semibold text-wayna-950">
                                                        {filters.rango_monto
                                                            ? 'Sin campañas en este rango'
                                                            : 'Aún no hay campañas'}
                                                    </p>
                                                    <p className="mt-2 text-sm leading-relaxed text-stone-600">
                                                        {filters.rango_monto
                                                            ? 'Probá otro rango de meta o limpiá el filtro.'
                                                            : 'Creá la primera campaña para que los turistas vean la meta en el perfil público.'}
                                                    </p>
                                                    <Link
                                                        href={route('admin.campanas.create')}
                                                        className={`mt-6 inline-flex ${adminPrimaryGradientBtn}`}
                                                    >
                                                        Crear campaña
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    )}

                                    {campanas.data.map((c) => {
                                        const p = pctAvance(c);
                                        return (
                                            <tr
                                                key={c.id}
                                                className={adminTableRowHover}
                                            >
                                                <td className="max-w-xs px-6 py-4 sm:max-w-md sm:px-8">
                                                    <div className="font-semibold text-wayna-950">{c.titulo}</div>
                                                    <div className="mt-2 md:hidden">
                                                        <span className="text-xs text-stone-500">Emprendedor: </span>
                                                        <span className="text-xs font-medium text-stone-700">
                                                            {c.emprendedor
                                                                ? `${c.emprendedor.nombre} ${c.emprendedor.apellidos}`
                                                                : '—'}
                                                        </span>
                                                    </div>
                                                    <div className="mt-3 max-w-md">
                                                        <BarraProgresoMeta
                                                            montoRecaudado={c.monto_recaudado}
                                                            meta={c.meta_apoyo}
                                                            porcentaje={p}
                                                            variant="inline"
                                                        />
                                                    </div>
                                                </td>
                                                <td className="hidden whitespace-nowrap px-4 py-4 text-sm text-stone-700 md:table-cell">
                                                    {c.emprendedor ? (
                                                        <span className="font-medium text-stone-800">
                                                            {c.emprendedor.nombre} {c.emprendedor.apellidos}
                                                        </span>
                                                    ) : (
                                                        <span className="text-stone-400">—</span>
                                                    )}
                                                </td>
                                                <td className="whitespace-nowrap px-4 py-4 text-right">
                                                    <div className="flex flex-col items-end gap-1">
                                                        <span className="font-mono text-sm font-semibold text-wayna-900">
                                                            Bs {Number(c.meta_apoyo).toFixed(2)}
                                                        </span>
                                                        <AdminRangoMontoBadge monto={c.meta_apoyo} />
                                                    </div>
                                                </td>
                                                <td className="whitespace-nowrap px-4 py-4 text-right font-mono text-sm text-stone-700">
                                                    Bs {Number(c.monto_recaudado).toFixed(2)}
                                                </td>
                                                <td className="whitespace-nowrap px-4 py-4">
                                                    <span
                                                        className={`inline-flex rounded-full px-3 py-1 text-xs font-bold capitalize ${estiloEstado(c.estado)}`}
                                                    >
                                                        {c.estado}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right sm:px-8">
                                                    <div className="flex flex-wrap justify-end gap-2">
                                                        <Link
                                                            href={route('admin.campanas.edit', c.id)}
                                                            className={adminTableActionEdit}
                                                        >
                                                            Editar
                                                        </Link>
                                                        <button
                                                            type="button"
                                                            onClick={() => eliminarCampaña(c)}
                                                            className={adminTableActionDanger}
                                                        >
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>

                        {campanas.links && campanas.links.length > 3 && (
                            <div className="border-t border-wayna-100 bg-wayna-50/30 px-6 py-4 sm:px-8">
                                <div className="flex flex-wrap gap-2">
                                    {campanas.links.map((link, index) => (
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
