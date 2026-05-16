/**
 * Comprueba si una campaña está activa y vigente por fechas (T-A12).
 */
export function campanaVigentePorFechas(campana, fechaHoy = null) {
    if (!campana || campana.estado !== 'activa') {
        return false;
    }

    const hoy = fechaHoy ?? fechaLocalHoy();

    if (campana.fecha_inicio) {
        const inicio = String(campana.fecha_inicio).split('T')[0];
        if (inicio > hoy) {
            return false;
        }
    }

    if (campana.fecha_fin) {
        const fin = String(campana.fecha_fin).split('T')[0];
        if (fin < hoy) {
            return false;
        }
    }

    return true;
}

export function fechaLocalHoy() {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');

    return `${y}-${m}-${day}`;
}
