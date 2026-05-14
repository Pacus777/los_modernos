import {
    adminListCardOuter,
    adminPaginationBtnActive,
    adminPaginationBtnIdle,
    adminTableHeadRow,
    adminTableRowHover,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/** Metadatos en chips compactos (sin scroll dentro de la celda). */
function MetadatosCell({ meta }) {
    if (meta == null || typeof meta !== 'object' || Array.isArray(meta)) {
        return <span className="text-stone-400">—</span>;
    }

    const etiquetas = {
        evento: 'Evento',
        donacion_id: 'Donación',
        campana_id: 'Campaña',
        monto: 'Monto',
        tipo_pago_id: 'Tipo pago',
        visitante_id: 'Visitante',
        referencia_pago: 'Referencia',
        locale: 'Idioma',
    };

    const orden = [
        'evento',
        'donacion_id',
        'campana_id',
        'monto',
        'tipo_pago_id',
        'visitante_id',
        'referencia_pago',
        'locale',
    ];

    const formatearValor = (clave, valor) => {
        if (valor === null || valor === undefined) {
            return '—';
        }
        if (clave === 'monto') {
            const n = Number(valor);
            return Number.isFinite(n) ? `Bs ${n}` : String(valor);
        }
        if (clave === 'donacion_id') {
            return `#${valor}`;
        }
        if (clave === 'referencia_pago') {
            const s = String(valor);
            return s.length > 28 ? `${s.slice(0, 28)}…` : s;
        }
        if (typeof valor === 'object') {
            return JSON.stringify(valor);
        }
        return String(valor);
    };

    const claves = [
        ...orden.filter((k) => Object.prototype.hasOwnProperty.call(meta, k)),
        ...Object.keys(meta)
            .filter((k) => !orden.includes(k))
            .sort(),
    ];

    return (
        <div className="flex max-w-md flex-wrap gap-1.5">
            {claves.map((k) => {
                const valor = formatearValor(k, meta[k]);
                const label = etiquetas[k] ?? k;
                return (
                    <span
                        key={k}
                        title={`${label}: ${typeof meta[k] === 'object' ? JSON.stringify(meta[k]) : meta[k]}`}
                        className="inline-flex max-w-full items-baseline gap-1 rounded-lg bg-wayna-50/90 px-2 py-1 text-xs text-wayna-950 ring-1 ring-wayna-200/60"
                    >
                        <span className="shrink-0 font-medium text-wayna-700/90">
                            {label}
                        </span>
                        <span className="min-w-0 break-all font-semibold text-wayna-950">
                            {valor}
                        </span>
                    </span>
                );
            })}
        </div>
    );
}

/**
 * Trazabilidad — listado admin (T-36 backend + listado base; T-37 puede refinar UI).
 */
export default function Index({ transacciones, filters }) {
    const { flash } = usePage().props;
    const [local, setLocal] = useState(() => ({ ...filters }));

    useEffect(() => {
        setLocal({ ...filters });
    }, [filters]);

    const aplicarFiltros = (e) => {
        e.preventDefault();
        router.get(route('admin.transacciones.index'), local, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const limpiarFiltros = () => {
        router.get(route('admin.transacciones.index'), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const filas = transacciones?.data ?? [];

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        Trazabilidad
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        Registros de transacciones con UUID. Filtrá por estado, origen y fechas.
                    </p>
                </div>
            }
        >
            <Head title="Trazabilidad — WAYNA" />

            {flash?.success && (
                <div className="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    {flash.success}
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
                                Estado
                            </label>
                            <input
                                type="text"
                                value={local.estado}
                                onChange={(e) =>
                                    setLocal((p) => ({ ...p, estado: e.target.value }))
                                }
                                className="mt-1 w-full rounded-lg border border-wayna-200 px-3 py-2 text-sm"
                                placeholder="ej. pendiente"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold uppercase tracking-wide text-wayna-800">
                                Origen (contiene)
                            </label>
                            <input
                                type="text"
                                value={local.origen}
                                onChange={(e) =>
                                    setLocal((p) => ({ ...p, origen: e.target.value }))
                                }
                                className="mt-1 w-full rounded-lg border border-wayna-200 px-3 py-2 text-sm"
                                placeholder="ej. turista"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold uppercase tracking-wide text-wayna-800">
                                Desde
                            </label>
                            <input
                                type="date"
                                value={local.fecha_desde}
                                onChange={(e) =>
                                    setLocal((p) => ({
                                        ...p,
                                        fecha_desde: e.target.value,
                                    }))
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
                                value={local.fecha_hasta}
                                onChange={(e) =>
                                    setLocal((p) => ({
                                        ...p,
                                        fecha_hasta: e.target.value,
                                    }))
                                }
                                className="mt-1 w-full rounded-lg border border-wayna-200 px-3 py-2 text-sm"
                            />
                        </div>
                    </div>
                    <div className="mt-3 flex flex-wrap gap-2">
                        <button
                            type="submit"
                            className="rounded-lg bg-wayna-600 px-4 py-2 text-sm font-semibold text-white hover:bg-wayna-700"
                        >
                            Aplicar filtros
                        </button>
                        <button
                            type="button"
                            onClick={limpiarFiltros}
                            className="rounded-lg border border-wayna-200 bg-white px-4 py-2 text-sm font-semibold text-wayna-900 hover:bg-wayna-50"
                        >
                            Limpiar
                        </button>
                    </div>
                </form>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-wayna-100 text-left text-sm">
                        <thead className={adminTableHeadRow}>
                            <tr>
                                <th className="min-w-[14rem] px-4 py-3 font-semibold text-wayna-950">
                                    UUID
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Origen
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Destino
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Estado
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Fecha
                                </th>
                                <th className="px-4 py-3 font-semibold text-wayna-950">
                                    Metadatos
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-wayna-100 bg-white">
                            {filas.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-sm text-stone-500"
                                    >
                                        No hay transacciones con los filtros actuales.
                                    </td>
                                </tr>
                            ) : (
                                filas.map((row) => (
                                    <tr key={row.id} className={adminTableRowHover}>
                                        <td className="align-top px-4 py-3">
                                            <span
                                                className="inline-block max-w-[16rem] select-all break-all rounded-md bg-stone-50 px-2 py-1.5 font-mono text-[11px] leading-snug text-stone-900 ring-1 ring-stone-200/80"
                                                title={row.id}
                                            >
                                                {row.id}
                                            </span>
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 align-top text-stone-800">
                                            {row.origen}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 align-top text-stone-800">
                                            {row.destino}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 align-top">
                                            <span className="rounded-full bg-stone-100 px-2 py-1 text-xs font-semibold text-stone-800">
                                                {row.estado}
                                            </span>
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 align-top text-stone-600">
                                            {row.created_at
                                                ? new Date(
                                                      row.created_at,
                                                  ).toLocaleString('es-BO')
                                                : '—'}
                                        </td>
                                        <td className="min-w-[12rem] max-w-sm px-4 py-3 align-top text-xs text-stone-700">
                                            <MetadatosCell meta={row.metadatos} />
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {transacciones?.links && transacciones.links.length > 3 && (
                    <div className="border-t border-wayna-100 bg-wayna-50/30 px-4 py-3 sm:px-6">
                        <div className="flex flex-wrap gap-2">
                            {transacciones.links.map((link, index) => (
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
        </AdminLayout>
    );
}
