import AdminFormField from '@/Components/Admin/AdminFormField';
import AdminMediaDropzone from '@/Components/Admin/AdminMediaDropzone';
import { adminFormStack, adminInputClass } from '@/Components/Admin/adminUi';
import { useEffect, useMemo } from 'react';

const MAX_GALERIA = 4;

function IconoSeccion({ children }) {
    return (
        <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-wayna-500 text-white shadow-md shadow-wayna-600/25">
            {children}
        </span>
    );
}

/**
 * Campos opcionales de medios públicos (T-A22) — diseño visual mejorado.
 */
export default function AdminEmprendedorMediosFields({
    data,
    setData,
    emprendedor,
    error,
    esEdicion,
}) {
    const fotoEmpresaActual = emprendedor?.foto_empresa
        ? `/storage/${emprendedor.foto_empresa}`
        : null;

    const vistaFotoEmpresa = useMemo(() => {
        if (data.foto_empresa instanceof File) {
            return URL.createObjectURL(data.foto_empresa);
        }
        return null;
    }, [data.foto_empresa]);

    useEffect(() => {
        return () => {
            if (vistaFotoEmpresa) {
                URL.revokeObjectURL(vistaFotoEmpresa);
            }
        };
    }, [vistaFotoEmpresa]);

    const galeriaActual = Array.isArray(emprendedor?.galeria) ? emprendedor.galeria : [];
    const conservar = Array.isArray(data.galeria_conservar) ? data.galeria_conservar : [];
    const nuevasCrear = Array.isArray(data.galeria) ? data.galeria : [];
    const nuevasEditar = Array.isArray(data.galeria_nuevas) ? data.galeria_nuevas : [];
    const archivosNuevos = esEdicion ? nuevasEditar : nuevasCrear;
    const totalGaleria = conservar.length + archivosNuevos.length;
    const cupoLibre = Math.max(0, MAX_GALERIA - totalGaleria);

    const previewsNuevas = useMemo(() => {
        return archivosNuevos
            .filter((f) => f instanceof File)
            .map((file) => ({
                file,
                url: URL.createObjectURL(file),
                name: file.name,
            }));
    }, [archivosNuevos]);

    useEffect(() => {
        return () => {
            previewsNuevas.forEach((p) => URL.revokeObjectURL(p.url));
        };
    }, [previewsNuevas]);

    const videoEsEnlace =
        typeof emprendedor?.video_url === 'string' &&
        (emprendedor.video_url.startsWith('http://') ||
            emprendedor.video_url.startsWith('https://'));
    const tieneVideoArchivo =
        emprendedor?.video_url && !videoEsEnlace && !data.quitar_video;

    const fotoEmpresaMostrar = vistaFotoEmpresa || fotoEmpresaActual;

    const toggleConservarGaleria = (ruta) => {
        const actual = new Set(conservar);
        if (actual.has(ruta)) {
            actual.delete(ruta);
        } else if (totalGaleria < MAX_GALERIA) {
            actual.add(ruta);
        }
        setData('galeria_conservar', [...actual]);
    };

    const agregarGaleriaNuevas = (archivos) => {
        const lista = Array.from(archivos ?? []);
        const maxNuevas = Math.max(0, MAX_GALERIA - conservar.length);
        const recortadas = lista.slice(0, maxNuevas);
        if (esEdicion) {
            setData('galeria_nuevas', recortadas);
        } else {
            setData('galeria', recortadas);
        }
    };

    const quitarArchivoNuevo = (indice) => {
        if (esEdicion) {
            setData(
                'galeria_nuevas',
                nuevasEditar.filter((_, i) => i !== indice),
            );
        } else {
            setData('galeria', nuevasCrear.filter((_, i) => i !== indice));
        }
    };

    const limpiarFotoEmpresa = () => setData('foto_empresa', null);

    return (
        <div className="admin-medios-panel mt-8 overflow-hidden rounded-3xl border border-wayna-200/80 bg-gradient-to-b from-wayna-50/70 via-white to-surface-muted/30 shadow-lg shadow-wayna-900/[0.04] ring-1 ring-black/[0.02]">
            <div className="border-b border-wayna-100 bg-gradient-to-r from-wayna-500 via-wayna-500 to-wayna-400 px-5 py-4 sm:px-6">
                <div className="flex items-start gap-3">
                    <IconoSeccion>
                        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M3 9a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 10.07 4h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 18.07 7H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                            <path strokeLinecap="round" strokeLinejoin="round" d="M15 13a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </IconoSeccion>
                    <div>
                        <p className="text-[11px] font-bold uppercase tracking-[0.2em] text-orange-100/95">
                            Perfil turista
                        </p>
                        <h3 className="text-lg font-bold text-white sm:text-xl">
                            Medios del emprendimiento
                        </h3>
                        <p className="mt-1 text-sm text-orange-50/90">
                            Opcional · local o productos (distinto a la foto de perfil arriba)
                        </p>
                    </div>
                </div>
            </div>

            <div className={`space-y-8 p-5 sm:p-6 ${adminFormStack}`}>
                {/* Foto empresa */}
                <section className="space-y-4">
                    <div className="flex items-center justify-between gap-2">
                        <h4 className="text-sm font-bold text-wayna-950">
                            Foto principal de la empresa
                        </h4>
                        {fotoEmpresaMostrar && (
                            <button
                                type="button"
                                onClick={limpiarFotoEmpresa}
                                className="text-xs font-semibold text-stone-500 underline-offset-2 hover:text-wayna-700 hover:underline"
                            >
                                Quitar selección
                            </button>
                        )}
                    </div>

                    <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-stretch">
                        {fotoEmpresaMostrar ? (
                            <div className="relative overflow-hidden rounded-2xl ring-2 ring-wayna-200 shadow-inner">
                                <img
                                    src={fotoEmpresaMostrar}
                                    alt=""
                                    className="aspect-[4/3] w-full object-cover lg:aspect-auto lg:min-h-[200px]"
                                />
                                <span className="absolute left-3 top-3 rounded-full bg-wayna-500 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow">
                                    {vistaFotoEmpresa ? 'Nueva' : 'Actual'}
                                </span>
                            </div>
                        ) : (
                            <div className="flex aspect-[4/3] items-center justify-center rounded-2xl border border-dashed border-wayna-100 bg-wayna-50/50 text-center lg:aspect-auto lg:min-h-[200px]">
                                <p className="px-4 text-xs font-medium text-stone-400">
                                    La vista previa aparece aquí
                                </p>
                            </div>
                        )}

                        <AdminMediaDropzone
                            id="foto_empresa"
                            accept="image/jpeg,image/png,image/webp"
                            title="Elegir imagen"
                            hint="JPG, PNG o WEBP · máx. 2 MB · local, productos o equipo"
                            badge="1 imagen"
                            onFiles={(files) =>
                                setData('foto_empresa', files?.[0] ?? null)
                            }
                        />
                    </div>
                    {error('foto_empresa') && (
                        <p className="text-sm font-medium text-red-600">{error('foto_empresa')}</p>
                    )}
                </section>

                {/* Galería */}
                <section className="space-y-4 rounded-2xl border border-wayna-100/90 bg-white/80 p-4 shadow-sm sm:p-5">
                    <div className="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h4 className="text-sm font-bold text-wayna-950">Galería</h4>
                            <p className="mt-0.5 text-xs text-stone-500">
                                Mostrá de 2 a 4 imágenes del trabajo (recomendado)
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="text-xs font-semibold text-stone-500">Cupo</span>
                            <div className="flex gap-1">
                                {Array.from({ length: MAX_GALERIA }).map((_, i) => (
                                    <span
                                        key={i}
                                        className={`h-2 w-6 rounded-full transition ${
                                            i < totalGaleria
                                                ? 'bg-wayna-500 shadow-sm'
                                                : 'bg-stone-200'
                                        }`}
                                    />
                                ))}
                            </div>
                            <span className="text-xs font-bold text-wayna-800">
                                {totalGaleria}/{MAX_GALERIA}
                            </span>
                        </div>
                    </div>

                    {esEdicion && galeriaActual.length > 0 && (
                        <div>
                            <p className="mb-2 text-xs font-bold uppercase tracking-wide text-wayna-800">
                                Imágenes guardadas
                            </p>
                            <ul className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                {galeriaActual.map((ruta) => {
                                    const activa = conservar.includes(ruta);
                                    return (
                                        <li key={ruta}>
                                            <button
                                                type="button"
                                                onClick={() => toggleConservarGaleria(ruta)}
                                                className={`group relative w-full overflow-hidden rounded-2xl ring-2 transition-all ${
                                                    activa
                                                        ? 'ring-wayna-500 shadow-md shadow-wayna-500/20'
                                                        : 'ring-stone-200 opacity-55 grayscale hover:opacity-80'
                                                }`}
                                            >
                                                <img
                                                    src={`/storage/${ruta}`}
                                                    alt=""
                                                    className="aspect-square w-full object-cover"
                                                />
                                                <span
                                                    className={`absolute inset-x-2 bottom-2 rounded-lg px-2 py-1 text-center text-[10px] font-bold ${
                                                        activa
                                                            ? 'bg-wayna-500 text-white'
                                                            : 'bg-stone-800/75 text-white'
                                                    }`}
                                                >
                                                    {activa ? '✓ Conservar' : 'Tocar para quitar'}
                                                </span>
                                            </button>
                                        </li>
                                    );
                                })}
                            </ul>
                        </div>
                    )}

                    {previewsNuevas.length > 0 && (
                        <div>
                            <p className="mb-2 text-xs font-bold uppercase tracking-wide text-wayna-800">
                                Por subir
                            </p>
                            <ul className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                {previewsNuevas.map((item, indice) => (
                                    <li
                                        key={`${item.name}-${indice}`}
                                        className="relative overflow-hidden rounded-2xl ring-2 ring-emerald-400/80 shadow-sm"
                                    >
                                        <img
                                            src={item.url}
                                            alt=""
                                            className="aspect-square w-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            onClick={() => quitarArchivoNuevo(indice)}
                                            className="absolute right-1.5 top-1.5 flex h-7 w-7 items-center justify-center rounded-full bg-black/65 text-white transition hover:bg-red-600"
                                            aria-label="Quitar imagen"
                                        >
                                            ×
                                        </button>
                                        <span className="absolute inset-x-1 bottom-1 truncate rounded bg-black/50 px-1 py-0.5 text-[9px] font-medium text-white">
                                            {item.name}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <AdminMediaDropzone
                        id="galeria_archivos"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        disabled={cupoLibre === 0}
                        compact
                        title={
                            cupoLibre === 0
                                ? 'Galería completa'
                                : 'Agregar imágenes a la galería'
                        }
                        hint={
                            cupoLibre === 0
                                ? 'Quitá una imagen para liberar espacio'
                                : `Podés elegir hasta ${cupoLibre} más · máx. 2 MB c/u`
                        }
                        badge={`${cupoLibre} disponible${cupoLibre === 1 ? '' : 's'}`}
                        onFiles={agregarGaleriaNuevas}
                    />
                    {(error('galeria') || error('galeria_nuevas')) && (
                        <p className="text-sm font-medium text-red-600">
                            {error('galeria') || error('galeria_nuevas')}
                        </p>
                    )}
                </section>

                {/* Video */}
                <section className="space-y-4 rounded-2xl border border-stone-200/80 bg-surface-muted/40 p-4 sm:p-5">
                    <h4 className="text-sm font-bold text-wayna-950">Video corto (opcional)</h4>

                    {tieneVideoArchivo && (
                        <div className="flex items-start gap-3 rounded-xl border border-wayna-200 bg-wayna-50/80 px-4 py-3">
                            <span className="mt-0.5 text-lg" aria-hidden>
                                🎬
                            </span>
                            <p className="text-sm text-stone-700">
                                Ya hay un video en el perfil. Subí otro archivo, pegá un enlace o
                                marcá quitar abajo.
                            </p>
                        </div>
                    )}

                    <div className="grid gap-4 sm:grid-cols-2">
                        <AdminMediaDropzone
                            id="video"
                            accept="video/mp4,video/webm"
                            icon="video"
                            compact
                            title="Subir archivo"
                            hint="MP4 o WebM · máx. 15 MB"
                            onFiles={(files) => {
                                setData('video', files?.[0] ?? null);
                                if (files?.[0]) {
                                    setData('video_enlace', '');
                                }
                            }}
                        />

                        <AdminFormField
                            id="video_enlace"
                            label="Enlace YouTube / Vimeo"
                            optional
                            error={error('video_enlace')}
                        >
                            <input
                                id="video_enlace"
                                type="url"
                                value={data.video_enlace ?? ''}
                                placeholder="https://youtube.com/..."
                                onChange={(e) => {
                                    setData('video_enlace', e.target.value);
                                    if (e.target.value) {
                                        setData('video', null);
                                    }
                                }}
                                className={adminInputClass}
                            />
                        </AdminFormField>
                    </div>

                    {error('video') && (
                        <p className="text-sm font-medium text-red-600">{error('video')}</p>
                    )}

                    {esEdicion && (emprendedor?.video_url || tieneVideoArchivo) && (
                        <label className="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 bg-white px-4 py-3 transition hover:border-red-200 hover:bg-red-50/50">
                            <input
                                type="checkbox"
                                checked={Boolean(data.quitar_video)}
                                onChange={(e) => setData('quitar_video', e.target.checked)}
                                className="h-4 w-4 rounded border-stone-300 text-red-600 focus:ring-red-400"
                            />
                            <span className="text-sm font-semibold text-stone-700">
                                Quitar video del perfil público
                            </span>
                        </label>
                    )}
                </section>
            </div>
        </div>
    );
}

