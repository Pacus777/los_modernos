import { modalWaynaBody, modalWaynaFooter, modalWaynaShell } from '@/Components/Admin/adminUi';
import Modal from '@/Components/Modal';
import { useState } from 'react';

function formatearMonto(monto) {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    }).format(Number(monto || 0));
}

function FilaDetalle({ etiqueta, valor, descripcion, destacado = false, aviso = false }) {
    return (
        <div
            className={`flex items-start justify-between gap-4 rounded-xl border px-4 py-3 ${
                destacado
                    ? 'border-wayna-300 bg-gradient-to-r from-wayna-50 to-white'
                    : aviso
                      ? 'border-amber-200 bg-amber-50/80'
                      : 'border-wayna-100 bg-white'
            }`}
        >
            <div className="min-w-0">
                <p className="text-sm font-bold text-wayna-950">{etiqueta}</p>
                {descripcion && (
                    <p className="mt-0.5 text-xs text-stone-600">{descripcion}</p>
                )}
            </div>
            <p className="shrink-0 text-right text-sm font-black text-wayna-950 sm:text-base">
                {valor}
            </p>
        </div>
    );
}

function TabBoton({ activo, onClick, children }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`rounded-lg px-3 py-1.5 text-xs font-bold transition ${
                activo
                    ? 'bg-wayna-700 text-white shadow-sm'
                    : 'bg-white text-wayna-800 ring-1 ring-wayna-200 hover:bg-wayna-50'
            }`}
        >
            {children}
        </button>
    );
}

function FilaDesglose({ titulo, subtitulo, item, totalReferencia }) {
    const pct =
        totalReferencia > 0
            ? Math.min(100, Math.round((item.total_validado / totalReferencia) * 100))
            : 0;

    return (
        <div className="rounded-xl border border-wayna-100 bg-white px-4 py-3">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-bold text-wayna-950">{titulo}</p>
                    {subtitulo && (
                        <p className="mt-0.5 truncate text-xs text-stone-500">{subtitulo}</p>
                    )}
                </div>
                <div className="shrink-0 text-right">
                    <p className="text-sm font-black text-wayna-950">
                        {formatearMonto(item.total_validado)}
                    </p>
                    <p className="text-[10px] text-stone-500">validado</p>
                </div>
            </div>
            <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-wayna-100">
                <div
                    className="h-full rounded-full bg-wayna-500"
                    style={{ width: `${pct}%` }}
                />
            </div>
            <div className="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-stone-600">
                <span>General: {formatearMonto(item.total_general)}</span>
                {item.total_pendiente > 0 && (
                    <span className="text-amber-700">
                        Pendiente: {formatearMonto(item.total_pendiente)}
                    </span>
                )}
                <span>Efectivo: {formatearMonto(item.total_efectivo)}</span>
                <span>QR: {formatearMonto(item.total_qr)}</span>
                <span>{item.aportes_validados ?? 0} aporte(s)</span>
            </div>
        </div>
    );
}

function ListaDesglose({ items, vacio, renderItem }) {
    if (!items?.length) {
        return (
            <p className="rounded-xl border border-wayna-100 bg-wayna-50/40 px-4 py-8 text-center text-sm text-stone-600">
                {vacio}
            </p>
        );
    }

    return (
        <div className="max-h-56 space-y-2 overflow-y-auto pr-1">{items.map(renderItem)}</div>
    );
}

/**
 * Modal de desglose de recaudación (T-A4): resumen global + por emprendedor + por campaña.
 */
