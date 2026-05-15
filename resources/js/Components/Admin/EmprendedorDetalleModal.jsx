import BarraProgresoMeta from '@/Components/BarraProgresoMeta';
import {
    modalWaynaBodyCompact,
    modalWaynaFooter,
    modalWaynaShell,
} from '@/Components/Admin/adminUi';
import Modal from '@/Components/Modal';
import { etiquetaDepartamento } from '@/utils/departamento';
import { etiquetaTipoEmprendimiento } from '@/utils/tipoEmprendimiento';
import { Link } from '@inertiajs/react';

function formatearBs(valor) {
    const n = Number(valor);
    if (Number.isNaN(n)) {
        return '0,00';
    }
    return n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function etiquetaEstadoEmprendedor(estado) {
    return estado === 'activo' ? 'Activo' : 'Inactivo';
}

function etiquetaEstadoCampana(estado) {
    const map = {
        activa: 'Activa',
        inactiva: 'Inactiva',
        finalizada: 'Finalizada',
    };
    return map[estado] ?? estado;
}

function estiloEstadoCampana(estado) {
    if (estado === 'activa') {
        return 'bg-wayna-100 text-wayna-900 ring-wayna-300/70';
    }
    if (estado === 'finalizada') {
        return 'bg-stone-100 text-stone-700 ring-stone-200';
    }
    return 'bg-amber-50 text-amber-900 ring-amber-200';
}

/**
 * Vista de solo lectura del emprendedor (T-A10).
 */
export default function EmprendedorDetalleModal({
    show = false,
    onClose,
    emprendedor,
    fotoSrc = null,
    qrSrc = null,
    onVerQr = null,
}) {
    if (!show || !emprendedor) {
        return null;
    }

    const nombreCompleto =
        `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim() || 'Emprendedor';

    const campanas = emprendedor.campanas ?? [];
    const metaReferencia = Number(emprendedor.meta_monto) || 0;

    const estiloEstado =
        emprendedor.estado === 'activo'
            ? 'bg-wayna-100 text-wayna-900 ring-1 ring-wayna-300/70'
            : 'bg-stone-100 text-stone-700 ring-1 ring-stone-200';

    return (
        <Modal show={show} onClose={onClose} maxWidth="xl">
            <div className={modalWaynaShell}>
                <div className="header-wayna-gradient shrink-0 px-4 py-3 sm:px-5">
                    <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-white/85">
                        Ficha del emprendedor
                    </p>
                    <h2 className="mt-1 text-lg font-bold text-white sm:text-xl">{nombreCompleto}</h2>
                </div>

                <div className={`${modalWaynaBodyCompact} p-4 sm:p-5`}>
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <div className="flex shrink-0 flex-col items-center gap-2 sm:w-28">
                            {fotoSrc ? (
                                <img
                                    src={fotoSrc}
                                    alt={nombreCompleto}
                                    className="h-28 w-28 rounded-2xl object-cover shadow-md ring-2 ring-wayna-200"
                                />
                            ) : (
                                <div className="flex h-28 w-28 items-center justify-center rounded-2xl bg-gradient-to-br from-wayna-100 to-surface-muted text-3xl font-black text-wayna-700 ring-2 ring-wayna-200">
                                    {emprendedor.nombre?.charAt(0) ?? '?'}
                                </div>
                            )}
                            <span
                                className={`inline-flex rounded-full px-3 py-1 text-xs font-bold capitalize ring-1 ${estiloEstado}`}
                            >
                                {etiquetaEstadoEmprendedor(emprendedor.estado)}
                            </span>
                        </div>

                        <div className="min-w-0 flex-1 space-y-4">
                            {etiquetaTipoEmprendimiento(emprendedor.tipo_emprendimiento) ||
                            etiquetaDepartamento(emprendedor.departamento) ? (
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {etiquetaTipoEmprendimiento(emprendedor.tipo_emprendimiento) ? (
                                        <div>
                                            <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-wayna-700">
                                                Tipo de emprendimiento
                                            </p>
                                            <p className="mt-1 text-sm font-semibold text-wayna-950">
                                                {etiquetaTipoEmprendimiento(
                                                    emprendedor.tipo_emprendimiento,
                                                )}
                                            </p>
                                        </div>
                                    ) : null}
                                    {etiquetaDepartamento(emprendedor.departamento) ? (
                                        <div>
                                            <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-wayna-700">
                                                Departamento
                                            </p>
                                            <p className="mt-1 text-sm font-semibold text-wayna-950">
                                                {etiquetaDepartamento(emprendedor.departamento)}
                                            </p>
                                        </div>
                                    ) : null}
                                </div>
                            ) : null}
                            <div>
                                <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-wayna-700">
                                    Descripción
                                </p>
                                <p className="mt-2 text-sm leading-relaxed text-stone-700">
                                    {emprendedor.descripcion?.trim() ||
                                        'Sin descripción registrada.'}
                                </p>
                            </div>

                            <div className="rounded-xl border border-wayna-100 bg-wayna-50/60 px-4 py-3">
                                <p className="text-[10px] font-bold uppercase tracking-wide text-wayna-700">
                                    Meta de referencia
                                </p>
                                <p className="mt-1 font-mono text-base font-bold text-wayna-950">
                                    Bs {formatearBs(metaReferencia)}
                                </p>
                            </div>

                            <div>
                                <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-wayna-700">
                                    Código QR de perfil
                                </p>
                                {qrSrc ? (
                                    <div className="mt-3 flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                                        <div className="rounded-2xl border-2 border-wayna-200 bg-white p-2 shadow-inner">
                                            <img
                                                src={qrSrc}
                                                alt={`QR ${nombreCompleto}`}
                                                className="h-24 w-24 object-contain"
                                            />
                                        </div>
                                        {onVerQr ? (
                                            <button
                                                type="button"
                                                onClick={() => onVerQr(emprendedor)}
                                                className="text-sm font-bold text-wayna-600 underline decoration-wayna-300 underline-offset-2 hover:text-wayna-800"
                                            >
                                                Ampliar y descargar QR
                                            </button>
                                        ) : null}
                                    </div>
                                ) : (
                                    <p className="mt-2 text-sm text-stone-500">
                                        QR pendiente de generación.
                                    </p>
                                )}
                            </div>

                            <div>
                                <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-wayna-700">
                                    Campañas asociadas
                                </p>
                                {campanas.length === 0 ? (
                                    <p className="mt-2 rounded-xl border border-dashed border-wayna-200 bg-surface-muted/80 px-4 py-3 text-sm text-stone-600">
                                        No hay campañas registradas para este emprendedor.
                                    </p>
                                ) : (
                                    <ul className="mt-3 space-y-3">
                                        {campanas.map((campana) => {
                                            const meta = Number(campana.meta_apoyo) || 0;
                                            const recaudado = Number(campana.monto_recaudado) || 0;
                                            const pct =
                                                meta > 0
                                                    ? Math.min(
                                                          100,
                                                          Math.round((recaudado / meta) * 100),
                                                      )
                                                    : 0;

                                            return (
                                                <li
                                                    key={campana.id}
                                                    className="rounded-xl border border-wayna-100 bg-white p-4 shadow-sm"
                                                >
                                                    <div className="flex flex-wrap items-start justify-between gap-2">
                                                        <p className="font-semibold text-wayna-950">
                                                            {campana.titulo}
                                                        </p>
                                                        <span
                                                            className={`inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase ring-1 ${estiloEstadoCampana(campana.estado)}`}
                                                        >
                                                            {etiquetaEstadoCampana(campana.estado)}
                                                        </span>
                                                    </div>
                                                    <p className="mt-2 font-mono text-sm text-stone-600">
                                                        Meta Bs {formatearBs(meta)} · Recaudado Bs{' '}
                                                        {formatearBs(recaudado)}
                                                    </p>
                                                    {meta > 0 && (
                                                        <div className="mt-3">
                                                            <BarraProgresoMeta
                                                                montoRecaudado={recaudado}
                                                                meta={meta}
                                                                porcentaje={pct}
                                                                animar={false}
                                                            />
                                                        </div>
                                                    )}
                                                </li>
                                            );
                                        })}
                                    </ul>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    className={`${modalWaynaFooter} flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3`}
                >
                    <button
                        type="button"
                        onClick={onClose}
                        className="btn-wayna-secondary w-full sm:w-auto sm:min-w-[8rem]"
                    >
                        Cerrar
                    </button>
                    <Link
                        href={route('admin.emprendedores.edit', emprendedor.id)}
                        className="btn-wayna-primary w-full text-center sm:w-auto sm:min-w-[8rem]"
                    >
                        Editar emprendedor
                    </Link>
                </div>
            </div>
        </Modal>
    );
}
