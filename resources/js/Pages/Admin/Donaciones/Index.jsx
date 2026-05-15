import {
    adminListCardOuter,
    adminPaginationBtnActive,
    adminPaginationBtnIdle,
    adminTableHeadRow,
    adminTableRowHover,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const ESTADOS_PAGO = [
    { value: '', label: 'Todos' },
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'validado', label: 'Validado' },
    { value: 'rechazado', label: 'Rechazado' },
];

function badgeEstado(estado) {
    const base =
        'inline-flex rounded-full px-2 py-1 text-xs font-semibold ring-1';
    if (estado === 'pendiente') {
        return `${base} bg-amber-50 text-amber-900 ring-amber-200/80`;
    }
    if (estado === 'validado') {
        return `${base} bg-emerald-50 text-emerald-900 ring-emerald-200/80`;
    }
    if (estado === 'rechazado') {
        return `${base} bg-red-50 text-red-900 ring-red-200/80`;
    }
    return `${base} bg-stone-100 text-stone-800 ring-stone-200/80`;
}

/**
 * Donaciones — panel admin (T-38 paso 3: listado y filtros).
 */
export default function Index({ donaciones, filters }) {
    const { flash } = usePage().props;
    const [accionEnDonacionId, setAccionEnDonacionId] = useState(null);

    const patchAccion = (nombreRuta, donacionId) => {
        setAccionEnDonacionId(donacionId);
        router.patch(
            route(nombreRuta, donacionId),
            {},
            {
                preserveScroll: true,
                onFinish: () => setAccionEnDonacionId(null),
            },
        );
    };

    const filterForm = useForm({
        estado_pago: filters.estado_pago ?? '',
        fecha_desde: filters.fecha_desde ?? '',
        fecha_hasta: filters.fecha_hasta ?? '',
    });

    useEffect(() => {
        filterForm.reset({
            estado_pago: filters.estado_pago ?? '',
            fecha_desde: filters.fecha_desde ?? '',
            fecha_hasta: filters.fecha_hasta ?? '',
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filters]);

    const aplicarFiltros = (e) => {
        e.preventDefault();
        filterForm.get(route('admin.donaciones.index'), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const limpiarFiltros = () => {
        filterForm.reset({
            estado_pago: '',
            fecha_desde: '',
            fecha_hasta: '',
        });
        router.get(route('admin.donaciones.index'), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const filas = donaciones?.data ?? [];

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        Donaciones
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        Revisá aportes por estado de pago y fechas. Las donaciones
                        pendientes pueden validarse o rechazarse desde la tabla.
                    </p>
                </div>
            }
        >
            <Head title="Donaciones — WAYNA" />

            {flash?.success && (
                <div className="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    {flash.success}
                </div>
            )}

            {flash?.error && (
                <div className="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                    {flash.error}
                </div>
            )}

            <div className={adminListCardOuter}>
                <form
                    onSubmit={aplicarFiltros}
                    className="border-b border-wayna-100 bg-wayna-50/40 px-4 py-4 sm:px-6"
                >
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
                        <div>
                            <label className="block text-xs font-semibold uppercase tracking-wide text-wayna-800">
                                Estado de pago
                            </label>
                            <select
                                value={filterForm.data.estado_pago}
                                onChange={(e) =>
                                    filterForm.setData(
                                        'estado_pago',
                                        e.target.value,
                                    )
                                }
                                className="mt-1 w-full rounded-lg border border-wayna-200 bg-white px-3 py-2 text-sm"
                            >
                                {ESTADOS_PAGO.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold uppercase tracking-wide text-wayna-800">
                                Desde
                            </label>
                            <input
                                type="date"
                                value={filterForm.data.fecha_desde}
                                onChange={(e) =>
                                    filterForm.setData(
                                        'fecha_desde',
                                        e.target.value,
                                    )
                                }
                                className="mt-1 w-full rounded-lg border border-wayna-200 px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold uppercase tracking-wide text-wayna-800">
                                Hasta
                            </label>
                            <input
                                type="date"
                                value={filterForm.data.fecha_hasta}
                                onChange={(e) =>
                                    filterForm.setData(
                                        'fecha_hasta',
                                        e.target.value,
                                    )
                                }
                                className="mt-1 w-full rounded-lg border border-wayna-200 px-3 py-2 text-sm"
                            />
                        </div>
                    </div>
                    <div className="mt-3 flex flex-wrap gap-2">
                        <button
                            type="submit"
                            disabled={filterForm.processing}
                            className="rounded-lg bg-wayna-500 px-4 py-2 text-sm font-semibold text-white hover:bg-wayna-700 disabled:opacity-60"
                        >
                            Aplicar filtros
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
                </form>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-wayna-100 text-left text-sm">
                        <thead className={adminTableHeadRow}>
                            <tr>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    ID
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Monto
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Estado
                                </th>
                                <th className="min-w-[8rem] px-4 py-3 font-semibold text-wayna-950">
                                    Referencia
                                </th>
                                <th className="min-w-[10rem] px-4 py-3 font-semibold text-wayna-950">
                                    Campaña
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Emprendedor
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Tipo pago
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Visitante
                                </th>
                                <th className="whitespace-nowrap px-4 py-3 font-semibold text-wayna-950">
                                    Fecha
                                </th>
                                <th className="min-w-[9rem] px-4 py-3 font-semibold text-wayna-950">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-wayna-100 bg-white">
                            {filas.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={10}
                                        className="px-4 py-8 text-center text-sm text-stone-500"
                                    >
                                        No hay donaciones con los filtros
                                        actuales.
                                    </td>
                                </tr>
                            ) : (
                                filas.map((row) => (
                                    <tr
                                        key={row.id}
                                        className={adminTableRowHover}
                                    >
                                        <td className="whitespace-nowrap px-4 py-3 align-top font-mono text-xs text-stone-800">
                                            #{row.id}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 align-top font-semibold text-wayna-950">
                                            Bs {Number(row.monto).toFixed(2)}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 align-top">
                                            <span
                                                className={badgeEstado(
                                                    row.estado_pago,
                                                )}
                                            >
                                                {row.estado_pago}
                                            </span>
                                        </td>
                                        <td className="max-w-[12rem] break-all px-4 py-3 align-top text-stone-700">
                                            {row.referencia_pago ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 align-top text-stone-800">
                                            {row.campana?.titulo ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 align-top text-stone-800">
                                            {row.campana?.emprendedor?.nombre ??
                                                '—'}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 align-top text-stone-700">
                                            {row.tipo_pago?.nombre ?? '—'}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 align-top text-xs text-stone-600">
                                            {row.visitante?.codigo ?? '—'}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 align-top text-stone-600">
                                            {row.created_at
                                                ? new Date(
                                                      row.created_at,
                                                  ).toLocaleString('es-BO')
                                                : '—'}
                                        </td>
                                        <td className="px-4 py-3 align-top">
                                            {row.estado_pago === 'pendiente' ? (
                                                <div className="flex flex-wrap gap-1.5">
                                                    <button
                                                        type="button"
                                                        disabled={
                                                            accionEnDonacionId ===
                                                            row.id
                                                        }
                                                        onClick={() =>
                                                            patchAccion(
                                                                'admin.donaciones.validar',
                                                                row.id,
                                                            )
                                                        }
                                                        className="rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                                                    >
                                                        Validar
                                                    </button>
                                                    <button
                                                        type="button"
                                                        disabled={
                                                            accionEnDonacionId ===
                                                            row.id
                                                        }
                                                        onClick={() =>
                                                            patchAccion(
                                                                'admin.donaciones.rechazar',
                                                                row.id,
                                                            )
                                                        }
                                                        className="rounded-lg border border-red-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-50 disabled:opacity-50"
                                                    >
                                                        Rechazar
                                                    </button>
                                                </div>
                                            ) : (
                                                <span className="text-xs text-stone-400">
                                                    —
                                                </span>
                                            )}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {donaciones?.links && donaciones.links.length > 3 && (
                    <div className="border-t border-wayna-100 bg-wayna-50/30 px-4 py-3 sm:px-6">
                        <div className="flex flex-wrap gap-2">
                            {donaciones.links.map((link, index) => (
                                <Link
                                    key={index}
                                    href={link.url || '#'}
                                    preserveScroll
                                    preserveState
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
        </AdminLayout>
    );
}
