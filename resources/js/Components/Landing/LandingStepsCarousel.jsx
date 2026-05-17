import LandingScrollReveal from '@/Components/Landing/LandingScrollReveal';
import { LANDING_STEPS_IMAGES } from '@/data/landingImages';
import { useCarousel } from '@/hooks/useCarousel';
import { useTranslation } from 'react-i18next';

function StepCard({ number, title, body, imageSrc, active }) {
    return (
        <article
            className={`relative min-w-[85%] shrink-0 snap-center overflow-hidden rounded-3xl border shadow-lg transition md:min-w-0 ${
                active
                    ? 'border-wayna-400 bg-surface-card shadow-wayna-900/10'
                    : 'border-wayna-100 bg-surface-card/80'
            }`}
        >
            {imageSrc && (
                <img src={imageSrc} alt="" className="aspect-[16/10] w-full object-cover" loading="lazy" />
            )}
            <div className="relative p-6">
                <span className="absolute -right-2 -top-4 text-7xl font-black text-wayna-100/90">
                    {number}
                </span>
                <h3 className="relative text-lg font-bold text-wayna-950">{title}</h3>
                <p className="relative mt-3 text-sm leading-relaxed text-stone-600">{body}</p>
            </div>
        </article>
    );
}

export default function LandingStepsCarousel() {
    const { t } = useTranslation();

    const steps = [
        {
            number: '01',
            title: t('landing.how.step1Title'),
            body: t('landing.how.step1Body'),
            imageSrc: LANDING_STEPS_IMAGES[0],
        },
        {
            number: '02',
            title: t('landing.how.step2Title'),
            body: t('landing.how.step2Body'),
            imageSrc: LANDING_STEPS_IMAGES[1],
        },
        {
            number: '03',
            title: t('landing.how.step3Title'),
            body: t('landing.how.step3Body'),
            imageSrc: LANDING_STEPS_IMAGES[2],
        },
    ];

    const { index, goTo, setPaused } = useCarousel(steps.length, { autoMs: 0 });

    return (
        <section id="como-funciona" className="scroll-mt-20 py-16 sm:py-20">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <LandingScrollReveal>
                    <header className="max-w-2xl">
                        <h2 className="text-3xl font-black text-wayna-900">{t('landing.how.title')}</h2>
                        <p className="mt-3 text-stone-600">{t('landing.how.subtitle')}</p>
                    </header>
                </LandingScrollReveal>

                <div
                    className="landing-steps-track mt-10 flex gap-4 overflow-x-auto pb-4 snap-x snap-mandatory md:hidden"
                    onTouchStart={() => setPaused(true)}
                >
                    {steps.map((step, i) => (
                        <StepCard key={step.number} {...step} active={i === index} />
                    ))}
                </div>

                <div className="mt-6 flex justify-center gap-2 md:hidden">
                    {steps.map((_, i) => (
                        <button
                            key={i}
                            type="button"
                            onClick={() => goTo(i)}
                            className={`h-2 rounded-full transition-all ${
                                i === index ? 'w-8 bg-wayna-500' : 'w-2 bg-wayna-200'
                            }`}
                            aria-label={t('landing.carousel.goToSlide', { n: i + 1 })}
                        />
                    ))}
                </div>

                <div className="mt-10 hidden gap-6 md:grid md:grid-cols-3">
                    {steps.map((step) => (
                        <StepCard key={step.number} {...step} active />
                    ))}
                </div>
            </div>
        </section>
    );
}
