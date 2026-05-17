import { useScrollReveal } from '@/hooks/useScrollReveal';

export default function LandingScrollReveal({ children, className = '', delayMs = 0 }) {
    const { ref, visible } = useScrollReveal();

    return (
        <section
            ref={ref}
            className={`landing-reveal ${visible ? 'landing-reveal--visible' : ''} ${className}`.trim()}
            style={delayMs ? { transitionDelay: `${delayMs}ms` } : undefined}
        >
            {children}
        </section>
    );
}
