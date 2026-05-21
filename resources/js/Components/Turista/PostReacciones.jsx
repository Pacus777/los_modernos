import { alternarReaccionPost } from '@/utils/postReaccionApi';
import { useCallback, useState } from 'react';
import { useTranslation } from 'react-i18next';

const TIPOS = [
    { id: 'me_gusta', emoji: '👍' },
    { id: 'aplauso', emoji: '👏' },
    { id: 'apoyo', emoji: '💚' },
    { id: 'inspirado', emoji: '✨' },
];

function totalesVacios() {
    return { me_gusta: 0, aplauso: 0, apoyo: 0, inspirado: 0 };
}

/**
 * Cuatro reacciones con animación y UI optimista (S4-07).
 */
export default function PostReacciones({
    postId,
    totalesIniciales = {},
    miReaccionInicial = null,
}) {
    const { t } = useTranslation();
    const [totales, setTotales] = useState(() => ({
        ...totalesVacios(),
        ...totalesIniciales,
    }));
    const [miReaccion, setMiReaccion] = useState(miReaccionInicial);
    const [popTipo, setPopTipo] = useState(null);
    const [error, setError] = useState(null);
    const [enviando, setEnviando] = useState(false);

    const pulsarAnimacion = useCallback((tipo) => {
        setPopTipo(tipo);
        window.setTimeout(() => setPopTipo(null), 380);
    }, []);

    const alternar = async (tipo) => {
        if (enviando) {
            return;
        }

        setError(null);
        const anteriorTotales = { ...totales };
        const anteriorMi = miReaccion;

        let siguienteMi = tipo;
        const siguienteTotales = { ...totales };

        if (miReaccion === tipo) {
            siguienteMi = null;
            siguienteTotales[tipo] = Math.max((siguienteTotales[tipo] ?? 0) - 1, 0);
        } else {
            if (miReaccion) {
                siguienteTotales[miReaccion] = Math.max(
                    (siguienteTotales[miReaccion] ?? 0) - 1,
                    0,
                );
            }
            siguienteTotales[tipo] = (siguienteTotales[tipo] ?? 0) + 1;
        }

        setMiReaccion(siguienteMi);
        setTotales(siguienteTotales);
        pulsarAnimacion(tipo);
        setEnviando(true);

        try {
            const data = await alternarReaccionPost(postId, tipo);
            setMiReaccion(data.mi_reaccion ?? null);
            setTotales({ ...totalesVacios(), ...data.totales });
        } catch (err) {
            setMiReaccion(anteriorMi);
            setTotales(anteriorTotales);
            setError(err.message);
        } finally {
            setEnviando(false);
        }
    };

    return (
        <div className="mt-3 border-t border-stone-100 pt-3">
            <div
                className="flex flex-wrap items-center gap-2"
                role="group"
                aria-label={t('tourist.reactions.groupLabel')}
            >
                {TIPOS.map(({ id, emoji }) => {
                    const activo = miReaccion === id;
                    const cuenta = totales[id] ?? 0;

                    return (
                        <button
                            key={id}
                            type="button"
                            disabled={enviando}
                            onClick={() => alternar(id)}
                            className={[
                                'post-reaccion-btn inline-flex min-h-10 items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm font-semibold transition',
                                activo
                                    ? 'border-wayna-400 bg-wayna-50 text-wayna-900 shadow-sm'
                                    : 'border-stone-200 bg-white text-stone-700 hover:border-wayna-300 hover:bg-wayna-50/60',
                                popTipo === id ? 'post-reaccion-btn--pop' : '',
                            ].join(' ')}
                            aria-pressed={activo}
                            aria-label={t(`tourist.reactions.types.${id}`, { count: cuenta })}
                        >
                            <span className="text-base leading-none" aria-hidden>
                                {emoji}
                            </span>
                            <span className="tabular-nums">{cuenta > 0 ? cuenta : ''}</span>
                        </button>
                    );
                })}
            </div>
            {error ? (
                <p className="mt-2 text-xs font-medium text-red-600" role="alert">
                    {error}
                </p>
            ) : null}
        </div>
    );
}
