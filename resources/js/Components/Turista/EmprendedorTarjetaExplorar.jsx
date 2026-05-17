import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function EmprendedorTarjetaExplorar({ emprendedor }) {
    const { t } = useTranslation();

    if (!emprendedor) {
        return null;
    }

    const nombreCompleto = `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim();
    const foto = emprendedor.foto_portada;

    return (
        <article className="group flex flex-col overflow-hidden rounded-3xl border border-wayna-100 bg-white shadow-md shadow-wayna-900/5 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl">
            <div className="relative aspect-[4/3] bg-gradient-to-br from-wayna-100 to-wayna-50">
                {foto ? (
                    <img
                        src={foto}
                        alt={nombreCompleto}
                        className="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                        loading="lazy"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center text-5xl font-black text-wayna-500">
                        {emprendedor.nombre?.charAt(0) || 'W'}
                    </div>
                )}

                <div className="absolute left-3 top-3 flex flex-wrap gap-1.5">
                    {emprendedor.tiene_video && (
                        <span className="rounded-full bg-black/55 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white backdrop-blur-sm">
                            {t('explore.badgeVideo')}
                        </span>
                    )}
                    {emprendedor.cantidad_fotos > 0 && (
                        <span className="rounded-full bg-black/55 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white backdrop-blur-sm">
                            {t('explore.badgePhotos', { count: emprendedor.cantidad_fotos })}
                        </span>
                    )}
                </div>
            </div>

            <div className="flex flex-1 flex-col p-5">
                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-wayna-600">
                    {t('explore.cardKicker')}
                </p>

                <h3 className="mt-1 text-lg font-black leading-tight text-wayna-950">{nombreCompleto}</h3>

                <div className="mt-2 flex flex-wrap gap-1.5">
                    {emprendedor.tipo_emprendimiento_etiqueta ? (
                        <span className="inline-flex rounded-full bg-wayna-100 px-2.5 py-0.5 text-xs font-bold text-wayna-800">
                            {emprendedor.tipo_emprendimiento_etiqueta}
                        </span>
                    ) : null}
                    {emprendedor.departamento_etiqueta ? (
                        <span className="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-bold text-stone-700">
                            {emprendedor.departamento_etiqueta}
                        </span>
                    ) : null}
                </div>

                {emprendedor.puntos?.length > 0 && (
                    <p className="mt-2 text-xs font-medium text-stone-500">
                        {emprendedor.puntos.map((p) => p.nombre).join(' · ')}
                    </p>
                )}

                <p className="mt-3 line-clamp-3 flex-1 text-sm leading-relaxed text-stone-600">
                    {emprendedor.descripcion || t('explore.cardStoryFallback')}
                </p>

                <div className="mt-4 flex flex-col gap-2 sm:flex-row">
                    <Link
                        href={emprendedor.perfil_url}
                        className="btn-wayna-primary flex-1 !py-3 text-center"
                    >
                        {t('explore.viewProfile')}
                    </Link>
                    {emprendedor.tiene_redes && (
                        <span className="inline-flex items-center justify-center rounded-2xl border border-wayna-200 bg-wayna-50 px-3 py-2 text-xs font-bold text-wayna-800">
                            {t('explore.hasSocial')}
                        </span>
                    )}
                </div>
            </div>
        </article>
    );
}
