import AdminMediaDropzone from '@/Components/Admin/AdminMediaDropzone';
import { useEffect, useMemo } from 'react';

/**
 * Fotografía de perfil (cabecera turista) — vista previa dinámica.
 */
export default function AdminFotografiaPerfilField({
    fotografia,
    setFotografia,
    urlActual = null,
    error = null,
    esEdicion = false,
    nombreCompleto = '',
}) {
    const vistaNueva = useMemo(() => {
        if (fotografia instanceof File) {
            return URL.createObjectURL(fotografia);
        }
        return null;
    }, [fotografia]);

    useEffect(() => {
        return () => {
            if (vistaNueva) {
                URL.revokeObjectURL(vistaNueva);
            }
        };
    }, [vistaNueva]);

    const imagenMostrar = vistaNueva || urlActual;
    const iniciales = nombreCompleto
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((p) => p[0]?.toUpperCase())
        .join('');

    return (
        <section className="overflow-hidden rounded-2xl border border-wayna-200/80 bg-gradient-to-br from-surface-muted/80 via-white to-wayna-50/50 shadow-sm ring-1 ring-black/[0.02]">
            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-wayna-100 bg-wayna-50/60 px-4 py-3 sm:px-5">
                <div>
                    <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-wayna-800">
                        Fotografía de perfil
                    </p>
                    <p className="text-xs text-stone-500">
                        Rostro o retrato del emprendedor en la portada (no es la foto del
                        negocio)
                    </p>
                </div>
                {imagenMostrar && (
                    <button
                        type="button"
                        onClick={() => setFotografia(null)}
                        className="rounded-lg border border-stone-200 bg-white px-2.5 py-1 text-xs font-bold text-stone-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700"
                    >
                        Quitar foto
                    </button>
                )}
            </div>

            <div className="grid gap-4 p-4 sm:grid-cols-[minmax(0,200px)_1fr] sm:items-stretch sm:p-5 lg:grid-cols-[minmax(0,220px)_1fr]">
                <div
                    className={`relative flex items-center justify-center overflow-hidden rounded-2xl transition-all duration-300 ${
                        imagenMostrar
                            ? 'ring-2 ring-wayna-400 shadow-lg shadow-wayna-500/15'
                            : 'border-2 border-dashed border-wayna-100 bg-wayna-50/40'
                    }`}
                >
                    {imagenMostrar ? (
                        <>
                            <img
                                src={imagenMostrar}
                                alt=""
                                className="aspect-[3/4] w-full object-cover sm:min-h-[240px]"
                            />
                            <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent" />
                            <div className="absolute inset-x-0 bottom-0 p-3">
                                {nombreCompleto ? (
                                    <p className="truncate text-sm font-bold text-white drop-shadow">
                                        {nombreCompleto}
                                    </p>
                                ) : null}
                                <span
                                    className={`mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase ${
                                        vistaNueva
                                            ? 'bg-emerald-500 text-white'
                                            : 'bg-white/90 text-wayna-900'
                                    }`}
                                >
                                    {vistaNueva ? 'Nueva' : 'Actual'}
                                </span>
                            </div>
                        </>
                    ) : (
                        <div className="flex aspect-[3/4] w-full flex-col items-center justify-center gap-2 px-4 text-center sm:min-h-[240px]">
                            <span className="flex h-16 w-16 items-center justify-center rounded-full bg-wayna-100 text-2xl font-black text-wayna-600 ring-4 ring-white">
                                {iniciales || '?'}
                            </span>
                            <p className="text-xs font-medium text-stone-400">
                                Vista previa en vivo
                            </p>
                        </div>
                    )}
                </div>

                <AdminMediaDropzone
                    id="fotografia"
                    accept="image/jpeg,image/png,image/webp"
                    title={esEdicion ? 'Cambiar fotografía' : 'Subir fotografía'}
                    hint="JPG, PNG o WEBP · máx. 2 MB · rostro o imagen representativa"
                    badge="Portada turista"
                    onFiles={(files) => setFotografia(files?.[0] ?? null)}
                />
            </div>

            {error && (
                <p className="border-t border-red-100 bg-red-50 px-4 py-2 text-sm font-medium text-red-600 sm:px-5">
                    {error}
                </p>
            )}
        </section>
    );
}

