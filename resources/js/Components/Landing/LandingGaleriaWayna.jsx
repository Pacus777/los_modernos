import LandingScrollReveal from '@/Components/Landing/LandingScrollReveal';
import { LANDING_IMAGES } from '@/data/landingImages';
import { useCarousel } from '@/hooks/useCarousel';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

function GaleriaLightbox({ image, alt, onClose }) {
    useEffect(() => {
        const onKey = (e) => {
            if (e.key === 'Escape') {
                onClose();
            }
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [onClose]);

    if (!image) {
        return null;
    }

    return (
        <div
            className="fixed inset-0 z-[60] flex items-center justify-center bg-wayna-950/90 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            onClick={onClose}
        >
            <button
                type="button"
                onClick={onClose}
                className="absolute right-4 top-4 rounded-full bg-white/10 px-3 py-1 text-sm font-bold text-white hover:bg-white/20"
            >
                ✕
            </button>
            <img
                src={image.src}
                alt={alt}
                className="max-h-[90vh] max-w-full rounded-2xl object-contain shadow-2xl"
                onClick={(e) => e.stopPropagation()}
            />
        </div>
    );
}

export default function LandingGaleriaWayna() {
    const { t } = useTranslation();
    const [lightbox, setLightbox] = useState(null);
    const { index, goTo, next, prev, setPaused } = useCarousel(LANDING_IMAGES.length, {
        autoMs: 4500,
    });

    const featured = LANDING_IMAGES[index];

    return (
        <section id="galeria" className="scroll-mt-20 border-b border-wayna-100 bg-white py-14 sm:py-20">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <LandingScrollReveal>
                    <header className="max-w-2xl">
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-wayna-600">
                            {t('landing.gallery.kicker')}
                        </p>
                        <h2 className="mt-2 text-3xl font-black text-wayna-950">{t('landing.gallery.title')}</h2>
                        <p className="mt-3 text-stone-600">{t('landing.gallery.subtitle')}</p>
                    </header>
                </LandingScrollReveal>

                <div
                    className="mt-10"
                    onMouseEnter={() => setPaused(true)}
                    onMouseLeave={() => setPaused(false)}
                >
                    <div className="landing-carousel-panel overflow-hidden rounded-3xl shadow-xl ring-1 ring-wayna-100">
                        <button
                            type="button"
                            onClick={() => setLightbox(featured)}
                            className="group relative block w-full"
                        >
                            <img
                                src={featured.src}
                                alt={t(featured.altKey)}
                                className="aspect-[16/9] w-full object-cover transition duration-500 group-hover:scale-[1.02] sm:aspect-[21/9]"
                            />
                            <span className="absolute inset-0 bg-gradient-to-t from-wayna-950/50 via-transparent to-transparent opacity-80" />
                            <span className="absolute bottom-4 left-4 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-wayna-800">
                                {t('landing.gallery.tapToExpand')}
                            </span>
                        </button>
                    </div>

                    <div className="mt-4 flex flex-wrap items-center justify-between gap-4">
                        <div className="flex max-w-full gap-2 overflow-x-auto pb-1">
                            {LANDING_IMAGES.map((img, i) => (
                                <button
                                    key={img.id}
                                    type="button"
                                    onClick={() => goTo(i)}
                                    className={`h-16 w-20 shrink-0 overflow-hidden rounded-xl border-2 transition ${
                                        i === index
                                            ? 'border-wayna-500 ring-2 ring-wayna-300'
                                            : 'border-transparent opacity-70 hover:opacity-100'
                                    }`}
                                >
                                    <img
                                        src={img.src}
                                        alt=""
                                        className="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                </button>
                            ))}
                        </div>
                        <div className="flex shrink-0 gap-2">
                            <button
                                type="button"
                                onClick={prev}
                                className="flex h-10 w-10 items-center justify-center rounded-full border border-wayna-200 bg-white font-bold text-wayna-700 hover:bg-wayna-50"
                                aria-label={t('landing.carousel.prev')}
                            >
                                ‹
                            </button>
                            <button
                                type="button"
                                onClick={next}
                                className="flex h-10 w-10 items-center justify-center rounded-full border border-wayna-200 bg-white font-bold text-wayna-700 hover:bg-wayna-50"
                                aria-label={t('landing.carousel.next')}
                            >
                                ›
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {lightbox && (
                <GaleriaLightbox
                    image={lightbox}
                    alt={t(lightbox.altKey)}
                    onClose={() => setLightbox(null)}
                />
            )}
        </section>
    );
}
