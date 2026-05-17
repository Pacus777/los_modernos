import LandingScrollReveal from '@/Components/Landing/LandingScrollReveal';
import { useScrollReveal } from '@/hooks/useScrollReveal';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

function useCountUp(target, active, durationMs = 1200) {
    const [value, setValue] = useState(0);

    useEffect(() => {
        if (!active || target <= 0) {
            setValue(active ? target : 0);
            return undefined;
        }

        let frame;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / durationMs, 1);
            const eased = 1 - (1 - progress) ** 3;
            setValue(Math.round(target * eased));
            if (progress < 1) {
                frame = requestAnimationFrame(tick);
            }
        };

        frame = requestAnimationFrame(tick);

        return () => cancelAnimationFrame(frame);
    }, [target, active, durationMs]);

    return value;
}

function StatItem({ label, value, active, suffix = '' }) {
    const count = useCountUp(value, active);

    return (
        <div className="rounded-2xl border border-wayna-200/60 bg-white/90 px-4 py-5 text-center shadow-sm backdrop-blur-sm">
            <p className="text-3xl font-black text-wayna-600 sm:text-4xl">
                {count}
                {suffix}
            </p>
            <p className="mt-1 text-xs font-bold uppercase tracking-wide text-stone-600 sm:text-sm">{label}</p>
        </div>
    );
}

export default function LandingStatsBar({ stats = {} }) {
    const { t } = useTranslation();
    const { ref, visible } = useScrollReveal({ threshold: 0.2 });

    const items = [
        { label: t('landing.statsBar.entrepreneurs'), value: stats.emprendedores ?? 0 },
        { label: t('landing.statsBar.locations'), value: stats.puntos ?? 0 },
    ];

    return (
        <section ref={ref} className="relative z-10 -mt-8 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <LandingScrollReveal>
                <div className="mx-auto grid max-w-2xl gap-3 sm:grid-cols-2">
                    {items.map((item, i) => (
                        <StatItem
                            key={item.label}
                            label={item.label}
                            value={item.value}
                            active={visible}
                            suffix={item.suffix}
                        />
                    ))}
                </div>
            </LandingScrollReveal>
        </section>
    );
}
