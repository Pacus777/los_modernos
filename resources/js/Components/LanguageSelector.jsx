import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { initI18n } from '@/i18n';
import { fallbackLanguage, idiomasDisponibles, idiomaEstaSoportado } from '@/i18n/languages';

/**
 * @param {'default'|'on-brand'} variant — on-brand: sobre barra naranja #f07e26
 */
export default function LanguageSelector({ variant = 'default' }) {
    const { t } = useTranslation();
    const { props } = usePage();
    const [processing, setProcessing] = useState(false);

    const currentLocale = idiomaEstaSoportado(props.locale)
        ? props.locale
        : fallbackLanguage;

    const changeLanguage = (locale) => {
        if (!idiomaEstaSoportado(locale)) return;
        if (locale === currentLocale || processing) return;

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

    return (
        <div className="flex flex-wrap items-center gap-1.5 sm:gap-2">
            {idiomasDisponibles.map((idioma) => {
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
                        className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold transition sm:text-sm ${buttonClass(
                            active,
                            idioma.enabled,
                        )} ${processing ? 'opacity-60' : ''}`}
                    >
                        <span aria-hidden>{idioma.flag}</span>
                        <span className="hidden sm:inline">{idioma.label}</span>
                        <span className="sm:hidden">{idioma.shortLabel}</span>
                    </button>
                );
            })}
        </div>
    );
}