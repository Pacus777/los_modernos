/**
 * Equivalente USD referencial desde Bs (T-A24).
 *
 * @param {number|string} montoBs
 * @param {number} usdPorBs cuántos USD por 1 Bs (ej. 0.145)
 * @returns {number|null}
 */
export function bolivianosAUsd(montoBs, usdPorBs) {
    const tasa = Number(usdPorBs);
    const monto = Number(montoBs);

    if (!Number.isFinite(tasa) || tasa <= 0 || !Number.isFinite(monto) || monto <= 0) {
        return null;
    }

    return Math.round(monto * tasa * 100) / 100;
}

/**
 * @param {number} usd
 * @param {string} [locale]
 */
export function formatearUsd(usd, locale = 'en-US') {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(usd);
}
