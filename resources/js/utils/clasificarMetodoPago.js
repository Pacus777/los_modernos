/**
 * Clasifica el método de pago del turista (T-A21).
 *
 * @returns {'efectivo' | 'qr' | 'otro'}
 */
export function clasificarMetodoPago(tipoPago, metodoFallback = '') {
    const texto = [tipoPago?.codigo, tipoPago?.nombre, tipoPago?.descripcion, metodoFallback]
        .filter(Boolean)
        .join(' ')
        .toLowerCase();

    if (texto.includes('efectivo')) {
        return 'efectivo';
    }

    if (
        texto.includes('qr') ||
        texto.includes('billetera') ||
        texto.includes('digital') ||
        texto.includes('wallet')
    ) {
        return 'qr';
    }

    return 'otro';
}
