import {
    adminListCardOuter,
    adminPaginationBtnActive,
    adminPaginationBtnIdle,
    adminTableHeadRow,
    adminTableRowHover,
} from '@/Components/Admin/adminUi';
import CajeroLayout from '@/Layouts/CajeroLayout';
import { Head, Link, usePage } from '@inertiajs/react';

/**
 * Donaciones en efectivo pendientes de confirmación (T-39, paso 3).
 */
export default function Pendientes({ pendientes }) {
    const { flash } = usePage().props;
    const filas = pendientes?.data ?? [];

    return (
        <CajeroLayout
            header={
                <div className="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-wayna-950">
                            Pendientes en efectivo
                        </h2>
                        <p className="mt-1 text-sm text-stone-600">
                            Aportes registrados que aún no fueron confirmados en
                            caja.
                        </p>
                    </div>
                    <Link
                        href={route('cajero.efectivo')}
                        className="shrink-0 text-sm font-semibold text-wayna-700 underline-offset-2 hover:text-wayna-900 hover:underline"
                    >
                        Volver al panel efectivo
                    </Link>
                </div>
            }
        >
            <Head title="Pendientes en efectivo — WAYNA" />

            <div className="mx-auto max-w-6xl">
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
                                    <th className="min-w-[9rem] px-4 py-3 font-semibold text-wayna-950">
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
                                    <th className="min-w-[7rem] px-4 py-3 font-semibold text-wayna-950">
                                        Acción
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-wayna-100 bg-white">
                                {filas.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={10}
                                            className="px-4 py-10 text-center text-sm text-stone-500"
                                        >
                                            No hay donaciones en efectivo
                                            pendientes.
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
                                                Bs{' '}
                                                {Number(row.monto).toFixed(2)}
                                            </td>
                                            <td className="whitespace-nowrap px-4 py-3 align-top">
                                                <span className="inline-flex rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-900 ring-1 ring-amber-200/80">
                                                    {row.estado_pago}
                                                </span>
                                            </td>
                                            <td className="max-w-[12rem] break-all px-4 py-3 align-top text-stone-700">
                                                <span className="font-mono text-xs font-bold tracking-wide text-wayna-900">
                                                    {row.referencia_pago ?? '—'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 align-top text-stone-800">
                                                {row.campana?.titulo ?? '—'}
                                            </td>
                                            <td className="px-4 py-3 align-top text-stone-800">
                                                {row.campana?.emprendedor
                                                    ?.nombre ?? '—'}
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
                                                <Link
                                                    href={route(
                                                        'cajero.efectivo.confirmar',
                                                        row.id,
                                                    )}
                                                    className="inline-flex rounded-lg bg-wayna-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-wayna-700"
                                                >
                                                    Confirmar
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {pendientes?.links &&
                        pendientes.links.length > 3 && (
                            <div className="border-t border-wayna-100 bg-wayna-50/30 px-4 py-3 sm:px-6">
                                <div className="flex flex-wrap gap-2">
                                    {pendientes.links.map((link, index) => (
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
            </div>
        </CajeroLayout>
    );
}
