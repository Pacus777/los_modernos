import { consumeWaynaEnterSkip } from '@/utils/waynaEnterSkip';
import { useEffect, useRef, useState } from 'react';
import { WAYNA_ENTER_PRESETS, prefersReducedMotion } from '@/hooks/useWaynaEnterPresets';

/**
 * Controla intro / exiting / ready y bloqueo de scroll del body.
 */
export function useWaynaEnterAnimation(variant = 'navigate', { enabled = true, onComplete } = {}) {
    const preset = WAYNA_ENTER_PRESETS[variant] ?? WAYNA_ENTER_PRESETS.navigate;
    const [stage, setStage] = useState(enabled ? 'intro' : 'ready');
    const onCompleteRef = useRef(onComplete);
    onCompleteRef.current = onComplete;

    useEffect(() => {
        if (!enabled) {
            setStage('ready');
            return undefined;
        }

        if (prefersReducedMotion() || consumeWaynaEnterSkip()) {
            setStage('ready');
            onCompleteRef.current?.();
            return undefined;
        }

        setStage('intro');
        document.body.classList.add('landing-intro-active');

        const exitTimer = window.setTimeout(() => setStage('exiting'), preset.exitStartMs);
        const readyTimer = window.setTimeout(() => {
            setStage('ready');
            document.body.classList.remove('landing-intro-active');
            onCompleteRef.current?.();
        }, preset.durationMs);

        return () => {
            window.clearTimeout(exitTimer);
            window.clearTimeout(readyTimer);
            document.body.classList.remove('landing-intro-active');
        };
    }, [enabled, preset.durationMs, preset.exitStartMs]);

    return stage;
}
