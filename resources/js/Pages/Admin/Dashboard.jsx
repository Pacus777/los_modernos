import BarraProgresoMeta from '@/Components/BarraProgresoMeta';
import DashboardGraficas from '@/Components/Admin/DashboardGraficas';
import RecaudacionDetalleModal from '@/Components/Admin/RecaudacionDetalleModal';
import {
    adminBackdropShort,
    adminListCardOuter,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

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
 * Dashboard admin compacto (T-A3): métricas principales arriba; detalle bajo demanda.
 */
export default function Dashboard({
    metricas,
    detalleRecaudacion = {},
    progresoCampanas = [],
    mostrarProgresoCampanas = false,
    graficas = {},
    filtros = {},
}) {
    const [fechaInicio, setFechaInicio] = useState(filtros?.fecha_inicio || '');
    const [fechaFin, setFechaFin] = useState(filtros?.fecha_fin || '');
    const [filtrosAbiertos, setFiltrosAbiertos] = useState(
        Boolean(filtros?.fecha_inicio || filtros?.fecha_fin),
    );
    const [progresoAbierto, setProgresoAbierto] = useState(mostrarProgresoCampanas);

    useEffect(() => {
        if (mostrarProgresoCampanas) {
            setProgresoAbierto(true);
        }
    }, [mostrarProgresoCampanas]);
    const [graficasAbiertas, setGraficasAbiertas] = useState(true);
    const [modalRecaudacion, setModalRecaudacion] = useState(false);

    const aplicarFiltros = (e) => {
        e.preventDefault();
        router.get(
            route('admin.dashboard'),
            {
                fecha_inicio: fechaInicio || undefined,
                fecha_fin: fechaFin || undefined,
                ver_progreso: fechaInicio || fechaFin ? 1 : undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const limpiarFiltros = () => {
        setFechaInicio('');
        setFechaFin('');
        setProgresoAbierto(false);
        router.get(route('admin.dashboard'), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };


    



    const obtenerFechaLocal = (fecha) => {
        const year = fecha.getFullYear();
        const month = String(fecha.getMonth() + 1).padStart(2, '0');
        const day = String(fecha.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    const aplicarPeriodoRapido = (periodo) => {
        const hoy = new Date();
        let inicio = '';
        let fin = obtenerFechaLocal(hoy);

        if (periodo === 'hoy') {
            inicio = obtenerFechaLocal(hoy);
        }

        if (periodo === 'semana') {
            const primerDia = new Date(hoy);
            primerDia.setDate(hoy.getDate() - 6);
            inicio = obtenerFechaLocal(primerDia);
        }

        if (periodo === 'mes') {
            const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            inicio = obtenerFechaLocal(primerDia);
        }

        if (periodo === 'todo') {
            setFechaInicio('');
            setFechaFin('');
            setProgresoAbierto(false);

            router.get(
                route('admin.dashboard'),
                {},
                { preserveState: true, preserveScroll: true, replace: true },
            );

            return;
        }

        setFechaInicio(inicio);
        setFechaFin(fin);
        setFiltrosAbiertos(true);

        router.get(
            route('admin.dashboard'),
            {
                fecha_inicio: inicio,
                fecha_fin: fin,
                ver_progreso: 1,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const solicitarProgresoCampanas = () => {
        router.get(
            route('admin.dashboard'),
            {
                fecha_inicio: fechaInicio || filtros?.fecha_inicio || undefined,
                fecha_fin: fechaFin || filtros?.fecha_fin || undefined,
                ver_progreso: 1,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const formatearMonto = (monto) =>
        new Intl.NumberFormat('es-BO', {
            style: 'currency',
            currency: 'BOB',
            minimumFractionDigits: 2,
        }).format(Number(monto || 0));

    const obtenerNombreEmprendedor = (campana) => {
        if (!campana?.emprendedor) {
            return 'Sin emprendedor';
        }
        return `${campana.emprendedor.nombre} ${campana.emprendedor.apellidos}`;
    };

    const tarjetas = [
        {
            id: 'recaudacion',
            label: 'Recaudación total',
            valor: formatearMonto(
                detalleRecaudacion?.total_general ?? metricas?.total_recaudado,
            ),
            hint: 'Clic para ver el desglose',
            className: 'from-white to-wayna-50/70',
            clickeable: true,
        },
        {
            id: 'aportes',
            label: 'Aportes',
            valor: metricas?.numero_aportes ?? 0,
            hint: 'Donaciones validadas',
            className: 'from-white to-surface-muted/60',
        },
        {
            id: 'emprendedores',
            label: 'Emprendedores apoyados',
            valor: metricas?.emprendedores_apoyados ?? 0,
            hint: 'Con al menos un aporte',
            className: 'from-white to-emerald-50/40',
        },
        {
            id: 'campanas',
            label: 'Campañas activas',
            valor: metricas?.campanas_activas ?? 0,
            hint: 'En curso ahora',
            className: 'from-white to-wayna-50/50',
        },
    ];

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        Panel de impacto
                    </h2>
                    <p className="mt-1 max-w-2xl text-sm text-stone-600">
                        Resumen del sistema. Abrí el detalle solo si lo necesitás.
                    </p>
                </div>
            }
        >
            <Head title="Dashboard — Wayna" />

            <div className="relative py-5 sm:py-6">
                <div className={adminBackdropShort} aria-hidden />

                <div className="relative mx-auto max-w-6xl space-y-4 px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        {tarjetas.map((t) => {
                            const contenido = (
                                <>
                                    <p className="text-[10px] font-bold uppercase tracking-wide text-wayna-700">
                                        {t.label}
                                    </p>
                                    <p className="mt-2 text-2xl font-black tracking-tight text-wayna-950 sm:text-3xl">
                                        {t.valor}
                                    </p>
                                    <p className="mt-1 text-xs text-stone-600">{t.hint}</p>
                                </>
                            );

                            if (t.clickeable) {
                                return (
                                    <button
                                        key={t.id}
                                        type="button"
                                        onClick={() => setModalRecaudacion(true)}
                                        className={`rounded-2xl border border-wayna-200/90 bg-gradient-to-br p-4 text-left shadow-sm transition hover:border-wayna-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-wayna-500/40 ${t.className}`}
                                        aria-label="Ver detalle de recaudación total"
                                    >
                                        {contenido}
                                    </button>
                                );
                            }

                            return (
                                <div
                                    key={t.id}
                                    className={`rounded-2xl border border-wayna-200/90 bg-gradient-to-br p-4 shadow-sm ${t.className}`}
                                >
                                    {contenido}
                                </div>
                            );
                        })}
                    </div>

                    <RecaudacionDetalleModal
                        show={modalRecaudacion}
                        onClose={() => setModalRecaudacion(false)}
                        detalle={detalleRecaudacion}
                        filtros={filtros}
                    />

                    <div className={adminListCardOuter}>
                        <button
                            type="button"
                            onClick={() => setGraficasAbiertas((v) => !v)}
                            className="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-wayna-50/50 sm:px-5"
                            aria-expanded={graficasAbiertas}
                        >
                            <div>
                                <p className="text-sm font-bold text-wayna-950">
                                    Gráficas de impacto
                                </p>
                                <p className="text-xs text-stone-600">
                                    Tendencias, métodos de pago y top campañas
                                </p>
                            </div>
                            <span
                                className={`shrink-0 text-wayna-700 transition ${graficasAbiertas ? 'rotate-90' : ''}`}
                            >
                                <IconoFlecha className="h-5 w-5" />
                            </span>
                        </button>
                        {graficasAbiertas && (
                            <div className="border-t border-wayna-100 px-4 py-4 sm:px-5">
                                <DashboardGraficas graficas={graficas} />
                            </div>
                        )}
                    </div>

                    <div className={`${adminListCardOuter} p-4 sm:p-5`}>
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <p className="text-sm font-bold text-wayna-950">
                                    Filtrar por período
                                </p>
                                <p className="mt-0.5 text-xs text-stone-600">
                                    {filtros?.fecha_inicio || filtros?.fecha_fin
                                        ? `${filtros.fecha_inicio || '…'} → ${filtros.fecha_fin || '…'}`
                                        : 'Sin filtro — métricas generales'}
                                </p>

                                <div className="mt-3 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        onClick={() => aplicarPeriodoRapido('hoy')}
                                        className="rounded-full border border-wayna-200 bg-white px-3 py-1.5 text-xs font-bold text-wayna-800 transition hover:bg-wayna-50"
                                    >
                                        Hoy
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => aplicarPeriodoRapido('semana')}
                                        className="rounded-full border border-wayna-200 bg-white px-3 py-1.5 text-xs font-bold text-wayna-800 transition hover:bg-wayna-50"
                                    >
                                        Últimos 7 días
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => aplicarPeriodoRapido('mes')}
                                        className="rounded-full border border-wayna-200 bg-white px-3 py-1.5 text-xs font-bold text-wayna-800 transition hover:bg-wayna-50"
                                    >
                                        Este mes
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => aplicarPeriodoRapido('todo')}
                                        className="rounded-full border border-stone-200 bg-stone-50 px-3 py-1.5 text-xs font-bold text-stone-700 transition hover:bg-stone-100"
                                    >
                                        Todo
                                    </button>
                                </div>
                            </div>

                            <form
                                onSubmit={aplicarFiltros}
                                className="grid gap-3 sm:grid-cols-2 lg:min-w-[34rem] lg:grid-cols-[1fr_1fr_auto_auto] lg:items-end"
                            >
                                <div>
                                    <label
                                        htmlFor="fecha_inicio"
                                        className="block text-xs font-bold text-wayna-950"
                                    >
                                        Desde
                                    </label>
                                    <input
                                        id="fecha_inicio"
                                        type="date"
                                        value={fechaInicio}
                                        onChange={(e) => setFechaInicio(e.target.value)}
                                        className="mt-1 block w-full rounded-xl border-wayna-200 bg-white py-2 text-sm shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="fecha_fin"
                                        className="block text-xs font-bold text-wayna-950"
                                    >
                                        Hasta
                                    </label>
                                    <input
                                        id="fecha_fin"
                                        type="date"
                                        value={fechaFin}
                                        onChange={(e) => setFechaFin(e.target.value)}
                                        className="mt-1 block w-full rounded-xl border-wayna-200 bg-white py-2 text-sm shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    className="rounded-xl bg-wayna-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-wayna-800"
                                >
                                    Aplicar
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
                    </div>

                    <div className={adminListCardOuter}>
                        <div className="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                            <div>
                                <p className="text-sm font-bold text-wayna-950">
                                    Progreso por campaña
                                </p>
                                <p className="mt-0.5 text-xs text-stone-600">
                                    {mostrarProgresoCampanas
                                        ? `${progresoCampanas.length} campaña${progresoCampanas.length === 1 ? '' : 's'} con datos`
                                        : 'Cargá el detalle solo cuando lo necesites'}
                                </p>
                            </div>
                            {!mostrarProgresoCampanas ? (
                                <button
                                    type="button"
                                    onClick={solicitarProgresoCampanas}
                                    className="shrink-0 rounded-xl bg-wayna-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-wayna-800"
                                >
                                    Ver progreso por campañas
                                </button>
                            ) : (
                                <button
                                    type="button"
                                    onClick={() => setProgresoAbierto((v) => !v)}
                                    className="inline-flex shrink-0 items-center gap-1 rounded-xl border border-wayna-200 bg-white px-3 py-2 text-sm font-bold text-wayna-800 transition hover:bg-wayna-50"
                                    aria-expanded={progresoAbierto}
                                >
                                    {progresoAbierto ? 'Ocultar barras' : 'Mostrar barras'}
                                    <IconoFlecha
                                        className={`h-4 w-4 transition ${progresoAbierto ? 'rotate-90' : ''}`}
                                    />
                                </button>
                            )}
                        </div>

                        {mostrarProgresoCampanas && progresoAbierto && (
                            <div className="max-h-[min(24rem,50vh)] divide-y divide-wayna-100 overflow-y-auto border-t border-wayna-100">
                                {progresoCampanas.length === 0 && (
                                    <p className="px-5 py-8 text-center text-sm text-stone-600">
                                        No hay campañas o no hay datos en el período seleccionado.
                                    </p>
                                )}
                                {progresoCampanas.map((campana) => (
                                    <div
                                        key={campana.id}
                                        className="px-4 py-4 sm:px-5"
                                    >
                                        <div className="mb-3 min-w-0">
                                            <p className="text-[10px] font-bold uppercase text-wayna-600">
                                                {campana.estado}
                                            </p>
                                            <p className="truncate text-sm font-bold text-wayna-950">
                                                {campana.titulo}
                                            </p>
                                            <p className="truncate text-xs text-stone-500">
                                                {obtenerNombreEmprendedor(campana)}
                                            </p>
                                        </div>
                                        <BarraProgresoMeta
                                            montoRecaudado={campana.monto_recaudado}
                                            meta={campana.meta_apoyo}
                                            porcentaje={campana.porcentaje}
                                        />
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className={`${adminListCardOuter} p-4 sm:p-5`}>
                        <p className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                            Accesos rápidos
                        </p>
                        <div className="mt-3 flex flex-wrap gap-2">
                            <Link
                                href={route('admin.emprendedores.index')}
                                className="inline-flex items-center gap-1 rounded-xl border border-wayna-200 bg-white px-3 py-2 text-sm font-bold text-wayna-800 transition hover:border-wayna-300 hover:bg-wayna-50"
                            >
                                Emprendedores
                                <IconoFlecha className="h-4 w-4" />
                            </Link>
                            <Link
                                href={route('admin.campanas.index')}
                                className="inline-flex items-center gap-1 rounded-xl border border-wayna-200 bg-white px-3 py-2 text-sm font-bold text-wayna-800 transition hover:border-wayna-300 hover:bg-wayna-50"
                            >
                                Campañas
                                <IconoFlecha className="h-4 w-4" />
                            </Link>
                            <Link
                                href={route('admin.donaciones.index')}
                                className="inline-flex items-center gap-1 rounded-xl border border-wayna-200 bg-white px-3 py-2 text-sm font-bold text-wayna-800 transition hover:border-wayna-300 hover:bg-wayna-50"
                            >
                                Donaciones
                                <IconoFlecha className="h-4 w-4" />
                            </Link>
                            <Link
                                href={route('admin.reportes.index')}
                                className="inline-flex items-center gap-1 rounded-xl border border-wayna-200 bg-white px-3 py-2 text-sm font-bold text-wayna-800 transition hover:border-wayna-300 hover:bg-wayna-50"
                            >
                                Reportes
                                <IconoFlecha className="h-4 w-4" />
                            </Link>
                        </div>
                        <p className="mt-3 text-center text-xs text-stone-600">
                            <Link
                                href={route('admin.emprendedores.create')}
                                className="font-bold text-wayna-700 hover:text-wayna-900"
                            >
                                + Emprendedor
                            </Link>
                            {' · '}
                            <Link
                                href={route('admin.campanas.create')}
                                className="font-bold text-wayna-700 hover:text-wayna-900"
                            >
                                + Campaña
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
