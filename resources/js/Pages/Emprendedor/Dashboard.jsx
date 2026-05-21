import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import BarraProgreso from '@/Components/Turista/BarraProgreso';
import LogoutButton from '@/Components/LogoutButton';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import { Head, Link, usePage } from '@inertiajs/react';

const ETIQUETAS_ESTADO = {
    activo: 'Activo',
    inactivo: 'Inactivo',
    pendiente: 'Pendiente',
};

const ETIQUETAS_DONACION = {
    validado: 'Validado',
    pendiente: 'Pendiente',
    rechazado: 'Rechazado',
};

function TarjetaResumen({ titulo, valor, detalle, acento = 'wayna' }) {
    const acentos = {
        wayna: 'border-wayna-200 bg-wayna-50/60 text-wayna-950',
        emerald: 'border-emerald-200 bg-emerald-50/80 text-emerald-950',
        amber: 'border-amber-200 bg-amber-50/80 text-amber-950',
        stone: 'border-stone-200 bg-stone-50/90 text-stone-900',
    };

    return (
        <div
            className={`rounded-2xl border px-4 py-4 shadow-sm ${acentos[acento] ?? acentos.wayna}`}
        >
            <p className="text-[10px] font-bold uppercase tracking-[0.18em] opacity-80">
                {titulo}
            </p>
            <p className="mt-2 text-2xl font-black tabular-nums">{valor}</p>
            {detalle ? <p className="mt-1 text-xs opacity-80">{detalle}</p> : null}
        </div>
    );
}

