import { LANDING_HERO_IMAGES } from '@/data/landingImages';
import { useCarousel } from '@/hooks/useCarousel';
import { useTranslation } from 'react-i18next';

function scrollToSection(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

const TEXT_SLIDE_COUNT = 3;

export default function LandingHeroCarousel({ onExplore }) {
    const { t } = useTranslation();

    const textSlides = [
        {
            kicker: t('landing.heroSlides.slide1.kicker'),
            title: t('landing.heroSlides.slide1.title'),
            subtitle: t('landing.heroSlides.slide1.subtitle'),
            cta: t('landing.hero.ctaExplore'),
            action: () => (onExplore ? onExplore() : scrollToSection('explorar')),
        },
        {
            kicker: t('landing.heroSlides.slide2.kicker'),
            title: t('landing.heroSlides.slide2.title'),
            subtitle: t('landing.heroSlides.slide2.subtitle'),
            cta: t('landing.heroSlides.slide2.cta'),
            action: () => scrollToSection('explorar'),
        },
        {
            kicker: t('landing.heroSlides.slide3.kicker'),
            title: t('landing.heroSlides.slide3.title'),
            subtitle: t('landing.heroSlides.slide3.subtitle'),
            cta: t('landing.hero.ctaHow'),
            action: () => scrollToSection('como-funciona'),
        },
    ];

    const imageCount = LANDING_HERO_IMAGES.length;
    const { index, goTo, next, prev, setPaused } = useCarousel(imageCount, { autoMs: 5500 });
    const textSlide = textSlides[index % TEXT_SLIDE_COUNT];
    const heroImage = LANDING_HERO_IMAGES[index];

    return (
        <section
            id="inicio"
            className="landing-enter-hero landing-enter-item landing-enter-item--2 relative overflow-hidden border-b border-wayna-200/50 bg-wayna-600 text-white"
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
            onFocus={() => setPaused(true)}
            onBlur={() => setPaused(false)}
        >
            <div className="landing-hero-bg" aria-hidden />
            <div
                className="pointer-events-none absolute inset-0 bg-gradient-to-r from-wayna-900/85 via-wayna-800/55 to-wayna-700/25 lg:via-wayna-800/40"
                aria-hidden
            />
            <div className="pointer-events-none absolute -left-24 top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl" />
            <div className="pointer-events-none absolute -right-16 bottom-0 h-72 w-72 rounded-full bg-wayna-900/25 blur-3xl" />

            <div className="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8 lg:py-16">
                <div className="grid items-center gap-10 lg:grid-cols-2 lg:gap-12">
                    <div key={`text-${index % TEXT_SLIDE_COUNT}`} className="landing-hero-slide order-2 lg:order-1">
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-orange-100">
                            {textSlide.kicker}
                        </p>
                        <h1 className="mt-4 text-3xl font-black leading-tight sm:text-4xl lg:text-[2.75rem] lg:leading-[1.1]">
                            {textSlide.title}
                        </h1>
                        <p className="mt-5 max-w-xl text-base leading-relaxed text-orange-50/95 sm:text-lg">
                            {textSlide.subtitle}
                        </p>
                        <div className="mt-8 flex flex-wrap gap-3">
                            <button type="button" onClick={textSlide.action} className="btn-wayna-primary-gradient">
                                {textSlide.cta}
                            </button>
                            <button
                                type="button"
                                onClick={() => scrollToSection('como-funciona')}
                                className="btn-wayna-secondary !border-white/40 !bg-white/10 !text-white hover:!bg-white/20"
                            >
                                {t('landing.hero.ctaHow')}
                            </button>
                        </div>
                    </div>

                    <div className="order-1 lg:order-2">
                        <div className="landing-hero-image-wrap relative mx-auto w-full max-w-md lg:mx-0 lg:max-w-none">
                            <img
                                key={heroImage.id}
                                src={heroImage.src}
                                alt={t(heroImage.altKey)}
                                className="landing-hero-image relative aspect-[4/3] w-full rounded-[1.75rem] object-cover shadow-2xl ring-4 ring-white/25 sm:aspect-[5/4] lg:aspect-[4/5] lg:max-h-[min(70vh,500px)]"
                                fetchPriority={index === 0 ? 'high' : 'auto'}
                                decoding="async"
                            />
                        </div>
                    </div>
                </div>

                <div className="mt-8 flex items-center justify-between gap-4 lg:mt-10">
                    <div className="flex gap-2">
                        {LANDING_HERO_IMAGES.map((_, i) => (
                            <button
                                key={i}
                                type="button"
                                onClick={() => goTo(i)}
                                className={`h-2.5 rounded-full transition-all ${
                                    i === index
                                        ? 'w-8 bg-white'
                                        : 'w-2.5 bg-white/40 hover:bg-white/70'
                                }`}
                                aria-label={t('landing.carousel.goToSlide', { n: i + 1 })}
                            />
                        ))}
                    </div>
                    <div className="flex gap-2">
                        <button
                            type="button"
                            onClick={prev}
                            className="flex h-10 w-10 items-center justify-center rounded-full border border-white/30 bg-white/10 text-lg font-bold transition hover:bg-white/20"
                            aria-label={t('landing.carousel.prev')}
                        >
                            ‹
                        </button>
                        <button
                            type="button"
                            onClick={next}
                            className="flex h-10 w-10 items-center justify-center rounded-full border border-white/30 bg-white/10 text-lg font-bold transition hover:bg-white/20"
                            aria-label={t('landing.carousel.next')}
                        >
                            ›
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    onClick={() => scrollToSection('explorar')}
                    className="mt-8 flex w-full flex-col items-center gap-1 text-xs font-semibold text-orange-100/90 lg:mt-6"
                >
                    <span>{t('landing.hero.scrollHint')}</span>
                    <svg className="h-5 w-5 animate-bounce" viewBox="0 0 24 24" fill="none" aria-hidden>
                        <path
                            d="M12 5v14M5 12l7 7 7-7"
                            stroke="currentColor"
                            strokeWidth="2"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        />
                    </svg>
                </button>
            </div>
        </section>
    );
}
