import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { initI18n } from '@/i18n';
import { fallbackLanguage, idiomasDisponibles, idiomaEstaSoportado } from '@/i18n/languages';

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
                ? 'border-white bg-white text-wayna-700 shadow-sm'
                : 'border-white/50 bg-wayna-600/50 text-white hover:border-white hover:bg-wayna-600';
        }

        return active
            ? 'border-wayna-500 bg-wayna-500 text-white shadow-sm'
            : 'border-wayna-200 bg-surface-card text-wayna-800 hover:border-wayna-300 hover:bg-wayna-50';
    };

    const selectClass =
        variant === 'on-brand'
            ? 'language-select language-select--on-brand max-w-[5.5rem] sm:max-w-none'
            : 'language-select max-w-[5.5rem] sm:max-w-none';

    if (compact) {
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

    return (
        <div
            className="flex max-w-full flex-wrap items-center justify-end gap-1 sm:gap-1.5"
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
                        className={`inline-flex shrink-0 items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-bold transition sm:gap-1.5 sm:px-3 sm:py-1.5 sm:text-sm ${buttonClass(
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
