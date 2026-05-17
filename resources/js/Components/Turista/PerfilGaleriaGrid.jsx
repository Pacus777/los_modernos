import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

function IconGrid({ className = 'h-5 w-5' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="currentColor" aria-hidden>
            <path d="M3 3h7v7H3V3zm11 0h7v7h-7V3zM3 14h7v7H3v-7zm11 0h7v7h-7v-7z" />
        </svg>
    );
}

function IconPlay({ className = 'h-8 w-8' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="currentColor" aria-hidden>
            <path d="M8 5v14l11-7L8 5z" />
        </svg>
    );
}

function IconReel({ className = 'h-5 w-5' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <rect x="4" y="4" width="16" height="16" rx="3" />
            <path d="M10 9l5 3-5 3V9z" fill="currentColor" stroke="none" />
        </svg>
    );
}

function construirItems(medios) {
    const items = [];

    if (medios?.foto_empresa) {
        items.push({ id: 'empresa', type: 'image', src: medios.foto_empresa });
    }

    (medios?.galeria ?? []).forEach((src, i) => {
        items.push({ id: `galeria-${i}`, type: 'image', src });
    });

    const video = medios?.video ?? { tipo: 'none' };
    if (video.tipo !== 'none') {
        items.push({ id: 'video', type: 'video', video });
    }

    return items;
}

function CeldaVideoMiniatura({ video, onOpen }) {
    const poster =
        video.tipo === 'embed' && video.embed_url
            ? null
            : video.src ?? null;

    return (
        <button
            type="button"
            onClick={onOpen}
            className="perfil-ig-grid__cell group relative w-full overflow-hidden bg-stone-900"
        >
            {poster ? (
                <video
                    src={poster}
                    muted
                    playsInline
                    preload="metadata"
                    className="h-full w-full object-cover opacity-90"
                />
            ) : (
                <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-wayna-700 to-wayna-950" />
            )}
            <span className="absolute inset-0 flex items-center justify-center bg-black/25 transition group-hover:bg-black/35">
                <span className="flex h-12 w-12 items-center justify-center rounded-full bg-white/95 text-wayna-700 shadow-lg">
                    <IconPlay />
                </span>
            </span>
        </button>
    );
}

function VisorLightbox({ item, nombreEmprendimiento, onClose, t }) {
    if (!item) {
        return null;
    }

    return (
        <div
            className="fixed inset-0 z-[70] flex items-center justify-center bg-black/90 p-3 sm:p-6"
            role="dialog"
            aria-modal="true"
            onClick={onClose}
        >
            <button
                type="button"
                className="absolute right-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur-sm transition hover:bg-white/25"
                onClick={onClose}
                aria-label={t('common.close', 'Cerrar')}
            >
                <span className="text-2xl leading-none">&times;</span>
            </button>

            {item.type === 'image' ? (
                <img
                    src={item.src}
                    alt={t('tourist.profile.galleryImageAlt', {
                        name: nombreEmprendimiento,
                        index: 1,
                    })}
                    className="max-h-[92vh] max-w-full object-contain"
                    onClick={(e) => e.stopPropagation()}
                />
            ) : (
                <div
                    className="w-full max-w-3xl overflow-hidden rounded-2xl bg-black shadow-2xl"
                    onClick={(e) => e.stopPropagation()}
                >
                    {item.video.tipo === 'embed' && item.video.embed_url ? (
                        <div className="relative aspect-video w-full">
                            <iframe
                                src={item.video.embed_url}
                                title={t('tourist.profile.videoTitle')}
                                className="absolute inset-0 h-full w-full"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowFullScreen
                            />
                        </div>
                    ) : item.video.src ? (
                        <video
                            src={item.video.src}
                            controls
                            playsInline
                            autoPlay
                            className="aspect-video w-full bg-black"
                        />
                    ) : null}
                </div>
            )}
        </div>
    );
}

/**
 * Galería estilo Instagram: rejilla 3×N, pestaña de video y visor a pantalla completa.
 */
