import { useEffect, useState } from 'react';

/**
 * Formato unificado T-A6: Bs. 500 / Bs. 2.000 — 25%
 */
export function formatearBsProgreso(monto) {
    const valor = Number(monto || 0);
    if (Number.isNaN(valor)) {
        return 'Bs. 0';
    }

    return `Bs. ${valor.toLocaleString('es-BO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })}`;
}

export function calcularPorcentajeMeta(montoRecaudado, meta, porcentajeExplicito) {
    if (porcentajeExplicito !== undefined && porcentajeExplicito !== null) {
        return Math.min(100, Math.max(0, Number(porcentajeExplicito) || 0));
    }

    const metaNum = Number(meta) || 0;
    const recaudado = Number(montoRecaudado) || 0;

    if (metaNum <= 0) {
        return 0;
    }

    return Math.min(100, Math.round((recaudado / metaNum) * 100));
}

export function textoProgresoMeta(montoRecaudado, meta, porcentajeExplicito) {
    const pct = calcularPorcentajeMeta(montoRecaudado, meta, porcentajeExplicito);

    return `${formatearBsProgreso(montoRecaudado)} / ${formatearBsProgreso(meta)} — ${pct}%`;
}

const MARCAS_META = [25, 50, 75];

/**
 * Barra de progreso con montos, porcentaje y animación (T-A6).
 */
export default function BarraProgresoMeta({
    montoRecaudado = 0,
    meta = 0,
    porcentaje,
    variant = 'compact',
    animar = true,
    className = '',
    etiquetaAria,
}) {
    const pct = calcularPorcentajeMeta(montoRecaudado, meta, porcentaje);
    const completa = pct >= 100;
    const [anchoBarra, setAnchoBarra] = useState(animar ? 0 : pct);
    const [badgeVisible, setBadgeVisible] = useState(!animar);

    useEffect(() => {
        if (!animar) {
            setAnchoBarra(pct);
            setBadgeVisible(true);
            return;
        }

        setAnchoBarra(0);
        setBadgeVisible(false);

        const idFill = window.requestAnimationFrame(() => {
            setAnchoBarra(pct);
            window.setTimeout(() => setBadgeVisible(true), 450);
        });

        return () => window.cancelAnimationFrame(idFill);
    }, [pct, animar, montoRecaudado, meta]);

    const esInline = variant === 'inline';
    const esFull = variant === 'full';

    const alturaTrack = esFull ? 'h-5' : esInline ? 'h-2' : 'h-3';
    const paddingTrack = esInline ? 'p-px' : 'p-[3px]';

    const textoRecaudado = esFull ? 'text-base sm:text-lg' : esInline ? 'text-[10px]' : 'text-sm';
    const badgePct = esFull
        ? 'px-3 py-1 text-xs'
        : esInline
          ? 'px-1.5 py-0.5 text-[9px]'
          : 'px-2.5 py-0.5 text-[10px]';

    return (
        <div className={className}>
            <div
                className={`mb-2 flex flex-wrap items-center justify-between gap-2 ${
                    esFull ? 'mb-3' : ''
                }`}
            >
                <p
                    className={`min-w-0 flex-1 font-bold tabular-nums leading-snug text-wayna-950 ${textoRecaudado}`}
                >
                    <span className="text-wayna-700">{formatearBsProgreso(montoRecaudado)}</span>
                    <span className="mx-1 font-normal text-stone-400">/</span>
                    <span className="font-semibold text-stone-600">
                        {formatearBsProgreso(meta)}
                    </span>
                </p>
                <span
                    className={`barra-progreso-badge shrink-0 rounded-full font-bold tabular-nums shadow-sm ring-1 ${
                        badgeVisible ? '' : 'opacity-0'
                    } ${badgePct} ${
                        completa
                            ? 'bg-emerald-600 text-white ring-emerald-500/40'
                            : 'bg-gradient-to-br from-wayna-600 to-wayna-500 text-white ring-wayna-400/50'
                    }`}
                >
                    {pct}%
                </span>
            </div>

            <div className="relative">
                {!esInline && (
                    <div
                        className="pointer-events-none absolute inset-x-2 top-1/2 z-0 h-0 -translate-y-1/2"
                        aria-hidden
                    >
                        {MARCAS_META.map((marca) => (
                            <span
                                key={marca}
                                className={`absolute top-0 h-1.5 w-1.5 -translate-x-1/2 rounded-full transition-colors duration-500 ${
                                    pct >= marca
                                        ? 'bg-wayna-500 shadow-[0_0_4px_rgba(240,126,38,0.6)]'
                                        : 'bg-wayna-200/90'
                                }`}
                                style={{ left: `${marca}%` }}
                            />
                        ))}
                    </div>
                )}

                <div
                    className={`barra-progreso-track relative z-10 overflow-hidden rounded-full ring-1 ring-wayna-200/80 ${alturaTrack} ${paddingTrack}`}
                    role="progressbar"
                    aria-valuemin={0}
                    aria-valuemax={100}
                    aria-valuenow={Math.round(pct)}
                    aria-label={etiquetaAria ?? textoProgresoMeta(montoRecaudado, meta, pct)}
                >
                    <div className="h-full overflow-hidden rounded-full bg-white/50">
                        <div
                            className={`barra-progreso-fill relative h-full overflow-hidden rounded-full ${
                                completa ? 'barra-progreso-fill--completa' : ''
                            }`}
                            style={{ width: `${anchoBarra}%` }}
                        >
                            {anchoBarra > 4 && (
                                <div
                                    className="barra-progreso-shimmer pointer-events-none absolute inset-y-0 left-0 w-[42%]"
                                    aria-hidden
                                />
                            )}
                            {anchoBarra > 8 && (
                                <span
                                    className="pointer-events-none absolute right-0 top-1/2 h-[70%] w-1 -translate-y-1/2 rounded-full bg-white/50 blur-[1px]"
                                    aria-hidden
                                />
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
