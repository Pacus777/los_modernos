import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

import es from './locales/es.json';
import en from './locales/en.json';

const supportedLanguages = ['es', 'en'];
const fallbackLanguage = 'es';

export function initI18n(language = fallbackLanguage) {
    const currentLanguage = supportedLanguages.includes(language)
        ? language
        : fallbackLanguage;

    if (!i18n.isInitialized) {
        i18n
            .use(initReactI18next)
            .init({
                resources: {
                    es: { translation: es },
                    en: { translation: en },
                },
                lng: currentLanguage,
                fallbackLng: fallbackLanguage,
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