export default function RecaudacionDetalleModal({
    show = false,
    onClose,
    detalle = {},
    filtros = {},
}) {
    const [pestaña, setPestaña] = useState('resumen');

    const periodo =
        filtros?.fecha_inicio || filtros?.fecha_fin
            ? `${filtros.fecha_inicio || '…'} → ${filtros.fecha_fin || '…'}`
            : 'Todo el historial';

    const tienePendiente = Number(detalle.total_pendiente || 0) > 0;
    const porEmprendedor = detalle.por_emprendedor ?? [];
    const porCampana = detalle.por_campana ?? [];
    const totalValidado = Number(detalle.total_validado || 0);

    const cerrar = () => {
        setPestaña('resumen');
        onClose?.();
    };

    return (
        <Modal show={show} onClose={cerrar} maxWidth="xl">
            <div className={modalWaynaShell}>
                <div className="header-wayna-gradient shrink-0 px-4 py-3 sm:px-5">
                    <h2 className="text-lg font-bold text-white">
                        Detalle de recaudación
                    </h2>
                    <p className="mt-1 text-sm text-white/85">Período: {periodo}</p>
                </div>

                <div className={`${modalWaynaBody} space-y-4 px-4 py-4 sm:px-5`}>
                    <div className="flex flex-wrap gap-2">
                        <TabBoton
                            activo={pestaña === 'resumen'}
                            onClick={() => setPestaña('resumen')}
                        >
                            Resumen general
                        </TabBoton>
                        <TabBoton
                            activo={pestaña === 'emprendedores'}
                            onClick={() => setPestaña('emprendedores')}
                        >
                            Por emprendedor ({porEmprendedor.length})
                        </TabBoton>
                        <TabBoton
                            activo={pestaña === 'campanas'}
                            onClick={() => setPestaña('campanas')}
                        >
                            Por campaña ({porCampana.length})
                        </TabBoton>
                    </div>

                    {pestaña === 'resumen' && (
                        <div className="space-y-3">
                            <FilaDetalle
                                destacado
                                etiqueta="Total general"
                                descripcion="Validado + pendiente (sin rechazados)"
                                valor={formatearMonto(detalle.total_general)}
                            />
                            <div className="grid gap-3 sm:grid-cols-2">
                                <FilaDetalle
                                    etiqueta="Por efectivo"
                                    descripcion="Donaciones validadas en caja"
                                    valor={formatearMonto(detalle.total_efectivo)}
                                />
                                <FilaDetalle
                                    etiqueta="Por QR bancario"
                                    descripcion="Donaciones validadas vía QR"
                                    valor={formatearMonto(detalle.total_qr)}
                                />
                            </div>
                            <FilaDetalle
                                etiqueta="Total validado"
                                descripcion={`${detalle.aportes_validados ?? 0} aporte(s) confirmados`}
                                valor={formatearMonto(detalle.total_validado)}
                            />
                            {tienePendiente ? (
                                <FilaDetalle
                                    aviso
                                    etiqueta="Total pendiente"
                                    descripcion={`${detalle.aportes_pendientes ?? 0} aporte(s) por confirmar`}
                                    valor={formatearMonto(detalle.total_pendiente)}
                                />
                            ) : (
                                <p className="rounded-xl border border-wayna-100 bg-wayna-50/50 px-4 py-2.5 text-xs text-stone-600">
                                    No hay montos pendientes de validación en este período.
                                </p>
                            )}
                            <p className="text-center text-[11px] leading-relaxed text-stone-500">
                                Usá las pestañas para ver el mismo desglose por emprendedor o
                                por campaña de cada uno.
                            </p>
                        </div>
                    )}

                    {pestaña === 'emprendedores' && (
                        <div className="space-y-2">
                            <p className="text-xs text-stone-600">
                                Cada emprendedor puede tener varias campañas; aquí se suma todo
                                lo recaudado en sus campañas.
                            </p>
                            <ListaDesglose
                                items={porEmprendedor}
                                vacio="Ningún emprendedor con donaciones en este período."
                                renderItem={(item) => (
                                    <FilaDesglose
                                        key={item.id}
                                        titulo={item.nombre}
                                        subtitulo={`Estado: ${item.estado}`}
                                        item={item}
                                        totalReferencia={totalValidado}
                                    />
                                )}
                            />
                        </div>
                    )}

                    {pestaña === 'campanas' && (
                        <div className="space-y-2">
                            <p className="text-xs text-stone-600">
                                Cada fila es una campaña concreta y el emprendedor al que
                                pertenece.
                            </p>
                            <ListaDesglose
                                items={porCampana}
                                vacio="Ninguna campaña con donaciones en este período."
                                renderItem={(item) => (
                                    <FilaDesglose
                                        key={item.id}
                                        titulo={item.titulo}
                                        subtitulo={`${item.emprendedor} · ${item.estado}`}
                                        item={item}
                                        totalReferencia={totalValidado}
                                    />
                                )}
                            />
                        </div>
                    )}

                </div>

                <div className={`${modalWaynaFooter} flex justify-end`}>
                    <button
                        type="button"
                        onClick={cerrar}
                        className="rounded-xl border border-wayna-200 bg-white px-4 py-2.5 text-sm font-bold text-wayna-800 transition hover:bg-wayna-50"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </Modal>
    );
}