export default function PerfilGaleriaGrid({ medios, nombreEmprendimiento = '' }) {
    const { t } = useTranslation();
    const [tab, setTab] = useState('grid');
    const [lightbox, setLightbox] = useState(null);

    const items = useMemo(() => construirItems(medios), [medios]);
    const imagenes = items.filter((i) => i.type === 'image');
    const videoItem = items.find((i) => i.type === 'video');
    const tieneContenido = items.length > 0;

    if (!tieneContenido) {
        return (
            <div className="border-t border-stone-200 px-4 py-12 text-center">
                <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full border-2 border-dashed border-wayna-200 text-wayna-400">
                    <IconGrid className="h-7 w-7" />
                </div>
                <p className="mt-3 text-sm text-stone-500">{t('tourist.profile.galleryEmpty')}</p>
            </div>
        );
    }

    const mostrarTabs = imagenes.length > 0 && videoItem;

    return (
        <section className="border-t border-stone-200">
            {mostrarTabs && (
                <div className="flex border-b border-stone-200" role="tablist">
                    <button
                        type="button"
                        role="tab"
                        aria-selected={tab === 'grid'}
                        onClick={() => setTab('grid')}
                        className={`flex flex-1 items-center justify-center gap-2 border-b-2 py-3 text-wayna-700 transition ${
                            tab === 'grid'
                                ? 'border-wayna-600 text-wayna-900'
                                : 'border-transparent text-stone-400 hover:text-stone-600'
                        }`}
                    >
                        <IconGrid />
                        <span className="sr-only">{t('tourist.profile.tabPhotos')}</span>
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={tab === 'video'}
                        onClick={() => setTab('video')}
                        className={`flex flex-1 items-center justify-center gap-2 border-b-2 py-3 transition ${
                            tab === 'video'
                                ? 'border-wayna-600 text-wayna-900'
                                : 'border-transparent text-stone-400 hover:text-stone-600'
                        }`}
                    >
                        <IconReel />
                        <span className="sr-only">{t('tourist.profile.tabVideo')}</span>
                    </button>
                </div>
            )}

            {tab === 'grid' && imagenes.length > 0 && (
                <ul className="perfil-ig-grid">
                    {imagenes.map((item, indice) => (
                        <li key={item.id}>
                            <button
                                type="button"
                                onClick={() => setLightbox(item)}
                                className="perfil-ig-grid__cell group block w-full"
                            >
                                <img
                                    src={item.src}
                                    alt={t('tourist.profile.galleryImageAlt', {
                                        name: nombreEmprendimiento,
                                        index: indice + 1,
                                    })}
                                    className="h-full w-full object-cover transition duration-300 group-hover:opacity-90"
                                    loading="lazy"
                                />
                            </button>
                        </li>
                    ))}
                </ul>
            )}

            {tab === 'video' && videoItem && (
                <div className="p-1">
                    <CeldaVideoMiniatura video={videoItem.video} onOpen={() => setLightbox(videoItem)} />
                </div>
            )}

            {!mostrarTabs && videoItem && imagenes.length === 0 && (
                <div className="p-1">
                    <CeldaVideoMiniatura video={videoItem.video} onOpen={() => setLightbox(videoItem)} />
                </div>
            )}

            {!mostrarTabs && !videoItem && imagenes.length > 0 && (
                <ul className="perfil-ig-grid">
                    {imagenes.map((item, indice) => (
                        <li key={item.id}>
                            <button
                                type="button"
                                onClick={() => setLightbox(item)}
                                className="perfil-ig-grid__cell group block w-full"
                            >
                                <img
                                    src={item.src}
                                    alt=""
                                    className="h-full w-full object-cover transition duration-300 group-hover:opacity-90"
                                    loading="lazy"
                                />
                            </button>
                        </li>
                    ))}
                </ul>
            )}

            <VisorLightbox
                item={lightbox}
                nombreEmprendimiento={nombreEmprendimiento}
                onClose={() => setLightbox(null)}
                t={t}
            />
        </section>
    );
}
