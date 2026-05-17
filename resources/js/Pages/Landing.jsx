import LandingDestacadosCarousel from '@/Components/Landing/LandingDestacadosCarousel';
import LandingGaleriaWayna from '@/Components/Landing/LandingGaleriaWayna';
import LandingHeroCarousel from '@/Components/Landing/LandingHeroCarousel';
import { LANDING_LOCATION_IMAGES } from '@/data/landingImages';
import LandingScrollReveal from '@/Components/Landing/LandingScrollReveal';
import LandingStatsBar from '@/Components/Landing/LandingStatsBar';
import LandingStepsCarousel from '@/Components/Landing/LandingStepsCarousel';
import LandingTestimonialsCarousel from '@/Components/Landing/LandingTestimonialsCarousel';
import ExplorarEmprendedores from '@/Components/Turista/ExplorarEmprendedores';
import ChatWidget from '@/Components/Turista/ChatWidget';
import LanguageSelector from '@/Components/LanguageSelector';
import WaynaNavBar from '@/Components/WaynaNavBar';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

const NAV_SECTIONS = [
    { id: 'inicio', key: 'home' },
    { id: 'explorar', key: 'explore' },
    { id: 'galeria', key: 'gallery' },
    { id: 'como-funciona', key: 'how' },
    { id: 'ubicaciones', key: 'locations' },
];

