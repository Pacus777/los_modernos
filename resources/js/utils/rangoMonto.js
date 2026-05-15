/** Rangos T-A15 (sincronizado con App\Enums\RangoMonto). */
export const RANGOS_MONTO = [
    { value: 'bajo', label: 'Bajo (hasta Bs. 500)', corta: 'Bajo' },
    { value: 'medio', label: 'Medio (Bs. 501 – 2.000)', corta: 'Medio' },
    { value: 'alto', label: 'Alto (más de Bs. 2.000)', corta: 'Alto' },
];

export const RANGO_MONTO_BAJO_MAX = 500;
export const RANGO_MONTO_MEDIO_MAX = 2000;

export function clasificarRangoMonto(monto) {
    const n = Number(monto);
    if (Number.isNaN(n) || monto === '' || monto === null) {
        return null;
    }
    if (n <= RANGO_MONTO_BAJO_MAX) {
        return 'bajo';
    }
    if (n <= RANGO_MONTO_MEDIO_MAX) {
        return 'medio';
    }
    return 'alto';
}

export function etiquetaRangoMonto(valor, corta = false) {
    if (!valor) {
        return null;
    }
    const opt = RANGOS_MONTO.find((r) => r.value === valor);
    if (!opt) {
        return null;
    }
    return corta ? opt.corta : opt.label;
}

export function estiloBadgeRango(valor) {
    const base =
        'inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide ring-1';
    if (valor === 'bajo') {
        return `${base} bg-sky-50 text-sky-900 ring-sky-200/80`;
    }
    if (valor === 'medio') {
        return `${base} bg-amber-50 text-amber-950 ring-amber-200/80`;
    }
    if (valor === 'alto') {
        return `${base} bg-violet-50 text-violet-950 ring-violet-200/80`;
    }
    return `${base} bg-stone-100 text-stone-700 ring-stone-200`;
}
