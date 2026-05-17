import LanguageSelector from '@/Components/LanguageSelector';
import WaynaNavBar from '@/Components/WaynaNavBar';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

const NAV_SECTIONS = [
    { id: 'inicio', key: 'home' },
    { id: 'como-funciona', key: 'how' },
    { id: 'plataforma', key: 'system' },
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

function StatPill({ label }) {
    return (
        <div className="rounded-2xl border border-wayna-200/80 bg-surface-card/95 px-4 py-3 text-center shadow-sm backdrop-blur-sm">
            <p className="text-sm font-bold text-wayna-900">{label}</p>
        </div>
    );
}

function StepCard({ number, title, body }) {
    return (
        <article className="relative overflow-hidden rounded-3xl border border-wayna-200/70 bg-surface-card p-6 shadow-lg shadow-wayna-900/5">
            <span className="absolute -right-2 -top-4 text-7xl font-black text-wayna-100/90">
                {number}
            </span>
            <h3 className="relative text-lg font-bold text-wayna-950">{title}</h3>
            <p className="relative mt-3 text-sm leading-relaxed text-stone-600">{body}</p>
        </article>
    );
}

function FeatureCard({ title, body }) {
    return (
        <article className="rounded-3xl border border-surface-200 bg-surface-card p-6 shadow-md">
            <h3 className="text-lg font-bold text-wayna-800">{title}</h3>
            <p className="mt-2 text-sm leading-relaxed text-stone-600">{body}</p>
        </article>
    );
}

function LocationCard({ name, address, hours }) {
    return (
        <article className="rounded-3xl border border-wayna-200/80 bg-gradient-to-br from-wayna-50 to-surface-card p-6 shadow-md">
            <h3 className="text-xl font-bold text-wayna-900">{name}</h3>
            <p className="mt-3 text-sm leading-relaxed text-stone-700">{address}</p>
            <p className="mt-4 text-sm font-semibold text-wayna-700">{hours}</p>
        </article>
    );
}

export default function Landing({ canLogin, panelUrl, marketUrl }) {
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

                    {canLogin && (
                        <Link
                            href={staffHref}
                            className="btn-wayna-primary !rounded-xl !px-4 !py-2 !text-xs sm:!text-sm"
                        >
                            {panelUrl ? t('landing.nav.staffLogin') : t('landing.hero.ctaStaff')}
                        </Link>
                    )}
                </WaynaNavBar>

                <section
                    id="inicio"
                    className="relative overflow-hidden border-b border-wayna-200/50 bg-gradient-to-b from-wayna-500 via-wayna-500 to-wayna-600 pb-16 pt-10 text-white sm:pb-20 sm:pt-14"
                >
                    <div className="pointer-events-none absolute -left-24 top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl" />
                    <div className="pointer-events-none absolute -right-16 bottom-0 h-72 w-72 rounded-full bg-wayna-900/20 blur-3xl" />

                    <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-orange-100">
                            {t('landing.hero.kicker')}
                        </p>

                        <h1 className="mt-4 max-w-3xl text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">
                            {t('landing.hero.title')}
                        </h1>

                        <p className="mt-5 max-w-2xl text-base leading-relaxed text-orange-50/95 sm:text-lg">
                            {t('landing.hero.subtitle')}
                        </p>

                        <div className="mt-8 flex flex-wrap gap-3">
                            <button
                                type="button"
                                onClick={() => scrollToSection('como-funciona')}
                                className="btn-wayna-primary-gradient"
                            >
                                {t('landing.hero.ctaHow')}
                            </button>

                            {canLogin && (
                                <Link
                                    href={staffHref}
                                    className="btn-wayna-secondary !border-white/40 !bg-white/10 !text-white hover:!bg-white/20"
                                >
                                    {t('landing.hero.ctaStaff')}
                                </Link>
                            )}
                        </div>

                        <div className="mt-10 grid gap-3 sm:grid-cols-3">
                            <StatPill label={t('landing.stats.transparent')} />
                            <StatPill label={t('landing.stats.languages')} />
                            <StatPill label={t('landing.stats.payments')} />
                        </div>

                        <button
                            type="button"
                            onClick={() => scrollToSection('como-funciona')}
                            className="mt-12 flex w-full flex-col items-center gap-1 text-xs font-semibold text-orange-100/90"
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

                <section id="como-funciona" className="scroll-mt-20 py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <header className="max-w-2xl">
                            <h2 className="text-3xl font-black text-wayna-900">{t('landing.how.title')}</h2>
                            <p className="mt-3 text-stone-600">{t('landing.how.subtitle')}</p>
                        </header>

                        <div className="mt-10 grid gap-6 md:grid-cols-3">
                            <StepCard
                                number="01"
                                title={t('landing.how.step1Title')}
                                body={t('landing.how.step1Body')}
                            />
                            <StepCard
                                number="02"
                                title={t('landing.how.step2Title')}
                                body={t('landing.how.step2Body')}
                            />
                            <StepCard
                                number="03"
                                title={t('landing.how.step3Title')}
                                body={t('landing.how.step3Body')}
                            />
                        </div>
                    </div>
                </section>

                <section
                    id="plataforma"
                    className="scroll-mt-20 border-y border-surface-200 bg-surface-muted py-16 sm:py-20"
                >
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <header className="max-w-2xl">
                            <h2 className="text-3xl font-black text-wayna-900">{t('landing.platform.title')}</h2>
                            <p className="mt-3 text-stone-600">{t('landing.platform.subtitle')}</p>
                        </header>

                        <div className="mt-10 grid gap-6 lg:grid-cols-3">
                            <FeatureCard
                                title={t('landing.platform.traceTitle')}
                                body={t('landing.platform.traceBody')}
                            />
                            <FeatureCard
                                title={t('landing.platform.touristTitle')}
                                body={t('landing.platform.touristBody')}
                            />
                            <FeatureCard
                                title={t('landing.platform.teamTitle')}
                                body={t('landing.platform.teamBody')}
                            />
                        </div>
                    </div>
                </section>

                <section className="py-16 sm:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="overflow-hidden rounded-3xl bg-gradient-to-r from-wayna-600 to-wayna-500 p-8 text-white shadow-xl sm:p-10 lg:flex lg:items-center lg:justify-between lg:gap-10">
                            <div className="max-w-xl">
                                <h2 className="text-2xl font-black sm:text-3xl">{t('landing.market.title')}</h2>
                                <p className="mt-3 text-sm leading-relaxed text-orange-50 sm:text-base">
                                    {t('landing.market.body')}
                                </p>
                            </div>
                            <a
                                href={externalMarket}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-6 inline-flex shrink-0 items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-bold text-wayna-700 shadow-lg transition hover:bg-orange-50 lg:mt-0"
                            >
                                {t('landing.market.cta')} →
                            </a>
                        </div>
                    </div>
                </section>

                <section id="ubicaciones" className="scroll-mt-20 pb-16 sm:pb-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <header className="max-w-2xl">
                            <h2 className="text-3xl font-black text-wayna-900">{t('landing.locations.title')}</h2>
                            <p className="mt-3 text-stone-600">{t('landing.locations.subtitle')}</p>
                        </header>

                        <div className="mt-10 grid gap-6 lg:grid-cols-2">
                            <LocationCard
                                name={t('landing.locations.sopocachiName')}
                                address={t('landing.locations.sopocachiAddress')}
                                hours={t('landing.locations.sopocachiHours')}
                            />
                            <LocationCard
                                name={t('landing.locations.surName')}
                                address={t('landing.locations.surAddress')}
                                hours={t('landing.locations.surHours')}
                            />
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
            </div>
        </>
    );
}
