import LandingScrollReveal from '@/Components/Landing/LandingScrollReveal';
import { useCarousel } from '@/hooks/useCarousel';
import { useTranslation } from 'react-i18next';

export default function LandingTestimonialsCarousel() {
    const { t } = useTranslation();

    const items = [
        {
            quote: t('landing.testimonials.t1.quote'),
            author: t('landing.testimonials.t1.author'),
            role: t('landing.testimonials.t1.role'),
        },
        {
            quote: t('landing.testimonials.t2.quote'),
            author: t('landing.testimonials.t2.author'),
            role: t('landing.testimonials.t2.role'),
        },
        {
            quote: t('landing.testimonials.t3.quote'),
            author: t('landing.testimonials.t3.author'),
            role: t('landing.testimonials.t3.role'),
        },
    ];

    const { index, goTo, next, prev, setPaused } = useCarousel(items.length, { autoMs: 8000 });
    const item = items[index];

    return (
        <section className="bg-wayna-950 py-16 text-orange-50 sm:py-20">
            <div
                className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
                onMouseEnter={() => setPaused(true)}
                onMouseLeave={() => setPaused(false)}
            >
                <LandingScrollReveal>
                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-orange-300">
                        {t('landing.testimonials.kicker')}
                    </p>
                    <h2 className="mt-2 text-2xl font-black text-white sm:text-3xl">
                        {t('landing.testimonials.title')}
                    </h2>
                </LandingScrollReveal>

                <div key={index} className="landing-carousel-panel mt-10">
                    <blockquote className="relative rounded-3xl border border-white/10 bg-white/5 p-8 shadow-xl backdrop-blur-sm sm:p-10">
                        <span
                            className="absolute -top-4 left-6 text-6xl font-black leading-none text-wayna-500"
                            aria-hidden
                        >
                            "
                        </span>
                        <p className="relative text-lg leading-relaxed text-orange-50/95 sm:text-xl">
                            {item.quote}
                        </p>
                        <footer className="mt-6 border-t border-white/10 pt-4">
                            <p className="font-bold text-white">{item.author}</p>
                            <p className="text-sm text-orange-200/80">{item.role}</p>
                        </footer>
                    </blockquote>
                </div>

                <div className="mt-8 flex items-center justify-between">
                    <div className="flex gap-2">
                        {items.map((_, i) => (
                            <button
                                key={i}
                                type="button"
                                onClick={() => goTo(i)}
                                className={`h-2 rounded-full transition-all ${
                                    i === index ? 'w-8 bg-wayna-400' : 'w-2 bg-white/30'
                                }`}
                                aria-label={t('landing.carousel.goToSlide', { n: i + 1 })}
                            />
                        ))}
                    </div>
                    <div className="flex gap-2">
                        <button
                            type="button"
                            onClick={prev}
                            className="rounded-full border border-white/20 px-4 py-2 text-sm font-bold hover:bg-white/10"
                        >
                            {t('landing.carousel.prev')}
                        </button>
                        <button
                            type="button"
                            onClick={next}
                            className="rounded-full bg-wayna-500 px-4 py-2 text-sm font-bold text-white hover:bg-wayna-600"
                        >
                            {t('landing.carousel.next')}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    );
}
