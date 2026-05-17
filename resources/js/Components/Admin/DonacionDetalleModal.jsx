import AdminRangoMontoBadge from '@/Components/Admin/AdminRangoMontoBadge';
import {
    modalWaynaBodyCompact,
    modalWaynaFooter,
    modalWaynaShell,
} from '@/Components/Admin/adminUi';
import Modal from '@/Components/Modal';
import ReferenciaPagoDestacada from '@/Components/ReferenciaPagoDestacada';

function formatearBs(valor) {
    const n = Number(valor);
    if (Number.isNaN(n)) {
        return '0,00';
    }
    return n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function badgeEstado(estado) {
    const base =
        'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1';
    if (estado === 'pendiente') {
        return `${base} bg-amber-50 text-amber-900 ring-amber-200/80`;
    }
    if (estado === 'validado') {
        return `${base} bg-emerald-50 text-emerald-900 ring-emerald-200/80`;
    }
    if (estado === 'rechazado') {
        return `${base} bg-red-50 text-red-900 ring-red-200/80`;
    }
    return `${base} bg-stone-100 text-stone-800 ring-stone-200/80`;
}

function nombreEmprendedor(emprendedor) {
    if (!emprendedor) {
        return '—';
    }
    const completo = `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim();
    return completo || '—';
}

function etiquetaMetodoPago(donacion) {
    const tipo = donacion.tipo_pago?.nombre?.trim();
    const metodo = donacion.metodo?.trim();
    if (tipo && metodo && tipo.toLowerCase() !== metodo.toLowerCase()) {
        return `${tipo} (${metodo})`;
    }
    return tipo || metodo || '—';
}

function etiquetaVisitante(visitante) {
    if (!visitante) {
        return null;
    }
    const partes = [];
    if (visitante.nombre) {
        partes.push(visitante.nombre);
    }
    partes.push(visitante.codigo);
    if (visitante.idioma) {
        partes.push(`idioma: ${visitante.idioma}`);
    }
    return partes.filter(Boolean).join(' · ');
}

function CampoDetalle({ etiqueta, children, className = '' }) {
    return (
        <div className={className}>
            <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-wayna-700">
                {etiqueta}
            </p>
            <div className="mt-1 text-sm font-medium text-wayna-950">{children}</div>
        </div>
    );
}

/**
 * Detalle de donación en modal (T-A17).
 */
export default function DonacionDetalleModal({ show = false, onClose, donacion }) {
    if (!show || !donacion) {
        return null;
    }

    const visitanteTexto = etiquetaVisitante(donacion.visitante);
    const observacion = donacion.observacion_validacion?.trim() || null;

    return (
        <Modal show={show} onClose={onClose} maxWidth="lg">
            <div className={modalWaynaShell}>
                <div className="header-wayna-gradient shrink-0 px-4 py-3 sm:px-5">
                    <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-white/85">
                        Detalle de donación
                    </p>
                    <h2 className="mt-1 text-lg font-bold text-white sm:text-xl">
                        Donación #{donacion.id}
                    </h2>
                </div>

                <div className={`${modalWaynaBodyCompact} p-4 sm:p-5`}>
                    <ReferenciaPagoDestacada
                        referencia={donacion.referencia_pago}
                        variant="admin"
                        className="mb-4"
                    />

                    <div className="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-wayna-100 bg-wayna-50/60 px-4 py-3">
                        <span className="font-mono text-xl font-bold text-wayna-950">
                            Bs {formatearBs(donacion.monto)}
                        </span>
                        <AdminRangoMontoBadge monto={donacion.monto} />
                        <span className={badgeEstado(donacion.estado_pago)}>
                            {donacion.estado_pago}
                        </span>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <CampoDetalle etiqueta="Emprendedor">
                            {nombreEmprendedor(donacion.campana?.emprendedor)}
                        </CampoDetalle>
                        <CampoDetalle etiqueta="Campaña">
                            {donacion.campana?.titulo ?? '—'}
                        </CampoDetalle>
                        <CampoDetalle etiqueta="Método de pago">
                            {etiquetaMetodoPago(donacion)}
                        </CampoDetalle>
                        <CampoDetalle etiqueta="Fecha de registro" className="sm:col-span-2">
                            {donacion.created_at
                                ? new Date(donacion.created_at).toLocaleString('es-BO', {
                                      dateStyle: 'medium',
                                      timeStyle: 'short',
                                  })
                                : '—'}
                        </CampoDetalle>
                        <CampoDetalle etiqueta="Visitante" className="sm:col-span-2">
                            {visitanteTexto ?? (
                                <span className="font-normal text-stone-500">
                                    Sin visitante asociado
                                </span>
                            )}
                        </CampoDetalle>
                        <CampoDetalle etiqueta="Observación de validación" className="sm:col-span-2">
                            {observacion ? (
                                <span className="leading-relaxed">{observacion}</span>
                            ) : donacion.estado_pago === 'pendiente' ? (
                                <span className="font-normal text-stone-500">
                                    Pendiente de revisión por administrador o cajero.
                                </span>
                            ) : (
                                <span className="font-normal text-stone-500">
                                    Sin registro de revisión en trazabilidad.
                                </span>
                            )}
                        </CampoDetalle>
                    </div>
                </div>

                <div
                    className={`${modalWaynaFooter} flex flex-col-reverse gap-2 sm:flex-row sm:justify-end`}
                >
                    <button
                        type="button"
                        onClick={onClose}
                        className="btn-wayna-secondary w-full sm:w-auto sm:min-w-[8rem]"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </Modal>
    );
}

