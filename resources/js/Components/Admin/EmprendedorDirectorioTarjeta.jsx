import { calcularPorcentajeMeta } from '@/Components/BarraProgresoMeta';
import { descargarDataUrl, exportarTarjetaQrPng } from '@/utils/exportQrTarjetaCanvas';
import { campanaVigentePorFechas } from '@/utils/campanaVigente';
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
 * Tarjeta marrón del directorio (maqueta referencia): foto, info, meta, barra, Editar + QR visibles.
 */
export default function EmprendedorDirectorioTarjeta({
    emprendedor,
    fotoSrc,
    qrSrc = '',
    editHref,
    onGenerarQr,
    generandoQr = false,
    onVerFicha,
    onDesactivar,
    puedeDesactivar = false,
}) {
    const [exportando, setExportando] = useState(false);

    if (!emprendedor) {
        return null;
    }

    const tieneQr = Boolean(qrSrc?.trim());
    const nombreCompleto =
        `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim() || 'Emprendedor';

    const campanaActiva =
        emprendedor.campanas?.find((c) => campanaVigentePorFechas(c)) ?? null;
    const metaCampana = campanaActiva ? Number(campanaActiva.meta_apoyo) : 0;
    const recaudado = campanaActiva ? Number(campanaActiva.monto_recaudado) : 0;
    const metaReferencia = Number(emprendedor.meta_monto) || 0;
    const metaProgreso = metaCampana > 0 ? metaCampana : metaReferencia;
    const porcentaje = calcularPorcentajeMeta(recaudado, metaProgreso);

    const descargarQr = async (locale) => {
        if (!tieneQr) {
            return;
        }
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

    const estiloEstado =
        emprendedor.estado === 'activo'
            ? 'bg-white/20 text-amber-50 ring-1 ring-white/25'
            : 'bg-black/25 text-stone-200 ring-1 ring-white/10';

    return (
        <article className="overflow-hidden rounded-3xl bg-[#5c4033] shadow-xl shadow-stone-900/25 ring-1 ring-black/10">
            <div className="flex flex-col gap-5 p-5 sm:flex-row sm:items-stretch sm:gap-6 sm:p-6 lg:p-7">
                <div className="flex shrink-0 justify-center sm:justify-start">
                    {fotoSrc ? (
                        <img
                            src={fotoSrc}
                            alt=""
                            className="h-36 w-36 rounded-2xl object-cover shadow-lg ring-2 ring-white/20 sm:h-40 sm:w-40"
                        />
                    ) : (
                        <div className="flex h-36 w-36 items-center justify-center rounded-2xl bg-[#4a3228] text-4xl font-black text-amber-100/90 shadow-lg ring-2 ring-white/15 sm:h-40 sm:w-40">
                            {emprendedor.nombre?.charAt(0) ?? '?'}
                        </div>
                    )}
                </div>

                <div className="min-w-0 flex-1 space-y-4">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <button
                            type="button"
                            onClick={onVerFicha}
                            className="cursor-pointer rounded-xl border border-transparent text-left transition hover:border-white/10 hover:bg-white/5 sm:-m-2 sm:p-2"
                        >
                            <p className="text-sm font-bold text-amber-100/95">
                                Información de la empresa
                            </p>
                            <p className="mt-2 text-sm leading-relaxed text-stone-100/90">
                                {emprendedor.descripcion?.trim() ||
                                    'Sin descripción registrada.'}
                            </p>
                        </button>
                        <div className="sm:text-right">
                            <p className="text-sm font-bold text-amber-100/95">Meta</p>
                            <p className="mt-2 font-mono text-2xl font-black text-white">
                                Bs {formatearBs(metaProgreso > 0 ? metaProgreso : metaReferencia)}
                            </p>
                            {campanaActiva?.titulo ? (
                                <p className="mt-1 text-xs text-stone-200/80">
                                    {campanaActiva.titulo}
                                </p>
                            ) : null}
                        </div>
                    </div>

                    <p className="text-sm leading-relaxed text-stone-100/85">
                        {nombreCompleto}
                        {emprendedor.estado ? (
                            <span
                                className={`ml-2 inline-flex rounded-full px-2 py-0.5 text-xs font-bold capitalize ${estiloEstado}`}
                            >
                                {emprendedor.estado}
                            </span>
                        ) : null}
                    </p>

                    <div>
                        <p className="mb-2 text-[11px] font-bold uppercase tracking-wide text-amber-100/70">
                            Barra de progreso
                        </p>
                        <div className="relative h-10 overflow-hidden rounded-full bg-[#b8d4eb] shadow-inner">
                            <div
                                className="absolute inset-y-0 left-0 flex items-center rounded-full bg-[#1e4a7a] px-4 transition-[width] duration-500"
                                style={{
                                    width: `${Math.min(100, Math.max(porcentaje, metaProgreso > 0 ? 8 : 0))}%`,
                                }}
                            >
                                <span className="truncate text-xs font-bold text-white sm:text-sm">
                                    {metaProgreso > 0 ? `${porcentaje}% recaudado` : 'Sin meta'}
                                </span>
                            </div>
                        </div>
                        <p className="mt-2 text-xs text-stone-200/75">
                            Bs {formatearBs(recaudado)} de Bs {formatearBs(metaProgreso)}
                        </p>
                    </div>
                </div>

                <div className="flex flex-col items-center gap-3 sm:w-44 sm:shrink-0">
                    {editHref ? (
                        <Link
                            href={editHref}
                            className="w-full rounded-full bg-[#f5c518] px-5 py-2.5 text-center text-sm font-bold text-stone-900 shadow-md transition hover:bg-[#ffd84d]"
                        >
                            Editar
                        </Link>
                    ) : null}

                    {tieneQr ? (
                        <>
                            <div className="rounded-2xl bg-white p-2.5 shadow-lg">
                                <img
                                    src={qrSrc}
                                    alt={`QR ${nombreCompleto}`}
                                    className="h-36 w-36 object-contain sm:h-40 sm:w-40"
                                />
                            </div>
                            <div className="flex w-full gap-2">
                                <button
                                    type="button"
                                    onClick={() => descargarQr('es')}
                                    disabled={exportando}
                                    className="flex-1 rounded-full border border-white/25 bg-white/10 px-2 py-1.5 text-xs font-semibold text-white transition hover:bg-white/20"
                                >
                                    {exportando ? '…' : 'ES'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => descargarQr('en')}
                                    disabled={exportando}
                                    className="flex-1 rounded-full border border-white/25 bg-white/10 px-2 py-1.5 text-xs font-semibold text-white transition hover:bg-white/20"
                                >
                                    {exportando ? '…' : 'EN'}
                                </button>
                            </div>
                        </>
                    ) : (
                        <div className="flex h-36 w-36 flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-amber-200/50 bg-[#4a3228]/80 p-4 text-center sm:h-40 sm:w-40">
                            <p className="text-xs font-semibold leading-snug text-amber-100/90">
                                QR pendiente
                            </p>
                            <button
                                type="button"
                                onClick={onGenerarQr}
                                disabled={generandoQr || !onGenerarQr}
                                className="w-full rounded-full bg-[#f5c518] px-3 py-2 text-xs font-bold text-stone-900 shadow-md transition hover:bg-[#ffd84d] disabled:opacity-60"
                            >
                                {generandoQr ? 'Generando…' : 'Generar QR'}
                            </button>
                        </div>
                    )}
                </div>
            </div>

            <div className="flex flex-wrap items-center justify-between gap-2 border-t border-white/10 px-5 py-3 sm:px-6">
                <div className="flex flex-wrap gap-3 text-xs sm:text-sm">
                    {onVerFicha ? (
                        <button
                            type="button"
                            onClick={onVerFicha}
                            className="font-semibold text-amber-100/90 underline decoration-white/30 underline-offset-2 hover:text-white"
                        >
                            Ver ficha completa
                        </button>
                    ) : null}
                    {puedeDesactivar && onDesactivar ? (
                        <button
                            type="button"
                            onClick={onDesactivar}
                            className="font-semibold text-red-200 underline decoration-red-300/40 underline-offset-2 hover:text-red-100"
                        >
                            Desactivar
                        </button>
                    ) : null}
                </div>
            </div>
        </article>
    );
}
