import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

function parseInstante(valor) {
    if (!valor) {
        return null;
    }

    const ms = Date.parse(valor);

    return Number.isFinite(ms) ? ms : null;
}

function formatearReloj(segundos) {
    const total = Math.max(0, Math.floor(segundos));
    const minutos = Math.floor(total / 60);
    const resto = total % 60;

    return `${String(minutos).padStart(2, '0')}:${String(resto).padStart(2, '0')}`;
}

/**
 * Temporizador visual del plazo para completar el pago (T-A26).
 * Solo UI: no modifica la donación en servidor.
 */
export default function TemporizadorPagoPendiente({ plazoPago }) {
    const { t } = useTranslation();

    const venceMs = useMemo(
        () => parseInstante(plazoPago?.vence_at),
        [plazoPago?.vence_at],
    );

    const minutosPlazo = plazoPago?.minutos_plazo ?? 5;
    const duracionSegundos = Math.max(60, minutosPlazo * 60);

    const [segundosRestantes, setSegundosRestantes] = useState(() => {
        if (plazoPago?.plazo_vencido) {
            return 0;
        }

        if (typeof plazoPago?.segundos_restantes === 'number') {
            return plazoPago.segundos_restantes;
        }

        if (venceMs === null) {
            return duracionSegundos;
        }

        return Math.max(0, Math.ceil((venceMs - Date.now()) / 1000));
    });

    useEffect(() => {
        if (venceMs === null) {
            return undefined;
        }

        const tick = () => {
            setSegundosRestantes(Math.max(0, Math.ceil((venceMs - Date.now()) / 1000)));
        };

        tick();
        const id = window.setInterval(tick, 1000);

        return () => window.clearInterval(id);
    }, [venceMs]);

    const vencido = segundosRestantes <= 0;
    const progreso = vencido
        ? 0
        : Math.min(100, (segundosRestantes / duracionSegundos) * 100);

    if (!plazoPago?.vence_at) {
        return null;
    }

    if (vencido) {
        return (
            <div
                className="rounded-2xl border-2 border-amber-300/90 bg-gradient-to-b from-amber-50 to-white px-4 py-4 text-center shadow-inner"
                role="status"
                aria-live="polite"
            >
                <p className="text-sm font-bold uppercase tracking-wide text-amber-900">
                    {t('tourist.confirmation.timerExpiredTitle')}
                </p>
                <p className="mt-2 text-sm leading-relaxed text-stone-700">
                    {t('tourist.confirmation.timerExpiredBody')}
                </p>
                <p className="mt-2 text-xs text-stone-500">
                    {t('tourist.confirmation.timerExpiredNote')}
                </p>
            </div>
        );
    }

    return (
        <div
            className="rounded-2xl border-2 border-wayna-300/70 bg-gradient-to-b from-wayna-50/90 via-white to-wayna-50/50 px-4 py-4 text-center shadow-inner ring-1 ring-wayna-200/50"
            role="timer"
            aria-live="polite"
            aria-atomic="true"
        >
            <p className="text-[11px] font-bold uppercase tracking-[0.18em] text-wayna-800">
                {t('tourist.confirmation.timerActiveTitle')}
            </p>
            <p className="mt-1 text-xs text-stone-600">
                {t('tourist.confirmation.timerActiveHint', {
                    minutes: minutosPlazo,
                })}
            </p>

            <div className="relative mx-auto mt-4 flex h-28 w-28 items-center justify-center">
                <svg
                    className="absolute inset-0 h-full w-full -rotate-90"
                    viewBox="0 0 120 120"
                    aria-hidden
                >
                    <circle
                        cx="60"
                        cy="60"
                        r="52"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="8"
                        className="text-wayna-100"
                    />
                    <circle
                        cx="60"
                        cy="60"
                        r="52"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="8"
                        strokeLinecap="round"
                        className="text-wayna-500 transition-[stroke-dashoffset] duration-1000 ease-linear"
                        strokeDasharray={2 * Math.PI * 52}
                        strokeDashoffset={
                            (2 * Math.PI * 52 * (100 - progreso)) / 100
                        }
                    />
                </svg>
                <span className="relative font-mono text-3xl font-black tabular-nums tracking-tight text-wayna-950">
                    {formatearReloj(segundosRestantes)}
                </span>
            </div>

            <div className="mx-auto mt-3 h-1.5 max-w-xs overflow-hidden rounded-full bg-wayna-100">
                <div
                    className="h-full rounded-full bg-gradient-to-r from-wayna-500 to-wayna-400 transition-[width] duration-1000 ease-linear"
                    style={{ width: `${progreso}%` }}
                />
            </div>
        </div>
    );
}
