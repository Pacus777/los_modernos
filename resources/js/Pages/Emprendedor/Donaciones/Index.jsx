import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import AdminPaginator from '@/Components/Admin/AdminPaginator';
import {
    adminFormFooterPrimaryBtn,
    adminFormFooterSecondaryBtn,
    adminInputClass,
    adminListCardOuter,
    adminPrimaryGradientBtn,
    adminTableHeadRow,
    adminTableRowHover,
} from '@/Components/Admin/adminUi';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

const ETIQUETAS_ESTADO = {
    validado: 'Validado',
    pendiente: 'Pendiente',
    rechazado: 'Rechazado',
};

function badgeEstado(estado) {
    const base = 'inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide';
    if (estado === 'validado') {
        return `${base} bg-emerald-100 text-emerald-800`;
    }
    if (estado === 'pendiente') {
        return `${base} bg-amber-100 text-amber-900`;
    }
    return `${base} bg-red-100 text-red-800`;
}

/**
 * E-07 — Historial de donaciones del emprendedor + export CSV.
 */
export default function Index({
    donaciones,
    filtros = {},
    resumen = {},
    rangosMonto = [],
    estadosPago = [],
}) {
    const { flash } = usePage().props;

    const [estadoPago, setEstadoPago] = useState(filtros.estado_pago ?? '');
    const [fechaDesde, setFechaDesde] = useState(filtros.fecha_desde ?? '');
    const [fechaHasta, setFechaHasta] = useState(filtros.fecha_hasta ?? '');
    const [rangoMonto, setRangoMonto] = useState(filtros.rango_monto ?? '');

    const filas = donaciones?.data ?? [];

    const queryFiltros = {
        estado_pago: estadoPago || undefined,
        fecha_desde: fechaDesde || undefined,
        fecha_hasta: fechaHasta || undefined,
        rango_monto: rangoMonto || undefined,
    };

    const aplicarFiltros = (e) => {
        e.preventDefault();
        router.get(route('emprendedor.donaciones.index'), queryFiltros, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const limpiarFiltros = () => {
        setEstadoPago('');
        setFechaDesde('');
        setFechaHasta('');
        setRangoMonto('');
        router.get(route('emprendedor.donaciones.index'));
    };

    const urlExportarExcel = route('emprendedor.donaciones.exportar', queryFiltros);
    const urlExportarPdf = route('emprendedor.donaciones.exportar.pdf', queryFiltros);

    return (
        <EmprendedorLayout
            contentClassName="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8"
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                            Aportes recibidos
                        </p>
                        <h2 className="mt-1 text-2xl font-bold text-wayna-950">
                            Historial de donaciones
                        </h2>
                        <p className="mt-1 text-sm text-stone-600">
                            Consultá y descargá tus aportes en Excel o PDF con diseño WAYNA.
                        </p>
                    </div>
                    <div className="flex shrink-0 flex-col gap-2 sm:flex-row">
                        <a
                            href={urlExportarPdf}
                            className={`text-center ${adminFormFooterSecondaryBtn}`}
                        >
                            Descargar PDF
                        </a>
                        <a
                            href={urlExportarExcel}
                            className={`text-center ${adminPrimaryGradientBtn}`}
                        >
                            Descargar Excel
                        </a>
                    </div>
                </div>
            }
        >
            <Head title="Mis donaciones — WAYNA" />

            <AdminFlashSuccess message={flash?.success} />

            <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-2xl border border-wayna-200/70 bg-white p-4 shadow-sm">
                    <p className="text-[10px] font-bold uppercase tracking-wider text-wayna-700">
                        Registros
                    </p>
                    <p className="mt-2 text-2xl font-black text-wayna-950">
                        {resumen.total_registros ?? 0}
                    </p>
                </div>
                <div className="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 shadow-sm">
                    <p className="text-[10px] font-bold uppercase tracking-wider text-emerald-800">
                        Validados (Bs)
                    </p>
                    <p className="mt-2 text-2xl font-black text-emerald-950">
                        {Number(resumen.total_validado ?? 0).toLocaleString('es-BO')}
                    </p>
                </div>
                <div className="rounded-2xl border border-wayna-100 bg-wayna-50/50 p-4 shadow-sm">
                    <p className="text-[10px] font-bold uppercase tracking-wider text-wayna-800">
                        Cant. validadas
                    </p>
                    <p className="mt-2 text-2xl font-black text-wayna-950">
                        {resumen.cantidad_validadas ?? 0}
                    </p>
                </div>
                <div className="rounded-2xl border border-amber-200/80 bg-amber-50/80 p-4 shadow-sm">
                    <p className="text-[10px] font-bold uppercase tracking-wider text-amber-900">
                        Pendientes
                    </p>
                    <p className="mt-2 text-2xl font-black text-amber-950">
                        {resumen.cantidad_pendientes ?? 0}
                    </p>
                </div>
            </div>

            <div className={`${adminListCardOuter} mb-6`}>
                <form
                    onSubmit={aplicarFiltros}
                    className="grid gap-4 border-b border-wayna-100 p-5 sm:grid-cols-2 lg:grid-cols-5 lg:items-end"
                >
                    <div>
                        <label htmlFor="estado_pago" className="block text-sm font-bold text-wayna-950">
                            Estado
                        </label>
                        <select
                            id="estado_pago"
                            value={estadoPago}
                            onChange={(e) => setEstadoPago(e.target.value)}
                            className={`${adminInputClass} mt-1 text-sm`}
                        >
                            {estadosPago.map((opt) => (
                                <option key={opt.value || 'todos'} value={opt.value}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label htmlFor="fecha_desde" className="block text-sm font-bold text-wayna-950">
                            Desde
                        </label>
                        <input
                            id="fecha_desde"
                            type="date"
                            value={fechaDesde}
                            onChange={(e) => setFechaDesde(e.target.value)}
                            className={`${adminInputClass} mt-1 text-sm`}
                        />
                    </div>
                    <div>
                        <label htmlFor="fecha_hasta" className="block text-sm font-bold text-wayna-950">
                            Hasta
                        </label>
                        <input
                            id="fecha_hasta"
                            type="date"
                            value={fechaHasta}
                            onChange={(e) => setFechaHasta(e.target.value)}
                            className={`${adminInputClass} mt-1 text-sm`}
                        />
                    </div>
                    <div>
                        <label htmlFor="rango_monto" className="block text-sm font-bold text-wayna-950">
                            Monto
                        </label>
                        <select
                            id="rango_monto"
                            value={rangoMonto}
                            onChange={(e) => setRangoMonto(e.target.value)}
                            className={`${adminInputClass} mt-1 text-sm`}
                        >
                            <option value="">Todos</option>
                            {rangosMonto.map((r) => (
                                <option key={r.value} value={r.value}>
                                    {r.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <button type="submit" className={adminFormFooterPrimaryBtn}>
                            Filtrar
                        </button>
                        <button
                            type="button"
                            onClick={limpiarFiltros}
                            className={adminFormFooterSecondaryBtn}
                        >
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <div className={adminListCardOuter}>
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-wayna-100 text-left text-sm">
                        <thead className={adminTableHeadRow}>
                            <tr>
                                <th className="px-4 py-3 font-semibold">ID</th>
                                <th className="px-4 py-3 font-semibold">Fecha</th>
                                <th className="px-4 py-3 font-semibold">Monto</th>
                                <th className="px-4 py-3 font-semibold">Estado</th>
                                <th className="min-w-[8rem] px-4 py-3 font-semibold">Campaña</th>
                                <th className="px-4 py-3 font-semibold">Tipo pago</th>
                                <th className="px-4 py-3 font-semibold">Quien apoyó</th>
                                <th className="min-w-[7rem] px-4 py-3 font-semibold">Referencia</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-wayna-50 bg-white">
                            {filas.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={8}
                                        className="px-4 py-10 text-center text-stone-500"
                                    >
                                        No hay donaciones con los filtros seleccionados.
                                    </td>
                                </tr>
                            ) : (
                                filas.map((row) => (
                                    <tr key={row.id} className={adminTableRowHover}>
                                        <td className="whitespace-nowrap px-4 py-3 font-mono text-xs">
                                            #{row.id}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-stone-700">
                                            {row.fecha_legible ?? '—'}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 font-semibold text-wayna-950">
                                            Bs {Number(row.monto).toLocaleString('es-BO')}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3">
                                            <span className={badgeEstado(row.estado_pago)}>
                                                {ETIQUETAS_ESTADO[row.estado_pago] ?? row.estado_pago}
                                            </span>
                                        </td>
                                        <td className="max-w-[12rem] truncate px-4 py-3">
                                            {row.campana_titulo ?? '—'}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3">
                                            {row.tipo_pago ?? '—'}
                                        </td>
                                        <td className="max-w-[10rem] truncate px-4 py-3">
                                            {row.visitante_nombre ?? '—'}
                                        </td>
                                        <td className="max-w-[8rem] truncate px-4 py-3 font-mono text-xs">
                                            {row.referencia_pago ?? '—'}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
                <AdminPaginator paginator={donaciones} etiqueta="donaciones" />
            </div>
        </EmprendedorLayout>
    );
}
