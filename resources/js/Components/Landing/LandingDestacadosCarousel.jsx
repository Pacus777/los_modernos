import EmprendedorTarjetaExplorar from '@/Components/Turista/EmprendedorTarjetaExplorar';
import LandingScrollReveal from '@/Components/Landing/LandingScrollReveal';
import { useEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';

export default function LandingDestacadosCarousel({ destacados = [] }) {
    const { t } = useTranslation();
    const trackRef = useRef(null);
    const indexRef = useRef(0);

    useEffect(() => {
        if (destacados.length <= 1) {
            return undefined;
        }

        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduced) {
            return undefined;
        }

        const id = window.setInterval(() => {
            const track = trackRef.current;
            if (!track) {
                return;
            }
            indexRef.current = (indexRef.current + 1) % destacados.length;
            const card = track.children[indexRef.current];
            card?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }, 6000);

        return () => window.clearInterval(id);
    }, [destacados.length]);

    if (!destacados.length) {
        return null;
    }

    const scrollByCard = (direction) => {
        const track = trackRef.current;
        if (!track) {
            return;
        }
        const width = track.clientWidth * 0.85;
        track.scrollBy({ left: direction * width, behavior: 'smooth' });
    };

    return (
        <section className="border-b border-wayna-100/80 bg-gradient-to-b from-white to-surface py-14 sm:py-16">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <LandingScrollReveal>
                    <header className="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-[0.2em] text-wayna-600">
                                {t('landing.featured.kicker')}
                            </p>
                            <h2 className="mt-2 text-2xl font-black text-wayna-950 sm:text-3xl">
                                {t('landing.featured.title')}
                            </h2>
                            <p className="mt-2 max-w-xl text-sm text-stone-600">
                                {t('landing.featured.subtitle')}
                            </p>
                        </div>
                        <div className="flex gap-2">
                            <button
                                type="button"
                                onClick={() => scrollByCard(-1)}
                                className="flex h-10 w-10 items-center justify-center rounded-full border border-wayna-200 bg-white text-lg font-bold text-wayna-700 shadow-sm hover:bg-wayna-50"
                                aria-label={t('landing.carousel.prev')}
                            >
                                ‹
                            </button>
                            <button
                                type="button"
                                onClick={() => scrollByCard(1)}
                                className="flex h-10 w-10 items-center justify-center rounded-full border border-wayna-200 bg-white text-lg font-bold text-wayna-700 shadow-sm hover:bg-wayna-50"
                                aria-label={t('landing.carousel.next')}
                            >
                                ›
                            </button>
                        </div>
                    </header>
                </LandingScrollReveal>

                <div
                    ref={trackRef}
                    className="landing-featured-track mt-8 flex gap-5 overflow-x-auto pb-4 snap-x snap-mandatory scroll-smooth"
                >
                    {destacados.map((emp) => (
                        <div
                            key={emp.id}
                            className="w-[min(88vw,340px)] shrink-0 snap-center sm:w-[320px] lg:w-[calc(33.333%-0.85rem)]"
                        >
                            <EmprendedorTarjetaExplorar emprendedor={emp} />
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
