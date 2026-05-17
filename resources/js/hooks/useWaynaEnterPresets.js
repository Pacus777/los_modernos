export const WAYNA_ENTER_PRESETS = {
    full: {
        durationMs: 1680,
        exitStartMs: 1180,
        particleCount: 16,
        showBurst: true,
        showParticles: true,
    },
    navigate: {
        durationMs: 1100,
        exitStartMs: 760,
        particleCount: 8,
        showBurst: true,
        showParticles: false,
    },
    section: {
        durationMs: 720,
        exitStartMs: 480,
        particleCount: 0,
        showBurst: false,
        showParticles: false,
    },
};

export function prefersReducedMotion() {
    return typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
