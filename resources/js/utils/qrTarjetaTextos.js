/**
 * Textos de la tarjeta QR descargable (ES / EN).
 */
export const TEXTOS_TARJETA_QR = {
    es: {
        titulo: '¡Escaneá para apoyar!',
        sinCampana: 'Escaneá y conocé su historia en Wayna',
        campana: (titulo) => `Campaña: ${titulo}`,
        marca: 'Wayna Conecta',
        sufijoArchivo: 'es',
    },
    en: {
        titulo: 'Scan to support!',
        sinCampana: 'Scan and discover their story on Wayna',
        campana: (titulo) => `Campaign: ${titulo}`,
        marca: 'Wayna Conecta',
        sufijoArchivo: 'en',
    },
};

export function textosTarjetaQr(locale, campanaTitulo) {
    const t = TEXTOS_TARJETA_QR[locale] ?? TEXTOS_TARJETA_QR.es;
    return {
        titulo: t.titulo,
        subtitulo: campanaTitulo ? t.campana(campanaTitulo) : t.sinCampana,
        marca: t.marca,
        sufijoArchivo: t.sufijoArchivo,
    };
}
