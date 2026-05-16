import { Link } from '@inertiajs/react';

/**
 * Tarjeta móvil para donación en efectivo pendiente (T-A29).
 */
export default function PendienteEfectivoCard({
    donacion,
    nombreEmprendedor,
    montoFormateado,
    fechaFormateada,
    onConfirmar,
    confirmando = false,
    modo = 'confirmar',
}) {
    const referencia = donacion.referencia_pago ?? '—';
    const campana = donacion.campana?.titulo ?? 'Sin campaña';

    return (
        <article className="rounded-xl border border-wayna-100 bg-white p-4 shadow-sm">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0 flex-1">
                    <p className="text-xs font-semibold uppercase tracking-wide text-stone-500">
                        #{donacion.id}
                    </p>
                    <h3 className="mt-1 text-base font-bold text-wayna-950">
                        {nombreEmprendedor}
                    </h3>
                    <p className="mt-1 truncate text-sm text-stone-600">{campana}</p>
                </div>
                <p className="shrink-0 text-lg font-bold text-wayna-900">
                    Bs {montoFormateado}
                </p>
            </div>

            <dl className="mt-3 space-y-2 border-t border-wayna-50 pt-3 text-sm">
                <div className="flex justify-between gap-3">
                    <dt className="shrink-0 text-stone-500">Referencia</dt>
                    <dd className="min-w-0 break-all text-right font-mono text-xs font-bold text-wayna-900">
                        {referencia}
                    </dd>
                </div>
                {fechaFormateada && (
                    <div className="flex justify-between gap-3">
                        <dt className="text-stone-500">Fecha</dt>
                        <dd className="text-right text-stone-700">{fechaFormateada}</dd>
                    </div>
                )}
                {donacion.estado_pago && (
                    <div className="flex justify-between gap-3">
                        <dt className="text-stone-500">Estado</dt>
                        <dd>
                            <span className="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-900 ring-1 ring-amber-200/80">
                                {donacion.estado_pago}
                            </span>
                        </dd>
                    </div>
                )}
            </dl>

            <div className="mt-4">
                {modo === 'enlace' ? (
                    <Link
                        href={route('cajero.efectivo.confirmar', donacion.id)}
                        className="touch-target flex min-h-11 w-full items-center justify-center rounded-lg bg-wayna-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-wayna-700"
                    >
                        Confirmar
                    </Link>
                ) : (
                    <button
                        type="button"
                        onClick={() => onConfirmar?.(donacion)}
                        disabled={confirmando}
                        className="touch-target flex min-h-11 w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {confirmando ? 'Confirmando…' : 'Confirmar pago'}
                    </button>
                )}
            </div>
        </article>
    );
}
