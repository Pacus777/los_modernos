import BarraProgresoMeta from '@/Components/BarraProgresoMeta';
import {
    modalWaynaBody,
    modalWaynaFooter,
    modalWaynaShell,
} from '@/Components/Admin/adminUi';
import Modal from '@/Components/Modal';
import { descargarDataUrl, exportarTarjetaQrPng } from '@/utils/exportQrTarjetaCanvas';
import { textosTarjetaQr } from '@/utils/qrTarjetaTextos';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

function formatearBs(valor) {
    const n = Number(valor);
    if (Number.isNaN(n)) {
        return '0,00';
    }
    return n.toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function slugArchivo(nombre) {
    return nombre
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
}

/**
 * Modal de perfil + QR del emprendedor (admin).
 * La descarga genera un PNG con tarjeta informativa; el modal se mantiene como antes.
 */
export default function EmprendedorQrModal({
    show = false,
    onClose,
    emprendedor,
    qrSrc,
    fotoSrc = null,
    editHref,
}) {
    const [exportando, setExportando] = useState(false);

    if (!show || !emprendedor || !qrSrc) {
        return null;
    }

    const nombreCompleto =
        `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim() || 'Emprendedor';

    const campanaActiva =
        emprendedor.campanas?.find((c) => c.estado === 'activa') ?? null;
    const metaCampana = campanaActiva ? Number(campanaActiva.meta_apoyo) : 0;
    const recaudado = campanaActiva ? Number(campanaActiva.monto_recaudado) : 0;
    const metaReferencia = Number(emprendedor.meta_monto) || 0;

    const metaProgreso = metaCampana > 0 ? metaCampana : metaReferencia;
    const porcentaje =
        metaProgreso > 0
            ? Math.min(100, Math.round((recaudado / metaProgreso) * 100))
            : 0;

    const estiloEstado =
        emprendedor.estado === 'activo'
            ? 'bg-wayna-100 text-wayna-900 ring-1 ring-wayna-300/70'
            : 'bg-stone-100 text-stone-700 ring-1 ring-stone-200';

    const descargarQr = async (locale) => {
        setExportando(true);
        const textos = textosTarjetaQr(locale, campanaActiva?.titulo);
        const base = slugArchivo(nombreCompleto) || 'emprendedor';

        try {
            const dataUrl = await exportarTarjetaQrPng({
                qrSrc,
                nombreCompleto,
                titulo: textos.titulo,
                subtitulo: textos.subtitulo,
                marca: textos.marca,
            });
            descargarDataUrl(
                dataUrl,
                `tarjeta-qr-wayna-${base}-${textos.sufijoArchivo}.png`,
            );
        } catch {
            const enlace = document.createElement('a');
            enlace.href = qrSrc;
            enlace.download = `qr-wayna-${base}-${textos.sufijoArchivo}.png`;
            document.body.appendChild(enlace);
            enlace.click();
            enlace.remove();
        } finally {
            setExportando(false);
        }
    };

    const imprimirQr = () => {
        document.body.classList.add('printing-emprendedor-qr');
        const limpiar = () => document.body.classList.remove('printing-emprendedor-qr');
        window.addEventListener('afterprint', limpiar, { once: true });
        window.print();
    };

    return (
        <Modal show={show} onClose={onClose} maxWidth="xl">
            <div id="emprendedor-qr-print" className={modalWaynaShell}>
                <div className="header-wayna-gradient shrink-0 px-4 py-3 sm:px-5">
                    <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-white/85">
                        QR del emprendedor
                    </p>
                    <h2 className="mt-1 text-lg font-bold text-white sm:text-xl">
                        {nombreCompleto}
                    </h2>
                    {campanaActiva?.titulo ? (
                        <p className="mt-1 text-sm text-white/90">
                            Campaña activa: {campanaActiva.titulo}
                        </p>
                    ) : null}
                </div>

                <div className={`${modalWaynaBody} p-4 sm:p-5`}>
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-stretch">
                        <div className="flex shrink-0 flex-col items-center gap-2 lg:w-28">
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
                                className={`inline-flex rounded-full px-3 py-1 text-xs font-bold capitalize ${estiloEstado}`}
                            >
                                {emprendedor.estado}
                            </span>
                        </div>

                        <div className="min-w-0 flex-1 space-y-4">
                            <div>
                                <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-wayna-700">
                                    Información del emprendimiento
                                </p>
                                <p className="mt-2 text-sm leading-relaxed text-stone-700">
                                    {emprendedor.descripcion?.trim() ||
                                        'Sin descripción registrada.'}
                                </p>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-2">
                                <div className="rounded-xl border border-wayna-100 bg-wayna-50/60 px-4 py-3">
                                    <p className="text-[10px] font-bold uppercase tracking-wide text-wayna-700">
                                        Meta de referencia
                                    </p>
                                    <p className="mt-1 font-mono text-base font-bold text-wayna-950">
                                        Bs {formatearBs(metaReferencia)}
                                    </p>
                                </div>
                                <div className="rounded-xl border border-wayna-100 bg-wayna-50/60 px-4 py-3">
                                    <p className="text-[10px] font-bold uppercase tracking-wide text-wayna-700">
                                        {campanaActiva ? 'Meta campaña activa' : 'Recaudado'}
                                    </p>
                                    <p className="mt-1 font-mono text-base font-bold text-wayna-950">
                                        {campanaActiva
                                            ? `Bs ${formatearBs(metaCampana)}`
                                            : `Bs ${formatearBs(recaudado)}`}
                                    </p>
                                </div>
                            </div>

                            {campanaActiva ? (
                                <div>
                                    <p className="mb-2 text-xs font-semibold text-stone-600">
                                        Progreso de la campaña
                                    </p>
                                    <BarraProgresoMeta
                                        montoRecaudado={recaudado}
                                        meta={metaProgreso}
                                        porcentaje={porcentaje}
                                    />
                                </div>
                            ) : (
                                <p className="rounded-xl border border-dashed border-wayna-200 bg-surface-muted/80 px-4 py-3 text-sm text-stone-600">
                                    Este emprendedor no tiene una campaña activa. El QR
                                    enlaza a su perfil público.
                                </p>
                            )}
                        </div>

                        <div className="flex flex-col items-center gap-2 lg:w-44 lg:border-l lg:border-wayna-100 lg:pl-4">
                            {editHref ? (
                                <Link
                                    href={editHref}
                                    onClick={onClose}
                                    className="no-print-qr w-full rounded-xl bg-wayna-500 px-4 py-2 text-center text-sm font-bold text-white shadow-sm transition hover:bg-wayna-600"
                                >
                                    Editar
                                </Link>
                            ) : null}
                            <div className="rounded-2xl border-2 border-wayna-200 bg-white p-2 shadow-inner">
                                <img
                                    src={qrSrc}
                                    alt={`QR ${nombreCompleto}`}
                                    className="h-32 w-32 object-contain sm:h-36 sm:w-36"
                                />
                            </div>
                            <p className="text-center text-xs text-stone-500">
                                Escaneá para abrir el perfil público
                            </p>
                            <div className="no-print-qr flex w-full flex-col gap-2">
                                <p className="text-center text-[10px] font-bold uppercase tracking-wide text-stone-500">
                                    Descargar tarjeta
                                </p>
                                <div className="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        onClick={() => descargarQr('es')}
                                        disabled={exportando}
                                        className="btn-wayna-primary text-xs sm:text-sm"
                                    >
                                        {exportando ? '…' : 'Español'}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => descargarQr('en')}
                                        disabled={exportando}
                                        className="btn-wayna-primary text-xs sm:text-sm"
                                    >
                                        {exportando ? '…' : 'English'}
                                    </button>
                                </div>
                                <button
                                    type="button"
                                    onClick={imprimirQr}
                                    disabled={exportando}
                                    className="btn-wayna-secondary w-full text-sm"
                                >
                                    Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div className={`${modalWaynaFooter} no-print-qr flex justify-center`}>
                    <button
                        type="button"
                        onClick={onClose}
                        className="btn-wayna-primary min-w-[10rem]"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </Modal>
    );
}
