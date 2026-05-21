import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

function formatearMonto(valor) {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(valor || 0));
}

function TooltipWayna({ active, payload, label }) {
    if (!active || !payload?.length) {
        return null;
    }

    return (
        <div className="rounded-xl border border-wayna-200 bg-white px-3 py-2 text-xs shadow-lg">
            <p className="font-bold text-wayna-950">{label}</p>
            {payload.map((item) => (
                <p key={item.dataKey} className="mt-0.5 text-stone-600">
                    <span style={{ color: item.color }}>{item.name}: </span>
                    {item.dataKey === 'monto'
                        ? formatearMonto(item.value)
                        : Number(item.value ?? 0).toLocaleString('es-BO')}
                </p>
            ))}
        </div>
    );
}

function GraficaVacia({ mensaje }) {
    return (
        <div className="flex h-56 items-center justify-center rounded-2xl bg-stone-50 text-center text-sm text-stone-500">
            {mensaje}
        </div>
    );
}

function tieneDatos(datos, claves) {
    return datos.some((fila) =>
        claves.some((clave) => Number(fila?.[clave] ?? 0) > 0),
    );
}

function TarjetaGrafica({ titulo, descripcion, children }) {
    return (
        <article className="rounded-3xl border border-wayna-200 bg-white p-4 shadow-sm ring-1 ring-black/[0.02]">
            <div className="mb-4">
                <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-wayna-600">
                    Estadística
                </p>
                <h3 className="mt-1 text-sm font-black text-wayna-950">
                    {titulo}
                </h3>
                <p className="mt-1 text-xs leading-relaxed text-stone-500">
                    {descripcion}
                </p>
            </div>

            <div className="h-56 w-full min-w-0">
                {children}
            </div>
        </article>
    );
}

function GraficaAportes({ datos }) {
    if (!tieneDatos(datos, ['monto', 'aportes'])) {
        return <GraficaVacia mensaje="Aún no hay aportes validados en los últimos 7 días." />;
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <AreaChart
                data={datos}
                margin={{ top: 8, right: 12, left: -14, bottom: 0 }}
            >
                <defs>
                    <linearGradient id="gradAportesEmprendedor" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#f07e26" stopOpacity={0.45} />
                        <stop offset="100%" stopColor="#f07e26" stopOpacity={0.05} />
                    </linearGradient>
                </defs>
                <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" vertical={false} />
                <XAxis
                    dataKey="etiqueta"
                    tick={{ fontSize: 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                />
                <YAxis
                    tick={{ fontSize: 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                    tickFormatter={(v) => `${Math.round(v / 1000)}k`}
                />
                <Tooltip content={<TooltipWayna />} />
                <Area
                    type="monotone"
                    dataKey="monto"
                    name="Monto validado"
                    stroke="#f07e26"
                    strokeWidth={2}
                    fill="url(#gradAportesEmprendedor)"
                />
            </AreaChart>
        </ResponsiveContainer>
    );
}

function GraficaContenido({ datos }) {
    if (!tieneDatos(datos, ['publicaciones', 'reacciones'])) {
        return <GraficaVacia mensaje="Aún no hay publicaciones ni reacciones recientes." />;
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <BarChart
                data={datos}
                margin={{ top: 8, right: 12, left: -14, bottom: 0 }}
            >
                <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" vertical={false} />
                <XAxis
                    dataKey="etiqueta"
                    tick={{ fontSize: 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                />
                <YAxis
                    allowDecimals={false}
                    tick={{ fontSize: 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                />
                <Tooltip content={<TooltipWayna />} />
                <Legend iconType="circle" iconSize={8} wrapperStyle={{ fontSize: 11 }} />
                <Bar
                    dataKey="publicaciones"
                    name="Publicaciones"
                    fill="#f07e26"
                    radius={[6, 6, 0, 0]}
                    maxBarSize={34}
                />
                <Bar
                    dataKey="reacciones"
                    name="Reacciones"
                    fill="#10b981"
                    radius={[6, 6, 0, 0]}
                    maxBarSize={34}
                />
            </BarChart>
        </ResponsiveContainer>
    );
}

function GraficaSeguidores({ datos }) {
    if (!tieneDatos(datos, ['seguidores'])) {
        return <GraficaVacia mensaje="Aún no hay nuevos seguidores en los últimos 7 días." />;
    }

    return (
        <ResponsiveContainer width="100%" height="100%">
            <LineChart
                data={datos}
                margin={{ top: 8, right: 12, left: -14, bottom: 0 }}
            >
                <CartesianGrid strokeDasharray="3 3" stroke="#e7e5e4" vertical={false} />
                <XAxis
                    dataKey="etiqueta"
                    tick={{ fontSize: 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                />
                <YAxis
                    allowDecimals={false}
                    tick={{ fontSize: 10, fill: '#78716c' }}
                    axisLine={false}
                    tickLine={false}
                />
                <Tooltip content={<TooltipWayna />} />
                <Line
                    type="monotone"
                    dataKey="seguidores"
                    name="Seguidores"
                    stroke="#d96d1c"
                    strokeWidth={3}
                    dot={{ r: 3 }}
                    activeDot={{ r: 5 }}
                />
            </LineChart>
        </ResponsiveContainer>
    );
}

export default function EmprendedorDashboardGraficas({ graficas = {} }) {
    const aportes = graficas?.aportes_por_dia ?? [];
    const contenido = graficas?.contenido_por_dia ?? [];
    const seguidores = graficas?.seguidores_por_dia ?? [];
    const periodo = graficas?.periodo?.dias ?? 7;

    return (
        <section className="space-y-4" aria-label="Estadísticas del emprendedor">
            <div>
                <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-wayna-600">
                    Estadísticas recientes
                </p>
                <h2 className="mt-1 text-lg font-black text-wayna-950">
                    Movimiento de los últimos {periodo} días
                </h2>
                <p className="mt-1 text-sm text-stone-500">
                    Resumen visual de aportes, publicaciones, reacciones y seguidores.
                </p>
            </div>

            <div className="grid gap-4 xl:grid-cols-3">
                <TarjetaGrafica
                    titulo="Aportes validados"
                    descripcion="Monto confirmado por día."
                >
                    <GraficaAportes datos={aportes} />
                </TarjetaGrafica>

                <TarjetaGrafica
                    titulo="Publicaciones y reacciones"
                    descripcion="Actividad e interacción generada por tus posts."
                >
                    <GraficaContenido datos={contenido} />
                </TarjetaGrafica>

                <TarjetaGrafica
                    titulo="Nuevos seguidores"
                    descripcion="Crecimiento reciente de tu perfil público."
                >
                    <GraficaSeguidores datos={seguidores} />
                </TarjetaGrafica>
            </div>
        </section>
    );
}