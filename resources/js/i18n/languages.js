export const fallbackLanguage = 'es';

export const idiomasDisponibles = [
    {
        code: 'es',
        label: 'Español',
        shortLabel: 'ES',
        flag: '🇧🇴',
        enabled: true,
    },
    {
        code: 'en',
        label: 'English',
        shortLabel: 'EN',
        flag: '🇺🇸',
        enabled: true,
    },

    // Idiomas preparados para futuro.
    // Activar cuando existan JSON o traducción dinámica guardada.
    {
        code: 'fr',
        label: 'Français',
        shortLabel: 'FR',
        flag: '🇫🇷',
        enabled: false,
    },
    {
        code: 'de',
        label: 'Deutsch',
        shortLabel: 'DE',
        flag: '🇩🇪',
        enabled: false,
    },
    {
        code: 'pt',
        label: 'Português',
        shortLabel: 'PT',
        flag: '🇧🇷',
        enabled: false,
    },
];

export const supportedLanguages = idiomasDisponibles
    .filter((idioma) => idioma.enabled)
    .map((idioma) => idioma.code);

export function obtenerIdiomaDisponible(code) {
    return idiomasDisponibles.find((idioma) => idioma.code === code);
}

export function idiomaEstaSoportado(code) {
    return supportedLanguages.includes(code);
}