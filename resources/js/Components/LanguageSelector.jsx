import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { initI18n } from '../i18n';

/**
 * @param {'default'|'on-brand'} variant — on-brand: sobre barra naranja #f07e26
 */
export default function LanguageSelector({ variant = 'default' }) {
    const { props } = usePage();
    const [processing, setProcessing] = useState(false);

    const currentLocale = props.locale || 'es';

    const languages = {
        es: 'ES',
        en: 'EN',
    };

    const changeLanguage = (locale) => {
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

    const buttonClass = (active) => {
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
        <div className="flex items-center gap-1.5 sm:gap-2">
            {Object.entries(languages).map(([code, label]) => (
                <button
                    key={code}
                    type="button"
                    onClick={() => changeLanguage(code)}
                    disabled={processing}
                    aria-pressed={currentLocale === code}
                    className={`rounded-full border px-3 py-1 text-xs font-bold transition sm:text-sm ${buttonClass(currentLocale === code)} ${
                        processing ? 'cursor-not-allowed opacity-60' : ''
                    }`}
                >
                    {label}
                </button>
            ))}
        </div>
    );
}
