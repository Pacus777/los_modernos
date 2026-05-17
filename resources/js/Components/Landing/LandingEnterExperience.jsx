import { useEffect, useState } from 'react';

const INTRO_DURATION_MS = 1680;
const EXIT_START_MS = 1180;
const PARTICLE_COUNT = 16;

/**
 * Intro visual al montar el landing (sin texto: solo logo, luz y apertura iris).
 * Respeta prefers-reduced-motion.
 */
export default function LandingEnterExperience({ children }) {
    const [stage, setStage] = useState('intro');

    useEffect(() => {
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reduced) {
            setStage('ready');
            return undefined;
        }

        document.body.classList.add('landing-intro-active');

        const exitTimer = window.setTimeout(() => setStage('exiting'), EXIT_START_MS);
        const readyTimer = window.setTimeout(() => {
            setStage('ready');
            document.body.classList.remove('landing-intro-active');
        }, INTRO_DURATION_MS);

        return () => {
            window.clearTimeout(exitTimer);
            window.clearTimeout(readyTimer);
            document.body.classList.remove('landing-intro-active');
        };
    }, []);

    const showOverlay = stage === 'intro' || stage === 'exiting';

    return (
        <div className={`landing-enter-root landing-enter-root--${stage}`}>
            {showOverlay && (
                <div
                    className={`landing-intro ${stage === 'exiting' ? 'landing-intro--exiting' : ''}`}
                    role="status"
                    aria-live="polite"
                    aria-busy={stage !== 'exiting'}
                >
                    <div className="landing-intro__sky" aria-hidden="true" />
                    <div className="landing-intro__sweep" aria-hidden="true" />
                    <div className="landing-intro__burst" aria-hidden="true">
                        <span />
                        <span />
                        <span />
                        <span />
                    </div>
                    <div className="landing-intro__particles" aria-hidden="true">
                        {Array.from({ length: PARTICLE_COUNT }, (_, i) => (
                            <span key={i} style={{ '--p': i }} />
                        ))}
                    </div>
                    <div className="landing-intro__mark">
                        <div className="landing-intro__halo" aria-hidden="true">
                            <span className="landing-intro__halo-ring" />
                            <span className="landing-intro__halo-ring landing-intro__halo-ring--delay" />
                        </div>
                        <div className="landing-intro__orbit" aria-hidden="true">
                            <span />
                            <span />
                            <span />
                        </div>
                        <img
                            src="/images/logo-blanco.png"
                            alt=""
                            className="landing-intro__logo"
                            width={220}
                            height={80}
                            decoding="async"
                        />
                        <div className="landing-intro__pulse-dots" aria-hidden="true">
                            <span />
                            <span />
                            <span />
                        </div>
                    </div>
                </div>
            )}
            <div
                className={`landing-enter-stage ${stage === 'ready' ? 'landing-enter-stage--ready' : ''}`}
            >
                {children}
            </div>
        </div>
    );
}
