import { adminListCardOuter } from '@/Components/Admin/adminUi';
import { useState } from 'react';
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

const COLORES_WAYNA = ['#f07e26', '#d96d1c', '#b55916', '#f3924f', '#914512', '#10b981'];

const COLORES_ESTADO = {
    activa: '#10b981',
    inactiva: '#a8a29e',
    finalizada: '#6366f1',
};

function formatearMonto(valor) {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(valor || 0));
}

function TooltipMonto({ active, payload, label }) {
    if (!active || !payload?.length) {
        return null;
    }

    return (
        <div className="rounded-xl border border-wayna-200 bg-white px-3 py-2 text-xs shadow-lg">
            <p className="font-bold text-wayna-950">{label}</p>
            {payload.map((item) => (
                <p key={item.dataKey} className="mt-0.5 text-stone-600">
                    <span style={{ color: item.color }}>{item.name}: </span>
                    {item.dataKey === 'monto' || item.dataKey === 'valor'
                        ? formatearMonto(item.value)
                        : item.value}
                </p>
            ))}
        </div>
    );
}

function GraficaVacia({ mensaje, grande = false }) {
    return (
        <p
            className={`flex items-center justify-center text-center text-stone-500 ${
                grande ? 'h-80 text-sm' : 'h-36 text-xs'
            }`}
        >
            {mensaje}
        </p>
    );
}

function TarjetaGrafica({ titulo, descripcion, children, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`${adminListCardOuter} min-w-0 p-3 text-left transition hover:-translate-y-0.5 hover:border-wayna-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-wayna-500/40 sm:p-4`}
        >
            <div className="flex min-h-[3rem] items-start justify-between gap-3">
                <div className="min-w-0">
                    <p className="line-clamp-1 text-xs font-bold text-wayna-950 sm:text-sm">
                        {titulo}
                    </p>
                    {descripcion && (
                        <p className="mt-0.5 line-clamp-2 text-[11px] leading-snug text-stone-600">
                            {descripcion}
                        </p>
                    )}
                </div>

                <span className="shrink-0 rounded-full bg-wayna-50 px-2 py-1 text-[10px] font-bold text-wayna-700">
                    Ver
                </span>
            </div>

            <div className="mt-3 h-40 sm:h-44 xl:h-48">
                {children}
            </div>
        </button>
    );
}

function ModalGrafica({ grafica, onClose, children }) {
    if (!grafica) {
        return null;
    }

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="titulo-modal-grafica"
        >
            <div className="w-full max-w-5xl overflow-hidden rounded-3xl border border-wayna-100 bg-white shadow-2xl">
                <div className="flex items-start justify-between gap-4 border-b border-wayna-100 px-5 py-4">
                    <div>
                        <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-wayna-600">
                            Detalle de gráfica
                        </p>
                        <h3
                            id="titulo-modal-grafica"
                            className="mt-1 text-xl font-black text-wayna-950"
                        >
                            {grafica.titulo}
                        </h3>
                        <p className="mt-1 text-sm text-stone-600">
                            {grafica.descripcion}
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-full border border-wayna-200 bg-white px-3 py-1.5 text-sm font-bold text-wayna-800 transition hover:bg-wayna-50"
                        aria-label="Cerrar modal"
                    >
                        Cerrar
                    </button>
                </div>

                <div className="px-5 py-5">
                    <div className="h-[22rem] sm:h-[26rem]">
                        {children}
                    </div>

                    {grafica.detalle ? (
                        <div className="mt-5 rounded-2xl bg-wayna-50/70 p-4 text-sm text-stone-700">
                            <p className="font-bold text-wayna-950">
                                Lectura rápida
                            </p>
                            <p className="mt-1">
                                {grafica.detalle}
                            </p>
                        </div>
                    ) : null}
                </div>
            </div>
        </div>
    );
}

