import { useEffect, useMemo, useState } from 'react';

function tarjetaDesdeUrl(id, etiqueta, url, esNueva = false) {
    if (!url) {
        return null;
    }
    return { id, etiqueta, url, esNueva };
}

/**
 * Vista previa dinámica de todas las imágenes del formulario emprendedor.
 */
export default function AdminEmprendedorVistaPreviaMedios({ data, emprendedor, fotoPerfilUrl }) {
    const [ampliada, setAmpliada] = useState(null);

    const items = useMemo(() => {
        const lista = [];

        const perfil = tarjetaDesdeUrl(
            'perfil',
            'Portada perfil',
            fotoPerfilUrl,
            data.fotografia instanceof File,
        );
        if (perfil) {
            lista.push(perfil);
        }

        let empresaUrl = null;
        if (data.foto_empresa instanceof File) {
            empresaUrl = URL.createObjectURL(data.foto_empresa);
        } else if (emprendedor?.foto_empresa) {
            empresaUrl = `/storage/${emprendedor.foto_empresa}`;
        }
        const empresaEsArchivo = data.foto_empresa instanceof File;
        const empresa = tarjetaDesdeUrl(
            'empresa',
            'Foto empresa',
            empresaUrl,
            empresaEsArchivo,
        );
        if (empresa) {
            if (empresaEsArchivo) {
                empresa._blob = true;
            }
            lista.push(empresa);
        }

        const conservar = Array.isArray(data.galeria_conservar) ? data.galeria_conservar : [];
        conservar.forEach((ruta, i) => {
            lista.push({
                id: `gal-c-${ruta}`,
                etiqueta: `Galería ${i + 1}`,
                url: `/storage/${ruta}`,
                esNueva: false,
            });
        });

        const archivosGaleria = [
            ...(Array.isArray(data.galeria) ? data.galeria : []),
            ...(Array.isArray(data.galeria_nuevas) ? data.galeria_nuevas : []),
        ];
        archivosGaleria.forEach((file, i) => {
            if (file instanceof File) {
                lista.push({
                    id: `gal-n-${file.name}-${i}`,
                    etiqueta: `Galería nueva ${i + 1}`,
                    url: URL.createObjectURL(file),
                    esNueva: true,
                    _blob: true,
                });
            }
        });

        return lista;
    }, [data, emprendedor, fotoPerfilUrl]);

    const blobs = useMemo(
        () => items.filter((it) => it._blob).map((it) => it.url),
        [items],
    );

    useEffect(() => {
        return () => blobs.forEach((u) => URL.revokeObjectURL(u));
    }, [blobs]);

    const tieneVideo =
        (data.video instanceof File && data.video.size > 0) ||
        Boolean(data.video_enlace?.trim()) ||
        (emprendedor?.video_url && !data.quitar_video);

    if (items.length === 0 && !tieneVideo) {
        return (
            <div className="rounded-2xl border border-dashed border-stone-200 bg-stone-50/80 px-4 py-8 text-center">
                <p className="text-sm font-semibold text-stone-500">
                    Las imágenes que elijas aparecerán aquí en vivo
                </p>
            </div>
        );
    }

    return (
        <div className="admin-vista-previa-medios rounded-2xl border border-wayna-100 bg-white p-4 shadow-sm sm:p-5">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p className="text-[11px] font-bold uppercase tracking-[0.18em] text-wayna-800">
                        Vista previa en vivo
                    </p>
                    <p className="text-xs text-stone-500">
                        {items.length} imagen{items.length === 1 ? '' : 'es'}
                        {tieneVideo ? ' · video incluido' : ''}
                    </p>
                </div>
                <span className="inline-flex items-center gap-1.5 rounded-full bg-wayna-500/10 px-3 py-1 text-[11px] font-bold text-wayna-800 ring-1 ring-wayna-200">
                    <span className="relative flex h-2 w-2">
                        <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-wayna-400 opacity-75" />
                        <span className="relative inline-flex h-2 w-2 rounded-full bg-wayna-500" />
                    </span>
                    Actualización automática
                </span>
            </div>

            <ul className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                {items.map((item) => (
                    <li
                        key={item.id}
                        className="admin-vista-previa-item animate-[fadeIn_0.35s_ease-out]"
                    >
                        <button
                            type="button"
                            onClick={() => setAmpliada(item)}
                            className="group relative w-full overflow-hidden rounded-xl ring-2 ring-wayna-100 transition hover:ring-wayna-400 hover:shadow-md"
                        >
                            <img
                                src={item.url}
                                alt=""
                                className="aspect-square w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                            <span className="absolute left-1.5 top-1.5 max-w-[90%] truncate rounded-md bg-black/55 px-1.5 py-0.5 text-[9px] font-bold text-white">
                                {item.etiqueta}
                            </span>
                            {item.esNueva && (
                                <span className="absolute right-1.5 top-1.5 rounded-md bg-emerald-500 px-1.5 py-0.5 text-[9px] font-bold text-white">
                                    Nueva
                                </span>
                            )}
                        </button>
                    </li>
                ))}

                {tieneVideo && (
                    <li className="flex aspect-square items-center justify-center rounded-xl border-2 border-dashed border-wayna-200 bg-gradient-to-br from-wayna-50 to-white">
                        <div className="text-center px-2">
                            <span className="text-2xl" aria-hidden>
                                ▶
                            </span>
                            <p className="mt-1 text-[10px] font-bold uppercase text-wayna-800">
                                Video
                            </p>
                        </div>
                    </li>
                )}
            </ul>

            {ampliada && (
                <div
                    className="fixed inset-0 z-[60] flex items-center justify-center bg-black/85 p-4"
                    role="dialog"
                    aria-modal="true"
                    onClick={() => setAmpliada(null)}
                >
                    <button
                        type="button"
                        className="absolute right-4 top-4 z-10 rounded-full bg-white px-3 py-1.5 text-sm font-bold text-wayna-900 shadow"
                        onClick={() => setAmpliada(null)}
                    >
                        Cerrar
                    </button>
                    <figure className="max-h-[90vh] max-w-full" onClick={(e) => e.stopPropagation()}>
                        <img
                            src={ampliada.url}
                            alt=""
                            className="max-h-[85vh] rounded-2xl object-contain shadow-2xl"
                        />
                        <figcaption className="mt-2 text-center text-sm font-semibold text-white">
                            {ampliada.etiqueta}
                        </figcaption>
                    </figure>
                </div>
            )}
        </div>
    );
}

