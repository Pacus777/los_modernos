import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Barra de progreso de la meta de campaña (T-30 / PB-09).
 * Recibe porcentaje y montos desde el servidor; anima el ancho al cargar.
 */
export default function BarraProgreso({
    porcentaje = 0,
    montoRecaudado = 0,
    meta = 0,
    titulo,
}) {
    const { t } = useTranslation();
    const [anchoBarra, setAnchoBarra] = useState(0);

    const pct = Math.min(Math.max(Number(porcentaje) || 0, 0), 100);
    const recaudadoFmt = Number(montoRecaudado ?? 0).toFixed(2);
    const metaFmt = Number(meta ?? 0).toFixed(2);

    useEffect(() => {
        const id = window.requestAnimationFrame(() => {
            setAnchoBarra(pct);
        });
        return () => window.cancelAnimationFrame(id);
    }, [pct]);

    return (
        <div className="relative overflow-hidden rounded-2xl border border-wayna-200/90 bg-gradient-to-b from-white via-wayna-50/90 to-orange-50/70 p-4 shadow-lg shadow-wayna-600/10 ring-1 ring-wayna-200/40">
            {/* Línea superior tipo “status” + rejilla sutil */}
            <div
                className="pointer-events-none absolute inset-0 opacity-[0.35]"
                style={{
                    backgroundImage: `
                        linear-gradient(to right, rgba(234, 88, 12, 0.06) 1px, transparent 1px),
                        linear-gradient(to bottom, rgba(234, 88, 12, 0.06) 1px, transparent 1px)
                    `,
                    backgroundSize: '18px 18px',
                }}
                aria-hidden
            />
            <div className="pointer-events-none absolute inset-x-4 top-0 h-px bg-gradient-to-r from-transparent via-wayna-400/70 to-transparent" />

            <div className="relative flex items-start justify-between gap-4">
                <div className="min-w-0 flex-1">
                    <p className="text-[10px] font-semibold uppercase tracking-[0.22em] text-wayna-700/90">
                        {t('tourist.profile.goalSectionTitle')}
                    </p>

                    {titulo ? (
                        <h3 className="mt-1.5 truncate text-lg font-bold tracking-tight text-wayna-950">
                            {titulo}
                        </h3>
                    ) : null}
                </div>

                <span className="shrink-0 rounded-lg border border-wayna-300/90 bg-gradient-to-br from-white to-wayna-50 px-3 py-1.5 font-mono text-xs font-bold tabular-nums tracking-wide text-wayna-800 shadow-sm shadow-wayna-500/20 ring-1 ring-wayna-100">
                    {pct}
                    <span className="ml-0.5 text-[10px] font-semibold text-wayna-600">%</span>
                </span>
            </div>

            <div
                className="relative mt-5 h-5 rounded-full bg-gradient-to-b from-wayna-100 to-orange-100 p-[3px] shadow-[inset_0_1px_6px_rgba(194,65,12,0.12)] ring-1 ring-wayna-200/80"
                role="progressbar"
                aria-valuemin={0}
                aria-valuemax={100}
                aria-valuenow={Math.round(pct)}
                aria-label={t('tourist.barraProgreso.ariaLabel', { pct: Math.round(pct) })}
            >
                <div className="h-full overflow-hidden rounded-full bg-white/95 ring-1 ring-inset ring-wayna-100/90">
                    <div
                        className="barra-progreso-fill relative h-full overflow-hidden rounded-full bg-gradient-to-r from-wayna-600 via-wayna-500 to-wayna-400 shadow-[0_0_12px_rgba(249,115,22,0.35)] transition-[width] duration-1000 ease-[cubic-bezier(0.22,1,0.36,1)]"
                        style={{ width: `${anchoBarra}%` }}
                    >
                        {pct > 0 ? (
                            <div
                                className="barra-progreso-shimmer pointer-events-none absolute inset-y-0 left-0 w-[45%] opacity-90"
                                aria-hidden
                            />
                        ) : null}
                    </div>
                </div>
            </div>

            <div className="relative mt-4 grid grid-cols-2 gap-3">
                <div className="rounded-xl border border-wayna-200/80 bg-white/75 px-3 py-3 shadow-sm backdrop-blur-sm">
                    <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-wayna-700/80">
                        {t('tourist.profile.raised')}
                    </p>
                    <p className="mt-1 font-mono text-base font-bold tabular-nums tracking-tight text-wayna-950">
                        Bs {recaudadoFmt}
                    </p>
                </div>

                <div className="rounded-xl border border-wayna-200/80 bg-white/75 px-3 py-3 shadow-sm backdrop-blur-sm">
                    <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-wayna-700/80">
                        {t('tourist.profile.goal')}
                    </p>
                    <p className="mt-1 font-mono text-base font-bold tabular-nums tracking-tight text-wayna-950">
                        Bs {metaFmt}
                    </p>
                </div>
            </div>
        </div>
    );
}
