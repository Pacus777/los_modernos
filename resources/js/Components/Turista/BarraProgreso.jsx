import BarraProgresoMeta, {
    calcularPorcentajeMeta,
} from '@/Components/BarraProgresoMeta';
import { useTranslation } from 'react-i18next';

/**
 * Barra de progreso de la meta de campaña (perfil turista).
 * Muestra Bs. recaudado / Bs. meta — % de forma explícita (T-A6).
 */
export default function BarraProgreso({
    porcentaje = 0,
    montoRecaudado = 0,
    meta = 0,
    titulo,
}) {
    const { t } = useTranslation();
    const pct = calcularPorcentajeMeta(montoRecaudado, meta, porcentaje);

    return (
        <div className="relative overflow-hidden rounded-2xl border border-wayna-200/90 bg-gradient-to-b from-surface-card via-wayna-50/90 to-surface-muted p-4 shadow-lg shadow-wayna-500/10 ring-1 ring-wayna-200/40">
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
            </div>

            <div className="relative mt-4">
                <BarraProgresoMeta
                    montoRecaudado={montoRecaudado}
                    meta={meta}
                    porcentaje={pct}
                    variant="full"
                    animar
                    etiquetaAria={t('tourist.barraProgreso.ariaLabel', {
                        pct: Math.round(pct),
                    })}
                />
            </div>
        </div>
    );
}