function formatearFecha(iso) {
    if (!iso) {
        return '—';
    }

    return new Date(iso).toLocaleString('es-BO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

/**
 * E-05 — Panel principal del emprendedor (/emprendedor/dashboard).
 */
export default function Dashboard({ panel, usuario }) {
    const { flash } = usePage().props;
    const perfil = panel?.perfil;
    const progreso = panel?.progreso;
    const stats = panel?.estadisticas;
    const campana = panel?.campana_activa;

    return (
        <EmprendedorLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Mi panel
                    </p>
                    <h2 className="mt-1 text-xl font-bold text-wayna-950 sm:text-2xl">
                        Hola, {usuario.name}
                    </h2>
                </div>
            }
        >
            <Head title="Mi panel — WAYNA" />

            <div className="mx-auto max-w-4xl space-y-8">
                <AdminFlashSuccess message={flash?.success} />

                {panel && perfil ? (
                    <>
                        <section className="overflow-hidden rounded-3xl border border-wayna-200 bg-white shadow-lg ring-1 ring-black/[0.03]">
                            <div className="header-wayna-gradient px-6 py-5 sm:flex sm:items-center sm:gap-6">
                                {perfil.foto_portada ? (
                                    <img
                                        src={perfil.foto_portada}
                                        alt=""
                                        className="mb-4 h-20 w-20 shrink-0 rounded-2xl border-2 border-white/40 object-cover shadow-md sm:mb-0"
                                    />
                                ) : (
                                    <div
                                        className="mb-4 flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl border-2 border-white/30 bg-white/10 text-2xl font-black text-white sm:mb-0"
                                        aria-hidden
                                    >
                                        {perfil.nombre_completo?.charAt(0) ?? '?'}
                                    </div>
                                )}
                                <div className="min-w-0 flex-1">
                                    <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-100/95">
                                        Tu perfil en WAYNA
                                    </p>
                                    <p className="mt-2 text-lg font-bold text-white sm:text-xl">
                                        {perfil.nombre_completo}
                                    </p>
                                    <p className="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-sm text-orange-50/90">
                                        <span>
                                            Estado:{' '}
                                            <span className="font-semibold text-white">
                                                {ETIQUETAS_ESTADO[perfil.estado] ?? perfil.estado}
                                            </span>
                                        </span>
                                        {perfil.tipo_emprendimiento ? (
                                            <span>{perfil.tipo_emprendimiento}</span>
                                        ) : null}
                                        {perfil.departamento ? (
                                            <span>{perfil.departamento}</span>
                                        ) : null}
                                    </p>
                                </div>
                            </div>

                            {perfil.descripcion_resumen ? (
                                <p className="border-t border-wayna-100 px-6 py-4 text-sm text-stone-600">
                                    {perfil.descripcion_resumen}
                                </p>
                            ) : null}

                            <div className="flex flex-wrap gap-3 border-t border-wayna-100 px-6 py-4">
                                <Link
                                    href={route('emprendedor.perfil.edit')}
                                    className="inline-flex items-center justify-center rounded-2xl bg-wayna-600 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-wayna-700"
                                >
                                    Editar perfil público
                                </Link>
                                <Link
                                    href={panel.acciones.perfil_publico_url}
                                    className="inline-flex items-center justify-center rounded-2xl border border-wayna-200 bg-white px-5 py-2.5 text-sm font-bold text-wayna-800 transition hover:border-wayna-300 hover:bg-wayna-50"
                                >
                                    Ver perfil público
                                </Link>
                                {panel.acciones.qr_url ? (
                                    <a
                                        href={panel.acciones.qr_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center justify-center rounded-2xl border border-wayna-200 bg-white px-5 py-2.5 text-sm font-bold text-wayna-800 transition hover:border-wayna-300 hover:bg-wayna-50"
                                    >
                                        Descargar mi QR
                                    </a>
                                ) : null}
                            </div>
                        </section>

                        <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <TarjetaResumen
                                titulo="Recaudado (validado)"
                                valor={`Bs ${Number(stats?.donaciones_validadas_total ?? 0).toLocaleString('es-BO')}`}
                                detalle={`${stats?.donaciones_validadas_cantidad ?? 0} aporte(s)`}
                                acento="emerald"
                            />
                            <TarjetaResumen
                                titulo="Pendientes de validar"
                                valor={stats?.donaciones_pendientes_cantidad ?? 0}
                                detalle="Efectivo u otros métodos manuales"
                                acento="amber"
                            />
                            <TarjetaResumen
                                titulo="Seguidores"
                                valor={stats?.seguidores ?? 0}
                                detalle="Visitantes que te siguen"
                            />
                            <TarjetaResumen
                                titulo="Puntos en el mapa"
                                valor={stats?.puntos ?? 0}
                                detalle={
                                    (stats?.publicaciones ?? 0) > 0
                                        ? `${stats.publicaciones} publicación(es)`
                                        : 'Visibilidad en explorar'
                                }
                                acento="stone"
                            />
                        </section>

                        {campana ? (
                            <section>
                                <BarraProgreso
                                    porcentaje={progreso?.porcentaje ?? 0}
                                    montoRecaudado={progreso?.monto_recaudado ?? 0}
                                    meta={progreso?.meta ?? 0}
                                    titulo={campana.titulo}
                                />
                                {campana.fecha_fin ? (
                                    <p className="mt-2 text-center text-xs text-stone-500">
                                        Campaña activa hasta{' '}
                                        {new Date(campana.fecha_fin).toLocaleDateString('es-BO', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric',
                                        })}
                                    </p>
                                ) : null}
                            </section>
                        ) : Number(progreso?.meta ?? 0) > 0 ? (
                            <div className="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-950">
                                Meta de referencia registrada:{' '}
                                <strong>
                                    Bs {Number(progreso.meta).toLocaleString('es-BO')}
                                </strong>
                                . Cuando el admin active una campaña, el progreso se mostrará acá.
                            </div>
                        ) : (
                            <p className="rounded-2xl border border-wayna-100 bg-wayna-50/50 px-5 py-4 text-sm text-stone-600">
                                Aún no tenés una campaña activa. El equipo WAYNA puede configurarla desde
                                administración.
                            </p>
                        )}

                        <section className="overflow-hidden rounded-3xl border border-wayna-200 bg-white shadow-sm">
                            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-wayna-100 px-5 py-4">
                                <div>
                                    <h3 className="text-sm font-bold text-wayna-950">
                                        Últimos aportes
                                    </h3>
                                    <p className="mt-0.5 text-xs text-stone-500">
                                        Movimientos recientes vinculados a tus campañas
                                    </p>
                                </div>
                                <Link
                                    href={route('emprendedor.donaciones.index')}
                                    className="text-xs font-bold text-wayna-700 underline underline-offset-2 hover:text-wayna-900"
                                >
                                    Ver historial completo
                                </Link>
                            </div>
                            {panel.ultimas_donaciones?.length > 0 ? (
                                <ul className="divide-y divide-wayna-50">
                                    {panel.ultimas_donaciones.map((d) => (
                                        <li
                                            key={d.id}
                                            className="flex flex-wrap items-center justify-between gap-2 px-5 py-3 text-sm"
                                        >
                                            <div className="min-w-0">
                                                <p className="font-semibold text-wayna-950">
                                                    Bs {Number(d.monto).toLocaleString('es-BO')}
                                                </p>
                                                <p className="truncate text-xs text-stone-500">
                                                    {d.campana_titulo ?? 'Campaña'}
                                                    {' · '}
                                                    {formatearFecha(d.fecha)}
                                                </p>
                                            </div>
                                            <span
                                                className={`shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide ${
                                                    d.estado_pago === 'validado'
                                                        ? 'bg-emerald-100 text-emerald-800'
                                                        : d.estado_pago === 'pendiente'
                                                          ? 'bg-amber-100 text-amber-900'
                                                          : 'bg-red-100 text-red-800'
                                                }`}
                                            >
                                                {ETIQUETAS_DONACION[d.estado_pago] ?? d.estado_pago}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="px-5 py-8 text-center text-sm text-stone-500">
                                    Todavía no hay aportes registrados. Compartí tu perfil público o tu QR
                                    para recibir el primero.
                                </p>
                            )}
                        </section>

                        <p className="rounded-2xl border border-wayna-100 bg-surface-muted/50 px-4 py-3 text-sm text-stone-600">
                            Actualizá fotos, descripción y redes con{' '}
                            <Link
                                href={route('emprendedor.perfil.edit')}
                                className="font-semibold text-wayna-700 underline underline-offset-2"
                            >
                                Editar perfil público
                            </Link>
                            . Las publicaciones del muro llegan en E-07.
                        </p>
                    </>
                ) : (
                    <div
                        className="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-900"
                        role="alert"
                    >
                        Tu cuenta no está vinculada a un emprendedor. Contactá al administrador de WAYNA.
                    </div>
                )}

                <div className="flex flex-col gap-3 border-t border-wayna-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-xs text-stone-500">
                        Sesión:{' '}
                        <span className="font-semibold text-stone-700">{usuario.email}</span>
                    </p>
                    <LogoutButton className="inline-flex w-full items-center justify-center rounded-2xl border border-wayna-200 bg-white px-5 py-3 text-sm font-bold text-wayna-800 shadow-sm transition hover:border-wayna-300 hover:bg-wayna-50 sm:w-auto">
                        Cerrar sesión
                    </LogoutButton>
                </div>
            </div>
        </EmprendedorLayout>
    );
}
