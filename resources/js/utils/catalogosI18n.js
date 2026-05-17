import { etiquetaDepartamento } from '@/utils/departamento';
import { etiquetaTipoEmprendimiento } from '@/utils/tipoEmprendimiento';

/**
 * Etiqueta de rubro traducida (fallback al catálogo en español del código).
 */
export function etiquetaTipoEmprendimientoT(t, value) {
    if (!value) {
        return null;
    }

    const key = `catalog.businessType.${value}`;
    const traducido = t(key);

    if (traducido && traducido !== key) {
        return traducido;
    }

    return etiquetaTipoEmprendimiento(value);
}

/**
 * Etiqueta de departamento traducida.
 */
export function etiquetaDepartamentoT(t, value) {
    if (!value) {
        return null;
    }

    const key = `catalog.department.${value}`;
    const traducido = t(key);

    if (traducido && traducido !== key) {
        return traducido;
    }

    return etiquetaDepartamento(value);
}

/**
 * Opciones de select con etiquetas i18n.
 */
export function opcionesTipoEmprendimientoT(t, opciones = []) {
    return opciones.map((opt) => ({
        ...opt,
        label: etiquetaTipoEmprendimientoT(t, opt.value) ?? opt.label,
    }));
}

export function opcionesDepartamentoT(t, opciones = []) {
    return opciones.map((opt) => ({
        ...opt,
        label: etiquetaDepartamentoT(t, opt.value) ?? opt.label,
    }));
}

/**
 * Nombre del método de pago (efectivo, QR, etc.) según idioma activo.
 */
export function etiquetaTipoPagoT(t, tipoPago) {
    if (!tipoPago) {
        return '';
    }

    const codigo = tipoPago.codigo ?? '';
    if (codigo) {
        const key = `catalog.paymentMethod.${codigo}`;
        const traducido = t(key);
        if (traducido && traducido !== key) {
            return traducido;
        }
    }

    return tipoPago.nombre ?? '';
}
