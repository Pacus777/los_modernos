import {
    adminBackdropShort,
    adminListCardHeader,
    adminListCardOuter,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

function IconoFlecha({ className }) {
    return (
        <svg className={className} viewBox="0 0 20 20" fill="currentColor" aria-hidden>
            <path
                fillRule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clipRule="evenodd"
            />
        </svg>
    );
}

/**
 * Panel principal admin WAYNA.
 *
 * Recibe métricas desde ReporteController@impacto mediante Inertia.
 * No usa fetch ni useEffect porque los datos llegan como props.
 */
export default function Dashboard({ metricas, progresoCampanas = [], filtros = {} }) {
    const [fechaInicio, setFechaInicio] = useState(filtros?.fecha_inicio || '');
    const [fechaFin, setFechaFin] = useState(filtros?.fecha_fin || '');

    const aplicarFiltros = (e) => {
        e.preventDefault();

        router.get(
            route('admin.dashboard'),
            {
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
        setFechaInicio('');
        setFechaFin('');

        router.get(
            route('admin.dashboard'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const formatearMonto = (monto) => {
        return new Intl.NumberFormat('es-BO', {
            style: 'currency',
            currency: 'BOB',
            minimumFractionDigits: 2,
        }).format(Number(monto || 0));
    };

    const formatearPorcentaje = (porcentaje) => {
        return `${Number(porcentaje || 0).toFixed(2)}%`;
    };

    const obtenerNombreEmprendedor = (campana) => {
        if (!campana?.emprendedor) {
            return 'Emprendedor no identificado';
        }

        return `${campana.emprendedor.nombre} ${campana.emprendedor.apellidos}`;
    };

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        Dashboard de impacto
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        Revisa el total recaudado, los aportes validados y el avance de las campañas.
                    </p>
                </div>
            }
        >
            <Head title="Dashboard — Wayna" />

            <div className="relative py-8">
                <div className={adminBackdropShort} aria-hidden />

                <div className="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    {/* Filtros */}
                    <div className={`${adminListCardOuter} mb-6`}>
                        <div className={adminListCardHeader}>
                            <h3 className="text-lg font-bold text-wayna-950">Filtro de impacto</h3>
                            <p className="mt-1 text-sm text-stone-600">
                                Consulta las métricas generales o filtra por un período específico.
                            </p>
                        </div>

                        <form
                            onSubmit={aplicarFiltros}
                            className="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4 lg:items-end"
                        >
                            <div>
                                <label
                                    htmlFor="fecha_inicio"
                                    className="block text-sm font-bold text-wayna-950"
                                >
                                    Fecha inicio
                                </label>
                                <input
                                    id="fecha_inicio"
                                    type="date"
                                    value={fechaInicio}
                                    onChange={(e) => setFechaInicio(e.target.value)}
                                    className="mt-1 block w-full rounded-xl border-wayna-200 bg-white text-sm shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                />
                            </div>

                            <div>
                                <label
                                    htmlFor="fecha_fin"
                                    className="block text-sm font-bold text-wayna-950"
                                >
                                    Fecha fin
                                </label>
                                <input
                                    id="fecha_fin"
                                    type="date"
                                    value={fechaFin}
                                    onChange={(e) => setFechaFin(e.target.value)}
                                    className="mt-1 block w-full rounded-xl border-wayna-200 bg-white text-sm shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                />
                            </div>

                            <button
                                type="submit"
                                className="rounded-xl bg-wayna-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-wayna-800"
                            >
                                Aplicar filtro
                            </button>

                            <button
                                type="button"
                                onClick={limpiarFiltros}
                                className="rounded-xl border border-wayna-200 bg-white px-4 py-2.5 text-sm font-bold text-wayna-800 transition hover:bg-wayna-50"
                            >
                                Limpiar
                            </button>
                        </form>
                    </div>

                    {/* Métricas principales */}
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div className="rounded-2xl border border-wayna-200/90 bg-gradient-to-br from-white to-wayna-50/60 p-5 shadow-sm">
                            <p className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                                Recaudación validada
                            </p>
                            <p className="mt-3 text-3xl font-black tracking-tight text-wayna-950">
                                {formatearMonto(metricas?.total_recaudado)}
                            </p>
                            <p className="mt-2 text-sm text-stone-600">
                                Total confirmado por admin o cajero.
                            </p>
                        </div>

                        <div className="rounded-2xl border border-wayna-200/90 bg-gradient-to-br from-white to-surface-muted/50 p-5 shadow-sm">
                            <p className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                                Aportes
                            </p>
                            <p className="mt-3 text-3xl font-black tracking-tight text-wayna-950">
                                {metricas?.numero_aportes || 0}
                            </p>
                            <p className="mt-2 text-sm text-stone-600">
                                Donaciones validadas dentro del sistema.
                            </p>
                        </div>

                        <div className="rounded-2xl border border-wayna-200/90 bg-gradient-to-br from-white to-emerald-50/50 p-5 shadow-sm sm:col-span-2 lg:col-span-1">
                            <p className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                                Emprendedores apoyados
                            </p>
                            <p className="mt-3 text-3xl font-black tracking-tight text-wayna-950">
                                {metricas?.emprendedores_apoyados || 0}
                            </p>
                            <p className="mt-2 text-sm text-stone-600">
                                Emprendedores con al menos un aporte confirmado.
                            </p>
                        </div>
                    </div>

                    {/* Progreso por campaña */}
                    <div className={`${adminListCardOuter} mt-6`}>
                        <div className={adminListCardHeader}>
                            <h3 className="text-lg font-bold text-wayna-950">Progreso por campaña</h3>
                            <p className="mt-1 text-sm text-stone-600">
                                Campañas ordenadas por creación, con avance calculado desde donaciones validadas.
                            </p>
                        </div>

                        <div className="divide-y divide-wayna-100">
                            {progresoCampanas.length === 0 && (
                                <div className="px-6 py-10 text-center text-sm text-stone-600">
                                    No hay campañas registradas o no existen datos para el período seleccionado.
                                </div>
                            )}

                            {progresoCampanas.map((campana) => (
                                <div key={campana.id} className="px-6 py-5 sm:px-8">
                                    <div className="mb-3 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                                                {campana.estado}
                                            </p>
                                            <h4 className="mt-1 text-base font-bold text-wayna-950">
                                                {campana.titulo}
                                            </h4>
                                            <p className="mt-1 text-sm text-stone-600">
                                                {obtenerNombreEmprendedor(campana)}
                                            </p>
                                        </div>

                                        <div className="rounded-xl bg-wayna-50 px-4 py-2 text-left sm:text-right">
                                            <p className="text-sm font-bold text-wayna-950">
                                                {formatearMonto(campana.monto_recaudado)}
                                            </p>
                                            <p className="text-xs text-stone-600">
                                                de {formatearMonto(campana.meta_apoyo)}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="h-3 overflow-hidden rounded-full bg-wayna-100">
                                        <div
                                            className="h-full rounded-full bg-wayna-700 transition-all duration-500"
                                            style={{
                                                width: `${Math.min(
                                                    Number(campana.porcentaje || 0),
                                                    100
                                                )}%`,
                                            }}
                                        />
                                    </div>

                                    <div className="mt-2 flex items-center justify-between text-xs font-semibold text-stone-600">
                                        <span>Avance de campaña</span>
                                        <span>{formatearPorcentaje(campana.porcentaje)}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Accesos rápidos */}
                    <div className={`${adminListCardOuter} mt-6`}>
                        <div className={adminListCardHeader}>
                            <h3 className="text-lg font-bold text-wayna-950">Accesos rápidos</h3>
                            <p className="mt-1 text-sm text-stone-600">
                                Módulos principales para continuar la gestión del sistema.
                            </p>
                        </div>

                        <div className="grid gap-4 p-6 sm:grid-cols-2 sm:p-8">
                            <Link
                                href={route('admin.emprendedores.index')}
                                className="group flex flex-col rounded-2xl border border-wayna-200/90 bg-gradient-to-br from-white to-wayna-50/50 p-5 shadow-sm transition hover:border-wayna-300 hover:shadow-md"
                            >
                                <span className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                                    Directorio
                                </span>
                                <span className="mt-2 text-lg font-bold text-wayna-950">
                                    Emprendedores
                                </span>
                                <span className="mt-1 flex-1 text-sm text-stone-600">
                                    Altas, fotos, meta referencial y QR de perfil.
                                </span>
                                <span className="mt-4 inline-flex items-center gap-1 text-sm font-bold text-wayna-700">
                                    Ir
                                    <IconoFlecha className="h-4 w-4 transition group-hover:translate-x-0.5" />
                                </span>
                            </Link>

                            <Link
                                href={route('admin.campanas.index')}
                                className="group flex flex-col rounded-2xl border border-wayna-200/90 bg-gradient-to-br from-white to-surface-muted/40 p-5 shadow-sm transition hover:border-wayna-300 hover:shadow-md"
                            >
                                <span className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                                    Metas
                                </span>
                                <span className="mt-2 text-lg font-bold text-wayna-950">
                                    Campañas
                                </span>
                                <span className="mt-1 flex-1 text-sm text-stone-600">
                                    Vinculación a emprendedor, estado y fechas de la meta pública.
                                </span>
                                <span className="mt-4 inline-flex items-center gap-1 text-sm font-bold text-wayna-700">
                                    Ir
                                    <IconoFlecha className="h-4 w-4 transition group-hover:translate-x-0.5" />
                                </span>
                            </Link>
                        </div>

                        <div className="border-t border-wayna-100 bg-wayna-50/40 px-6 py-4 sm:px-8">
                            <p className="text-center text-sm text-stone-600">
                                ¿Primera vez?{' '}
                                <Link
                                    href={route('admin.emprendedores.create')}
                                    className="font-bold text-wayna-800 underline decoration-wayna-300 decoration-2 underline-offset-2 hover:text-wayna-950"
                                >
                                    Crear un emprendedor
                                </Link>
                                {' · '}
                                <Link
                                    href={route('admin.campanas.create')}
                                    className="font-bold text-wayna-800 underline decoration-wayna-300 decoration-2 underline-offset-2 hover:text-wayna-950"
                                >
                                    Nueva campaña
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}