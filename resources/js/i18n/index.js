import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

import en from './locales/en.json';
import es from './locales/es.json';
import { fallbackLanguage, supportedLanguages } from './languages';

const resources = {
    es: { translation: es },
    en: { translation: en },
};

export function initI18n(language = fallbackLanguage) {
    const currentLanguage = supportedLanguages.includes(language)
        ? language
        : fallbackLanguage;

    if (!i18n.isInitialized) {
        i18n
            .use(initReactI18next)
            .init({
                resources,
                lng: currentLanguage,
                fallbackLng: fallbackLanguage,
                supportedLngs: supportedLanguages,
                interpolation: {
                    escapeValue: false,
                },
            });
    } else if (i18n.language !== currentLanguage) {
        i18n.changeLanguage(currentLanguage);
    }

    return i18n;
}

export default i18n;