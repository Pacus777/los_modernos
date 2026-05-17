import LandingScrollReveal from '@/Components/Landing/LandingScrollReveal';
import { LANDING_STEPS_IMAGES } from '@/data/landingImages';
import { useTranslation } from 'react-i18next';

function StepCard({ number, title, body, imageSrc }) {
    return (
        <article className="flex h-full flex-col overflow-hidden rounded-3xl border border-wayna-200 bg-surface-card shadow-md shadow-wayna-900/5">
            {imageSrc && (
                <img
                    src={imageSrc}
                    alt=""
                    className="aspect-[16/10] w-full object-cover sm:aspect-[5/3]"
                    loading="lazy"
                />
            )}
            <div className="relative flex flex-1 flex-col p-5 sm:p-6">
                <span
                    className="pointer-events-none absolute right-2 top-0 select-none text-5xl font-black leading-none text-wayna-100 sm:text-6xl lg:text-7xl"
                    aria-hidden
                >
                    {number}
                </span>
                <h3 className="relative pr-12 text-base font-bold text-wayna-950 sm:pr-14 sm:text-lg">
                    {title}
                </h3>
                <p className="relative mt-2 flex-1 text-sm leading-relaxed text-stone-600 sm:mt-3">
                    {body}
                </p>
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

    return (
        <section id="como-funciona" className="scroll-mt-20 py-14 sm:py-16 lg:py-20">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <LandingScrollReveal>
                    <header className="max-w-2xl">
                        <h2 className="text-2xl font-black text-wayna-900 sm:text-3xl">
                            {t('landing.how.title')}
                        </h2>
                        <p className="mt-3 text-sm leading-relaxed text-stone-600 sm:text-base">
                            {t('landing.how.subtitle')}
                        </p>
                    </header>
                </LandingScrollReveal>

                <ol className="mt-8 grid list-none grid-cols-1 gap-5 p-0 sm:mt-10 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                    {steps.map((step) => (
                        <li key={step.number} className="min-w-0">
                            <StepCard {...step} />
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    );
}
