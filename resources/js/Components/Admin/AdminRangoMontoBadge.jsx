import {
    clasificarRangoMonto,
    estiloBadgeRango,
    etiquetaRangoMonto,
} from '@/utils/rangoMonto';

/**
 * Etiqueta visual del rango de un monto (ayuda, no cálculo financiero).
 */
export default function AdminRangoMontoBadge({ monto, className = '' }) {
    const rango = clasificarRangoMonto(monto);
    if (!rango) {
        return null;
    }

    return (
        <span
            className={`${estiloBadgeRango(rango)} ${className}`}
            title={etiquetaRangoMonto(rango)}
        >
            {etiquetaRangoMonto(rango, true)}
        </span>
    );
}
