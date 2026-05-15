import { adminListCardOuter } from '@/Components/Admin/adminUi';
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

function GraficaVacia({ mensaje }) {
    return (
        <p className="flex h-48 items-center justify-center text-center text-sm text-stone-500">
            {mensaje}
        </p>
    );
}

function TarjetaGrafica({ titulo, descripcion, children }) {
    return (
        <div className={`${adminListCardOuter} p-4 sm:p-5`}>
            <p className="text-sm font-bold text-wayna-950">{titulo}</p>
            {descripcion && (
                <p className="mt-0.5 text-xs text-stone-600">{descripcion}</p>
            )}
            <div className="mt-4 h-52 sm:h-56">{children}</div>
        </div>
    );
}

export default function DashboardGraficas({ graficas = {} }) {
    const evolucion = graficas.evolucion ?? [];
    const campanasEstado = graficas.campanas_por_estado ?? [];
    const porMetodo = graficas.por_metodo ?? [];
    const topCampanas = graficas.top_campanas ?? [];

    const hayEvolucion = evolucion.some((p) => p.monto > 0 || p.aportes > 0);
    const totalCampanasEstado = campanasEstado.reduce((s, c) => s + c.total, 0);
    const totalMetodo = porMetodo.reduce((s, m) => s + m.monto, 0);

    return (
        <section className="space-y-3" aria-label="Gráficas de impacto">
            <TarjetaGrafica
                titulo="Evolución de recaudación"
                descripcion="Monto validado por período (área)"
            >
                {!hayEvolucion ? (
                    <GraficaVacia mensaje="Aún no hay recaudación en este período." />
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <AreaChart data={evolucion} margin={{ top: 8, right: 8, left: 0, bottom: 0 }}>
                            <defs>
                                <linearGradient id="gradRecaudacion" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stopColor="#f07e26" stopOpacity={0.45} />
                                    <stop offset="100%" stopColor="#f07e26" stopOpacity={0.05} />
                                </linearGradient>
                            </defs>
                            <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" vertical={false} />
                            <XAxis
                                dataKey="etiqueta"
                                tick={{ fontSize: 11, fill: '#78716c' }}
                                axisLine={false}
                                tickLine={false}
                            />
                            <YAxis
                                tick={{ fontSize: 11, fill: '#78716c' }}
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
                                strokeWidth={2}
                                fill="url(#gradRecaudacion)"
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                )}
            </TarjetaGrafica>

            <div className="grid gap-3 lg:grid-cols-2">
                <TarjetaGrafica
                    titulo="Aportes por período"
                    descripcion="Cantidad de donaciones validadas (barras)"
                >
                    {!hayEvolucion ? (
                        <GraficaVacia mensaje="Sin aportes registrados." />
                    ) : (
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart data={evolucion} margin={{ top: 8, right: 8, left: 0, bottom: 0 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" vertical={false} />
                                <XAxis
                                    dataKey="etiqueta"
                                    tick={{ fontSize: 11, fill: '#78716c' }}
                                    axisLine={false}
                                    tickLine={false}
                                />
                                <YAxis
                                    allowDecimals={false}
                                    tick={{ fontSize: 11, fill: '#78716c' }}
                                    axisLine={false}
                                    tickLine={false}
                                />
                                <Tooltip content={<TooltipMonto />} />
                                <Bar
                                    dataKey="aportes"
                                    name="Aportes"
                                    fill="#d96d1c"
                                    radius={[6, 6, 0, 0]}
                                    maxBarSize={48}
                                />
                            </BarChart>
                        </ResponsiveContainer>
                    )}
                </TarjetaGrafica>

                <TarjetaGrafica
                    titulo="Campañas por estado"
                    descripcion="Distribución actual del catálogo (dona)"
                >
                    {totalCampanasEstado === 0 ? (
                        <GraficaVacia mensaje="No hay campañas registradas." />
                    ) : (
                        <ResponsiveContainer width="100%" height="100%">
                            <PieChart>
                                <Pie
                                    data={campanasEstado}
                                    dataKey="total"
                                    nameKey="etiqueta"
                                    cx="50%"
                                    cy="50%"
                                    innerRadius="52%"
                                    outerRadius="78%"
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
                                    iconSize={8}
                                    wrapperStyle={{ fontSize: 12 }}
                                />
                            </PieChart>
                        </ResponsiveContainer>
                    )}
                </TarjetaGrafica>

                <TarjetaGrafica
                    titulo="Recaudación por método de pago"
                    descripcion="Participación según canal (dona)"
                >
                    {totalMetodo === 0 ? (
                        <GraficaVacia mensaje="Sin donaciones validadas." />
                    ) : (
                        <ResponsiveContainer width="100%" height="100%">
                            <PieChart>
                                <Pie
                                    data={porMetodo}
                                    dataKey="monto"
                                    nameKey="etiqueta"
                                    cx="50%"
                                    cy="50%"
                                    outerRadius="78%"
                                >
                                    {porMetodo.map((item, i) => (
                                        <Cell
                                            key={item.metodo}
                                            fill={COLORES_WAYNA[i % COLORES_WAYNA.length]}
                                        />
                                    ))}
                                </Pie>
                                <Tooltip
                                    formatter={(value, name) => [formatearMonto(value), name]}
                                />
                                <Legend
                                    iconType="circle"
                                    iconSize={8}
                                    wrapperStyle={{ fontSize: 12 }}
                                />
                            </PieChart>
                        </ResponsiveContainer>
                    )}
                </TarjetaGrafica>

                <TarjetaGrafica
                    titulo="Top campañas por recaudación"
                    descripcion="Las 5 con mayor monto validado (barras horizontales)"
                >
                    {topCampanas.length === 0 ? (
                        <GraficaVacia mensaje="No hay datos de campañas." />
                    ) : (
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart
                                data={topCampanas}
                                layout="vertical"
                                margin={{ top: 4, right: 12, left: 4, bottom: 4 }}
                            >
                                <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" horizontal={false} />
                                <XAxis
                                    type="number"
                                    tick={{ fontSize: 11, fill: '#78716c' }}
                                    axisLine={false}
                                    tickLine={false}
                                    tickFormatter={(v) => `${Math.round(v / 1000)}k`}
                                />
                                <YAxis
                                    type="category"
                                    dataKey="titulo"
                                    width={100}
                                    tick={{ fontSize: 10, fill: '#57534e' }}
                                    axisLine={false}
                                    tickLine={false}
                                />
                                <Tooltip
                                    formatter={(value) => [formatearMonto(value), 'Recaudado']}
                                    labelFormatter={(label) => label}
                                />
                                <Bar
                                    dataKey="monto"
                                    name="Recaudado"
                                    fill="#b55916"
                                    radius={[0, 6, 6, 0]}
                                    maxBarSize={28}
                                />
                            </BarChart>
                        </ResponsiveContainer>
                    )}
                </TarjetaGrafica>
            </div>
        </section>
    );
}
