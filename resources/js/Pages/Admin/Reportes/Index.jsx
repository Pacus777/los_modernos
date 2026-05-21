import {
    adminBackdropShort,
    adminInputClass,
    adminListCardHeader,
    adminListCardOuter,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

/**
 * Reporte de donaciones.
 *
 * Recibe desde ReporteController@donaciones:
 * - donaciones: paginador Laravel
 * - emprendedores: lista para filtro
 * - estados: pendiente, validado, rechazado
 * - filtros: filtros activos de URL
 *
 * No usa fetch ni axios. Todo llega por props de Inertia.
 */
export default function Index({
    donaciones,
    emprendedores = [],
    estados = [],
    filtros = {},
}) {
    const [emprendedorId, setEmprendedorId] = useState(filtros?.emprendedor_id || '');
    const [estadoPago, setEstadoPago] = useState(filtros?.estado_pago || '');
    const [fechaInicio, setFechaInicio] = useState(filtros?.fecha_inicio || '');
    const [fechaFin, setFechaFin] = useState(filtros?.fecha_fin || '');

    const aplicarFiltros = (e) => {
        e.preventDefault();

        router.get(
            route('admin.reportes.index'),
            {
                emprendedor_id: emprendedorId || undefined,
                estado_pago: estadoPago || undefined,
                fecha_inicio: fechaInicio || undefined,
                fecha_fin: fechaFin || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const limpiarFiltros = () => {
        setEmprendedorId('');
        setEstadoPago('');
        setFechaInicio('');
        setFechaFin('');

        router.get(
            route('admin.reportes.index'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const imprimirReporte = () => {
        window.print();
    };

    const formatearMonto = (monto) => {
        return new Intl.NumberFormat('es-BO', {
            style: 'currency',
            currency: 'BOB',
            minimumFractionDigits: 2,
        }).format(Number(monto || 0));
    };

    const formatearFecha = (fecha) => {
        if (!fecha) {
            return 'Sin fecha';
        }

        return new Date(fecha).toLocaleString('es-BO', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const obtenerNombreEmprendedor = (donacion) => {
        const emprendedor = donacion?.campana?.emprendedor;

        if (!emprendedor) {
            return 'Emprendedor no identificado';
        }

        return `${emprendedor.nombre} ${emprendedor.apellidos}`;
    };

    const obtenerTituloCampana = (donacion) => {
        return donacion?.campana?.titulo || 'Sin campaña';
    };

    const obtenerTipoPago = (donacion) => {
        return donacion?.tipo_pago?.nombre || donacion?.tipoPago?.nombre || donacion?.metodo || 'No definido';
    };

    const obtenerClaseEstado = (estado) => {
        if (estado === 'validado') {
            return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
        }

        if (estado === 'rechazado') {
            return 'bg-red-50 text-red-700 ring-red-200';
        }

        return 'bg-amber-50 text-amber-700 ring-amber-200';
    };

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        Reporte de donaciones
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        Consulta aportes por emprendedor, estado y período. El reporte puede imprimirse desde el navegador.
                    </p>
                </div>
            }
        >
            <Head title="Reportes — Wayna" />

            <style>
                {`
                    @media print {
                        body {
                            background: white !important;
                        }

                        aside,
                        nav,
                        .no-print {
                            display: none !important;
                        }

                        .print-area {
                            box-shadow: none !important;
                            border: none !important;
                        }

                        .print-table {
                            width: 100% !important;
                            font-size: 11px !important;
                        }

                        .print-table th,
                        .print-table td {
                            padding: 6px !important;
                        }

                        @page {
                            margin: 14mm;
                        }
                    }
                `}
            </style>

            <div className="relative py-8">
                <div className={adminBackdropShort} aria-hidden />

                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Filtros */}
                    <div className={`${adminListCardOuter} no-print mb-6`}>
                        <div className={adminListCardHeader}>
                            <h3 className="text-lg font-bold text-wayna-950">
                                Filtros del reporte
                            </h3>
                            <p className="mt-1 text-sm text-stone-600">
                                Combina filtros por emprendedor, estado del pago y rango de fechas.
                            </p>
                        </div>

                        <form
                            onSubmit={aplicarFiltros}
                            className="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-6 lg:items-end"
                        >
                            <div className="lg:col-span-2">
                                <label
                                    htmlFor="emprendedor_id"
                                    className="block text-sm font-bold text-wayna-950"
                                >
                                    Emprendedor
                                </label>

                                <select
                                    id="emprendedor_id"
                                    value={emprendedorId}
                                    onChange={(e) => setEmprendedorId(e.target.value)}
                                    className={`${adminInputClass} mt-1 text-sm`}
                                >
                                    <option value="">Todos</option>

                                    {emprendedores.map((emprendedor) => (
                                        <option key={emprendedor.id} value={emprendedor.id}>
                                            {emprendedor.nombre_completo}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label
                                    htmlFor="estado_pago"
                                    className="block text-sm font-bold text-wayna-950"
                                >
                                    Estado
                                </label>

                                <select
                                    id="estado_pago"
                                    value={estadoPago}
                                    onChange={(e) => setEstadoPago(e.target.value)}
                                    className={`${adminInputClass} mt-1 text-sm`}
                                >
                                    <option value="">Todos</option>

                                    {estados.map((estado) => (
                                        <option key={estado} value={estado}>
                                            {estado}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label
                                    htmlFor="fecha_inicio"
                                    className="block text-sm font-bold text-wayna-950"
                                >
                                    Inicio
                                </label>

                                <input
                                    id="fecha_inicio"
                                    type="date"
                                    value={fechaInicio}
                                    onChange={(e) => setFechaInicio(e.target.value)}
                                    className={`${adminInputClass} mt-1 text-sm`}
                                />
                            </div>

                            <div>
                                <label
                                    htmlFor="fecha_fin"
                                    className="block text-sm font-bold text-wayna-950"
                                >
                                    Fin
                                </label>

                                <input
                                    id="fecha_fin"
                                    type="date"
                                    value={fechaFin}
                                    onChange={(e) => setFechaFin(e.target.value)}
                                    className={`${adminInputClass} mt-1 text-sm`}
                                />
                            </div>

                            <div className="flex gap-2 lg:flex-col">
                                <button
                                    type="submit"
                                    className="flex-1 rounded-xl bg-wayna-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-wayna-800"
                                >
                                    Filtrar
                                </button>

                                <button
                                    type="button"
                                    onClick={limpiarFiltros}
                                    className="flex-1 rounded-xl border border-wayna-200 bg-white px-4 py-2.5 text-sm font-bold text-wayna-800 transition hover:bg-wayna-50"
                                >
                                    Limpiar
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Reporte */}
                    <div className={`${adminListCardOuter} print-area`}>
                        <div className={`${adminListCardHeader} flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between`}>
                            <div>
                                <h3 className="text-lg font-bold text-wayna-950">
                                    Donaciones registradas
                                </h3>
                                <p className="mt-1 text-sm text-stone-600">
                                    Tabla paginada con los aportes registrados en WAYNA.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={imprimirReporte}
                                className="no-print rounded-xl border border-wayna-200 bg-white px-4 py-2 text-sm font-bold text-wayna-800 transition hover:bg-wayna-50"
                            >
                                Imprimir reporte
                            </button>
                        </div>

                        <div className="hidden px-6 pt-6 print:block">
                            <h1 className="text-xl font-bold text-wayna-950">
                                Reporte de donaciones — WAYNA
                            </h1>
                            <p className="mt-1 text-sm text-stone-600">
                                Generado desde el panel administrativo.
                            </p>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="print-table min-w-full divide-y divide-wayna-100">
                                <thead className="bg-wayna-50/70">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-wayna-800">
                                            Fecha
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-wayna-800">
                                            Emprendedor
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-wayna-800">
                                            Campaña
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-wayna-800">
                                            Tipo pago
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-wayna-800">
                                            Estado
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-wayna-800">
                                            Monto
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-wayna-800">
                                            Referencia
                                        </th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-wayna-100 bg-white">
                                    {donaciones?.data?.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan="7"
                                                className="px-6 py-10 text-center text-sm text-stone-600"
                                            >
                                                No hay donaciones registradas con los filtros seleccionados.
                                            </td>
                                        </tr>
                                    )}

                                    {donaciones?.data?.map((donacion) => (
                                        <tr key={donacion.id} className="hover:bg-wayna-50/40">
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-stone-700">
                                                {formatearFecha(donacion.created_at)}
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4">
                                                <p className="text-sm font-bold text-wayna-950">
                                                    {obtenerNombreEmprendedor(donacion)}
                                                </p>
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-stone-700">
                                                {obtenerTituloCampana(donacion)}
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-stone-700">
                                                {obtenerTipoPago(donacion)}
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset ${obtenerClaseEstado(
                                                        donacion.estado_pago
                                                    )}`}
                                                >
                                                    {donacion.estado_pago}
                                                </span>
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-wayna-950">
                                                {formatearMonto(donacion.monto)}
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                <span className="font-mono text-xs font-bold tracking-wide text-wayna-900">
                                                    {donacion.referencia_pago || 'Sin referencia'}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Paginación */}
                        {donaciones?.links && donaciones.links.length > 3 && (
                            <div className="no-print border-t border-wayna-100 bg-wayna-50/40 px-6 py-4 sm:px-8">
                                <div className="flex flex-wrap gap-2">
                                    {donaciones.links.map((link, index) => (
                                        <Link
                                            key={`${link.label}-${index}`}
                                            href={link.url || '#'}
                                            preserveScroll
                                            preserveState
                                            className={`rounded-lg px-3 py-1.5 text-sm font-bold transition ${
                                                link.active
                                                    ? 'bg-wayna-700 text-white'
                                                    : 'bg-white text-wayna-800 ring-1 ring-wayna-200 hover:bg-wayna-50'
                                            } ${
                                                !link.url
                                                    ? 'pointer-events-none cursor-not-allowed opacity-40'
                                                    : ''
                                            }`}
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
        </AdminLayout>
    );
}