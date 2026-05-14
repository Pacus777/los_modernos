import { router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useState } from 'react';

export default function LanguageSelector() {
    const { props } = usePage();
    const { i18n } = useTranslation();
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
                    i18n.changeLanguage(locale);
                },
                onFinish: () => {
                    setProcessing(false);
                },
            }
        );
    };

    return (
        <div className="flex items-center gap-2">
            {Object.entries(languages).map(([code, label]) => (
                <button
                    key={code}
                    type="button"
                    onClick={() => changeLanguage(code)}
                    disabled={processing}
                    aria-pressed={currentLocale === code}
                    className={`rounded-full border px-3 py-1 text-sm font-semibold transition ${
                        currentLocale === code
                            ? 'border-red-700 bg-red-700 text-white'
                            : 'border-red-200 bg-white text-red-800 hover:bg-red-50'
                    } ${processing ? 'cursor-not-allowed opacity-60' : ''}`}
                >
                    {label}
                </button>
            ))}
        </div>
    );
}