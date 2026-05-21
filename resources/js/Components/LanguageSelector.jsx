import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { initI18n } from '@/i18n';
import { fallbackLanguage, idiomasDisponibles, idiomaEstaSoportado } from '@/i18n/languages';
import { setWaynaEnterSkip } from '@/utils/waynaEnterSkip';

/**
 * @param {'default'|'on-brand'} variant — on-brand: sobre barra naranja #f07e26
 * @param {boolean} compact — solo idiomas activos; en móvil usa lista desplegable
 */
export default function LanguageSelector({ variant = 'default', compact = false }) {
    const { t } = useTranslation();
    const { props } = usePage();
    const [processing, setProcessing] = useState(false);

    const currentLocale = idiomaEstaSoportado(props.locale)
        ? props.locale
        : fallbackLanguage;

    const lista = compact
        ? idiomasDisponibles.filter((idioma) => idioma.enabled)
        : idiomasDisponibles;

    const changeLanguage = (locale) => {
        if (!idiomaEstaSoportado(locale)) {
            return;
        }
        if (locale === currentLocale || processing) {
            return;
        }

        setProcessing(true);

        // Evita la intro visual Wayna al recargar la página (p. ej. landing tras cambiar idioma).
        setWaynaEnterSkip();

        router.post(
            '/idioma',
            { locale },
            {
                preserveScroll: true,
                preserveState: false,
                onSuccess: () => {
                    initI18n(locale);
                },
                onFinish: () => {
                    setProcessing(false);
                },
            },
        );
    };

    const buttonClass = (active, enabled) => {
        if (!enabled) {
            return 'cursor-not-allowed border-stone-200 bg-stone-100 text-stone-400';
        }

        if (variant === 'on-brand') {
            return active
                ? 'border-2 border-white bg-white text-wayna-800 shadow-md ring-2 ring-wayna-900/15'
                : 'border-2 border-white/90 bg-white/20 text-white hover:border-white hover:bg-white/35';
        }

        return active
            ? 'border-wayna-500 bg-wayna-500 text-white shadow-sm'
            : 'border-wayna-200 bg-surface-card text-wayna-800 hover:border-wayna-300 hover:bg-wayna-50';
    };

    const selectClass =
        variant === 'on-brand'
            ? 'language-select language-select--on-brand max-w-[5.5rem] sm:max-w-none'
            : 'language-select max-w-[5.5rem] sm:max-w-none';

    // En cabecera naranja: botones visibles (el select nativo contrasta mal y el menú es azul del SO).
    const usarBotones = variant === 'on-brand' || lista.length <= 3;

    if (compact && !usarBotones) {
        return (
            <div className="shrink-0">
                <label className="sr-only" htmlFor="language-select">
                    {t('common.language')}
                </label>
                <select
                    id="language-select"
                    value={currentLocale}
                    disabled={processing}
                    onChange={(e) => changeLanguage(e.target.value)}
                    className={`${selectClass} ${processing ? 'opacity-60' : ''}`}
                    aria-label={t('common.language')}
                >
                    {lista.map((idioma) => (
                        <option key={idioma.code} value={idioma.code}>
                            {idioma.flag} {idioma.shortLabel}
                        </option>
                    ))}
                </select>
            </div>
        );
    }

    const groupShellClass =
        variant === 'on-brand'
            ? 'rounded-full border-2 border-white/70 bg-wayna-800/25 p-0.5 shadow-sm backdrop-blur-sm'
            : 'rounded-full border border-wayna-200/80 bg-surface-card p-0.5 shadow-sm';

    return (
        <div
            className={`flex max-w-full flex-wrap items-center justify-end ${groupShellClass}`}
            role="group"
            aria-label={t('common.language')}
        >
            {lista.map((idioma) => {
                const active = currentLocale === idioma.code;

                return (
                    <button
                        key={idioma.code}
                        type="button"
                        onClick={() => changeLanguage(idioma.code)}
                        disabled={processing || !idioma.enabled}
                        aria-pressed={active}
                        title={
                            idioma.enabled
                                ? idioma.label
                                : t('language.comingSoon', { label: idioma.label })
                        }
                        className={`inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1.5 text-xs font-extrabold tracking-wide transition sm:gap-1.5 sm:px-3.5 sm:py-2 sm:text-sm ${buttonClass(
                            active,
                            idioma.enabled,
                        )} ${processing ? 'opacity-60' : ''}`}
                    >
                        <span aria-hidden>{idioma.flag}</span>
                        <span className="sm:hidden">{idioma.shortLabel}</span>
                        <span className="hidden sm:inline">{idioma.label}</span>
                    </button>
                );
            })}
        </div>
    );
}