function scrollToSection(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function NavAnchor({ id, label }) {
    return (
        <button
            type="button"
            onClick={() => scrollToSection(id)}
            className="hidden rounded-lg px-2 py-1 text-sm font-semibold text-white/95 transition hover:bg-white/15 lg:inline-block"
        >
            {label}
        </button>
    );
}

function FeatureCard({ title, body }) {
    return (
        <article className="rounded-3xl border border-surface-200 bg-surface-card p-6 shadow-md transition duration-300 hover:-translate-y-0.5 hover:shadow-lg">
            <h3 className="text-lg font-bold text-wayna-800">{title}</h3>
            <p className="mt-2 text-sm leading-relaxed text-stone-600">{body}</p>
        </article>
    );
}

function LocationCard({ name, address, hours, imageSrc }) {
    return (
        <article className="overflow-hidden rounded-3xl border border-wayna-200/80 bg-gradient-to-br from-wayna-50 to-surface-card shadow-md transition duration-300 hover:shadow-lg">
            {imageSrc && (
                <img
                    src={imageSrc}
                    alt=""
                    className="aspect-[16/10] w-full object-cover"
                    loading="lazy"
                />
            )}
            <div className="p-6">
                <h3 className="text-xl font-bold text-wayna-900">{name}</h3>
                <p className="mt-3 text-sm leading-relaxed text-stone-700">{address}</p>
                <p className="mt-4 text-sm font-semibold text-wayna-700">{hours}</p>
            </div>
        </article>
    );
}

export default function Landing({
    canLogin,
    panelUrl,
    marketUrl,
    emprendedores = [],
    destacados = [],
    stats = {},
    filtros = {},
    catalogos = {},
}) {
    const { t } = useTranslation();
    const year = new Date().getFullYear();
    const externalMarket = marketUrl || 'https://www.waynamercados.com/';
    const staffHref = panelUrl || (canLogin ? route('login') : '#');

    return (
        <>
            <Head title={t('landing.headTitle')} />

            <div className="min-h-screen bg-surface text-stone-900">
                <WaynaNavBar href="#inicio">
                    <nav className="hidden items-center gap-1 md:flex" aria-label={t('landing.footer.navTitle')}>
                        {NAV_SECTIONS.map(({ id, key }) => (
                            <NavAnchor key={id} id={id} label={t(`landing.nav.${key}`)} />
                        ))}
                    </nav>

                    <a
                        href={externalMarket}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="hidden rounded-xl border border-white/30 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-white/10 sm:inline-block"
                    >
                        {t('landing.nav.market')}
                    </a>

                    <LanguageSelector variant="on-brand" />
                </WaynaNavBar>

                <LandingHeroCarousel />

                <LandingStatsBar stats={stats} />

                <LandingGaleriaWayna />

                <LandingDestacadosCarousel destacados={destacados} />

                <ExplorarEmprendedores
                    emprendedores={emprendedores}
                    filtros={filtros}
                    catalogos={catalogos}
                />

                <LandingStepsCarousel />

                <section
                    id="plataforma"
                    className="scroll-mt-20 border-y border-surface-200 bg-surface-muted py-16 sm:py-20"
                >
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <LandingScrollReveal>
                            <header className="max-w-2xl">
                                <h2 className="text-3xl font-black text-wayna-900">
                                    {t('landing.platform.title')}
                                </h2>
                                <p className="mt-3 text-stone-600">{t('landing.platform.subtitle')}</p>
                            </header>
                        </LandingScrollReveal>

                        <div className="mt-10 grid gap-6 lg:grid-cols-3">
                            <LandingScrollReveal delayMs={80}>
                                <FeatureCard
                                    title={t('landing.platform.traceTitle')}
                                    body={t('landing.platform.traceBody')}
                                />
                            </LandingScrollReveal>
                            <LandingScrollReveal delayMs={160}>
                                <FeatureCard
                                    title={t('landing.platform.touristTitle')}
                                    body={t('landing.platform.touristBody')}
                                />
                            </LandingScrollReveal>
                            <LandingScrollReveal delayMs={240}>
                                <FeatureCard
                                    title={t('landing.platform.teamTitle')}
                                    body={t('landing.platform.teamBody')}
                                />
                            </LandingScrollReveal>
                        </div>
                    </div>
                </section>

                <LandingTestimonialsCarousel />

                <section className="py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <LandingScrollReveal>
                            <div className="overflow-hidden rounded-3xl bg-gradient-to-r from-wayna-600 to-wayna-500 p-8 text-white shadow-xl sm:p-10 lg:flex lg:items-center lg:justify-between lg:gap-10">
                                <div className="max-w-xl">
                                    <h2 className="text-2xl font-black sm:text-3xl">
                                        {t('landing.market.title')}
                                    </h2>
                                    <p className="mt-3 text-sm leading-relaxed text-orange-50 sm:text-base">
                                        {t('landing.market.body')}
                                    </p>
                                </div>
                                <a
                                    href={externalMarket}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="mt-6 inline-flex shrink-0 items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-bold text-wayna-700 shadow-lg transition hover:scale-[1.02] hover:bg-orange-50 lg:mt-0"
                                >
                                    {t('landing.market.cta')} →
                                </a>
                            </div>
                        </LandingScrollReveal>
                    </div>
                </section>

                <section id="ubicaciones" className="scroll-mt-20 pb-16 sm:pb-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <LandingScrollReveal>
                            <header className="max-w-2xl">
                                <h2 className="text-3xl font-black text-wayna-900">
                                    {t('landing.locations.title')}
                                </h2>
                                <p className="mt-3 text-stone-600">{t('landing.locations.subtitle')}</p>
                            </header>
                        </LandingScrollReveal>

                        <div className="mt-10 grid gap-6 lg:grid-cols-2">
                            <LandingScrollReveal delayMs={100}>
                                <LocationCard
                                    name={t('landing.locations.sopocachiName')}
                                    address={t('landing.locations.sopocachiAddress')}
                                    hours={t('landing.locations.sopocachiHours')}
                                    imageSrc={LANDING_LOCATION_IMAGES.sopocachi}
                                />
                            </LandingScrollReveal>
                            <LandingScrollReveal delayMs={200}>
                                <LocationCard
                                    name={t('landing.locations.surName')}
                                    address={t('landing.locations.surAddress')}
                                    hours={t('landing.locations.surHours')}
                                    imageSrc={LANDING_LOCATION_IMAGES.sur}
                                />
                            </LandingScrollReveal>
                        </div>
                    </div>
                </section>

                <footer className="border-t border-wayna-200/60 bg-wayna-950 py-10 text-orange-50">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <p className="text-sm font-bold text-white">{t('common.appName')}</p>
                                <p className="mt-2 text-sm text-orange-100/80">{t('landing.footer.tagline')}</p>
                            </div>

                            <div>
                                <p className="text-xs font-bold uppercase tracking-wider text-orange-200">
                                    {t('landing.footer.navTitle')}
                                </p>
                                <ul className="mt-3 space-y-2 text-sm">
                                    {NAV_SECTIONS.map(({ id, key }) => (
                                        <li key={id}>
                                            <button
                                                type="button"
                                                onClick={() => scrollToSection(id)}
                                                className="text-orange-50/90 hover:text-white"
                                            >
                                                {t(`landing.nav.${key}`)}
                                            </button>
                                        </li>
                                    ))}
                                    {canLogin && (
                                        <li>
                                            <Link href={staffHref} className="text-orange-50/90 hover:text-white">
                                                {t('landing.nav.staffLogin')}
                                            </Link>
                                        </li>
                                    )}
                                </ul>
                            </div>

                            <div>
                                <p className="text-xs font-bold uppercase tracking-wider text-orange-200">
                                    {t('landing.footer.marketTitle')}
                                </p>
                                <ul className="mt-3 space-y-2 text-sm">
                                    <li>
                                        <a
                                            href={externalMarket}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-orange-50/90 hover:text-white"
                                        >
                                            {t('landing.nav.market')}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <p className="mt-10 border-t border-white/10 pt-6 text-center text-xs text-orange-100/70">
                            {t('landing.footer.rights', { year })}
                        </p>
                    </div>
                </footer>

                <ChatWidget />
            </div>
        </>
    );
}
