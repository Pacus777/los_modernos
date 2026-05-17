import { WAYNA_ENTER_PRESETS } from '@/hooks/useWaynaEnterPresets';

/**
 * Overlay iris + logo Wayna (sin texto). Solo presentacional; el stage lo controla el padre.
 */
export default function WaynaEnterOverlay({ variant = 'navigate', stage = 'ready' }) {
    const preset = WAYNA_ENTER_PRESETS[variant] ?? WAYNA_ENTER_PRESETS.navigate;

    if (stage === 'ready') {
        return null;
    }

    const showOverlay = stage === 'intro' || stage === 'exiting';
    const quickClass = variant !== 'full' ? 'landing-intro--quick' : '';

    if (!showOverlay) {
        return null;
    }

    return (
        <div
            className={`landing-intro landing-intro--${variant} ${quickClass} ${
                stage === 'exiting' ? 'landing-intro--exiting' : ''
            }`}
            role="status"
            aria-live="polite"
            aria-busy={stage !== 'exiting'}
        >
            <div className="landing-intro__sky" aria-hidden="true" />
            {variant === 'full' && <div className="landing-intro__sweep" aria-hidden="true" />}
            {preset.showBurst && (
                <div className="landing-intro__burst" aria-hidden="true">
                    <span />
                    <span />
                    <span />
                    {variant === 'full' && <span />}
                </div>
            )}
            {preset.showParticles && preset.particleCount > 0 && (
                <div className="landing-intro__particles" aria-hidden="true">
                    {Array.from({ length: preset.particleCount }, (_, i) => (
                        <span key={i} style={{ '--p': i }} />
                    ))}
                </div>
            )}
            <div className="landing-intro__mark">
                <div className="landing-intro__halo" aria-hidden="true">
                    <span className="landing-intro__halo-ring" />
                    <span className="landing-intro__halo-ring landing-intro__halo-ring--delay" />
                </div>
                {variant === 'full' && (
                    <div className="landing-intro__orbit" aria-hidden="true">
                        <span />
                        <span />
                        <span />
                    </div>
                )}
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
    );
}