function GraficaEvolucion({ evolucion, hayEvolucion, grande = false }) {
    if (!hayEvolucion) {
        return (
            <GraficaVacia
                grande={grande}
                mensaje="Aún no hay recaudación en este período."
            />
        );
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <AreaChart
                data={evolucion}
                margin={{
                    top: 8,
                    right: grande ? 24 : 8,
                    left: grande ? 0 : -18,
                    bottom: grande ? 16 : 0,
                }}
            >
                <defs>
                    <linearGradient id="gradRecaudacion" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#f07e26" stopOpacity={0.45} />
                        <stop offset="100%" stopColor="#f07e26" stopOpacity={0.05} />
                    </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" vertical={false} />
                <XAxis
                    dataKey="etiqueta"
                    tick={{ fontSize: grande ? 12 : 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                />
                <YAxis
                    tick={{ fontSize: grande ? 12 : 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                    tickFormatter={(v) => `${Math.round(v / 1000)}k`}
                />
                <Tooltip content={<TooltipMonto />} />
                <Area
                    type="monotone"
                    dataKey="monto"
                    name="Recaudado"
                    stroke="#f07e26"
                    strokeWidth={grande ? 3 : 2}
                    fill="url(#gradRecaudacion)"
                />
            </AreaChart>
        </ResponsiveContainer>
    );
}

function GraficaAportes({ evolucion, hayEvolucion, grande = false }) {
    if (!hayEvolucion) {
        return (
            <GraficaVacia
                grande={grande}
                mensaje="Sin aportes registrados."
            />
        );
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <BarChart
                data={evolucion}
                margin={{
                    top: 8,
                    right: grande ? 24 : 8,
                    left: grande ? 0 : -18,
                    bottom: grande ? 16 : 0,
                }}
            >
                <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" vertical={false} />
                <XAxis
                    dataKey="etiqueta"
                    tick={{ fontSize: grande ? 12 : 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                />
                <YAxis
                    allowDecimals={false}
                    tick={{ fontSize: grande ? 12 : 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                />
                <Tooltip content={<TooltipMonto />} />
                <Bar
                    dataKey="aportes"
                    name="Aportes"
                    fill="#d96d1c"
                    radius={[6, 6, 0, 0]}
                    maxBarSize={grande ? 64 : 36}
                />
            </BarChart>
        </ResponsiveContainer>
    );
}

function GraficaMetodoPago({ porMetodo, totalMetodo, grande = false }) {
    if (totalMetodo === 0) {
        return (
            <GraficaVacia
                grande={grande}
                mensaje="Sin donaciones validadas."
            />
        );
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <PieChart>
                <Pie
                    data={porMetodo}
                    dataKey="monto"
                    nameKey="etiqueta"
                    cx="50%"
                    cy={grande ? '50%' : '46%'}
                    outerRadius={grande ? '78%' : '68%'}
                >
                    {porMetodo.map((item, i) => (
                        <Cell
                            key={item.metodo}
                            fill={COLORES_WAYNA[i % COLORES_WAYNA.length]}
                        />
                    ))}
                </Pie>
                <Tooltip
                    formatter={(value, name) => [
                        formatearMonto(value),
                        name,
                    ]}
                />
                <Legend
                    iconType="circle"
                    iconSize={grande ? 9 : 7}
                    wrapperStyle={{ fontSize: grande ? 12 : 10 }}
                />
            </PieChart>
        </ResponsiveContainer>
    );
}

function GraficaTopCampanas({ topCampanas, grande = false }) {
    if (topCampanas.length === 0) {
        return (
            <GraficaVacia
                grande={grande}
                mensaje="No hay datos de campañas."
            />
        );
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <BarChart
                data={topCampanas}
                layout="vertical"
                margin={{
                    top: 4,
                    right: grande ? 24 : 10,
                    left: grande ? 24 : -12,
                    bottom: 4,
                }}
            >
                <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" horizontal={false} />
                <XAxis
                    type="number"
                    tick={{ fontSize: grande ? 12 : 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                    tickFormatter={(v) => `${Math.round(v / 1000)}k`}
                />
                <YAxis
                    type="category"
                    dataKey="titulo"
                    width={grande ? 150 : 78}
                    tick={{ fontSize: grande ? 12 : 9, fill: '#57534e' }}
                    axisLine={false}
                    tickLine={false}
                />
                <Tooltip
                    formatter={(value) => [
                        formatearMonto(value),
                        'Recaudado',
                    ]}
                    labelFormatter={(label) => label}
                />
                <Bar
                    dataKey="monto"
                    name="Recaudado"
                    fill="#b55916"
                    radius={[0, 6, 6, 0]}
                    maxBarSize={grande ? 34 : 22}
                />
            </BarChart>
        </ResponsiveContainer>
    );
}

function GraficaCampanasEstado({ campanasEstado, totalCampanasEstado, grande = false }) {
    if (totalCampanasEstado === 0) {
        return (
            <GraficaVacia
                grande={grande}
                mensaje="No hay campañas registradas."
            />
        );
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <PieChart>
                <Pie
                    data={campanasEstado}
                    dataKey="total"
                    nameKey="etiqueta"
                    cx="50%"
                    cy="50%"
                    innerRadius={grande ? '50%' : '48%'}
                    outerRadius={grande ? '76%' : '68%'}
                    paddingAngle={3}
                >
                    {campanasEstado.map((item) => (
                        <Cell
                            key={item.estado}
                            fill={COLORES_ESTADO[item.estado] ?? '#a8a29e'}
                        />
                    ))}
                </Pie>
                <Tooltip
                    formatter={(value, name) => [`${value} campaña(s)`, name]}
                />
                <Legend
                    iconType="circle"
                    iconSize={grande ? 9 : 7}
                    wrapperStyle={{ fontSize: grande ? 12 : 10 }}
                />
            </PieChart>
        </ResponsiveContainer>
    );
}

export default function DashboardGraficas({ graficas = {} }) {
    const [graficaActiva, setGraficaActiva] = useState(null);

    const evolucion = graficas.evolucion ?? [];
    const campanasEstado = graficas.campanas_por_estado ?? [];
    const porMetodo = graficas.por_metodo ?? [];
    const topCampanas = graficas.top_campanas ?? [];

    const hayEvolucion = evolucion.some((p) => p.monto > 0 || p.aportes > 0);
    const totalCampanasEstado = campanasEstado.reduce((s, c) => s + c.total, 0);
    const totalMetodo = porMetodo.reduce((s, m) => s + m.monto, 0);

    const graficasDisponibles = [
        {
            id: 'evolucion',
            titulo: 'Evolución de recaudación',
            descripcion: 'Monto validado por período',
            detalle:
                'Permite observar cómo evoluciona la recaudación validada en el tiempo. Ayuda a identificar períodos con mayor o menor apoyo económico.',
            mini: (
                <GraficaEvolucion
                    evolucion={evolucion}
                    hayEvolucion={hayEvolucion}
                />
            ),
            grande: (
                <GraficaEvolucion
                    evolucion={evolucion}
                    hayEvolucion={hayEvolucion}
                    grande
                />
            ),
        },
        {
            id: 'metodo',
            titulo: 'Recaudación por método',
            descripcion: 'Participación según canal',
            detalle:
                'Compara los métodos de pago utilizados por los donantes. Ayuda a decidir qué canales conviene priorizar o mejorar.',
            mini: (
                <GraficaMetodoPago
                    porMetodo={porMetodo}
                    totalMetodo={totalMetodo}
                />
            ),
            grande: (
                <GraficaMetodoPago
                    porMetodo={porMetodo}
                    totalMetodo={totalMetodo}
                    grande
                />
            ),
        },
        {
            id: 'top',
            titulo: 'Top campañas',
            descripcion: 'Mayor monto validado',
            detalle:
                'Presenta las campañas con mayor recaudación validada. Permite identificar qué historias, emprendedores o metas generan mayor respuesta en los donantes.',
            mini: (
                <GraficaTopCampanas
                    topCampanas={topCampanas}
                />
            ),
            grande: (
                <GraficaTopCampanas
                    topCampanas={topCampanas}
                    grande
                />
            ),
        },
    ];

    const graficaSeleccionada = graficasDisponibles.find(
        (grafica) => grafica.id === graficaActiva,
    );

    return (
        <>
            <section
                className="grid gap-3 md:grid-cols-3"
                aria-label="Gráficas de impacto"
            >
                {graficasDisponibles.map((grafica) => (
                    <TarjetaGrafica
                        key={grafica.id}
                        titulo={grafica.titulo}
                        descripcion={grafica.descripcion}
                        onClick={() => setGraficaActiva(grafica.id)}
                    >
                        {grafica.mini}
                    </TarjetaGrafica>
                ))}
            </section>

            <ModalGrafica
                grafica={graficaSeleccionada}
                onClose={() => setGraficaActiva(null)}
            >
                {graficaSeleccionada?.grande}
            </ModalGrafica>
        </>
    );
}