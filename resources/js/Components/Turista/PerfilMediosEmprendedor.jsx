import { useState } from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Galería y video opcional del emprendimiento (T-A22).
 */
export default function PerfilMediosEmprendedor({ medios, nombreEmprendimiento = '' }) {
    const { t } = useTranslation();
    const [imagenAmpliada, setImagenAmpliada] = useState(null);

    const galeria = medios?.galeria ?? [];
    const video = medios?.video ?? { tipo: 'none' };
    const tieneGaleria = galeria.length > 0;
    const tieneVideo = video.tipo !== 'none';

    if (!tieneGaleria && !tieneVideo) {
        return null;
    }

    return (
        <section className="space-y-5">
            {tieneGaleria && (
                <div>
                    <h2 className="text-lg font-bold text-gray-900">
                        {t('tourist.profile.galleryTitle')}
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        {t('tourist.profile.gallerySubtitle')}
                    </p>
                    <ul className="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
                        {galeria.map((url, indice) => (
                            <li key={url}>
                                <button
                                    type="button"
                                    onClick={() => setImagenAmpliada(url)}
                                    className="group block w-full overflow-hidden rounded-2xl border border-wayna-100 bg-stone-100 shadow-sm transition hover:border-wayna-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-wayna-500/40"
                                >
                                    <img
                                        src={url}
                                        alt={t('tourist.profile.galleryImageAlt', {
                                            name: nombreEmprendimiento,
                                            index: indice + 1,
                                        })}
                                        className="aspect-square w-full object-cover transition duration-300 group-hover:scale-105"
                                    />
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            {tieneVideo && (
                <div>
                    <h2 className="text-lg font-bold text-gray-900">
                        {t('tourist.profile.videoTitle')}
                    </h2>
                    <div className="mt-3 overflow-hidden rounded-2xl border border-wayna-100 bg-black/5 shadow-inner">
                        {video.tipo === 'embed' && video.embed_url ? (
                            <div className="relative aspect-video w-full">
                                <iframe
                                    src={video.embed_url}
                                    title={t('tourist.profile.videoTitle')}
                                    className="absolute inset-0 h-full w-full"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowFullScreen
                                />
                            </div>
                        ) : video.src ? (
                            <video
                                src={video.src}
                                controls
                                playsInline
                                className="aspect-video w-full bg-black"
                                preload="metadata"
                            >
                                {t('tourist.profile.videoUnsupported')}
                            </video>
                        ) : null}
                    </div>
                </div>
            )}

            {imagenAmpliada && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                    role="dialog"
                    aria-modal="true"
                    onClick={() => setImagenAmpliada(null)}
                >
                    <button
                        type="button"
                        className="absolute right-4 top-4 rounded-full bg-white/90 px-3 py-1 text-sm font-bold text-wayna-900 shadow"
                        onClick={() => setImagenAmpliada(null)}
                    >
                        {t('common.close')}
                    </button>
                    <img
                        src={imagenAmpliada}
                        alt=""
                        className="max-h-[90vh] max-w-full rounded-2xl object-contain shadow-2xl"
                        onClick={(e) => e.stopPropagation()}
                    />
                </div>
            )}
        </section>
    );
}
