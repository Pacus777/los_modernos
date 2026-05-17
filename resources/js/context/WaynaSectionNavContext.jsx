import WaynaEnterOverlay from '@/Components/Wayna/WaynaEnterOverlay';
import { useWaynaEnterAnimation } from '@/hooks/useWaynaEnterAnimation';
import { prefersReducedMotion } from '@/hooks/useWaynaEnterPresets';
import { consumeWaynaEnterSkip } from '@/utils/waynaEnterSkip';
import { createContext, useCallback, useContext, useRef, useState } from 'react';

const WaynaSectionNavContext = createContext(null);

function scrollToId(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

export function WaynaSectionNavProvider({ children }) {
    const [targetId, setTargetId] = useState(null);
    const pendingIdRef = useRef(null);

    const handleComplete = useCallback(() => {
        const id = pendingIdRef.current;
        pendingIdRef.current = null;
        setTargetId(null);
        if (id) {
            requestAnimationFrame(() => scrollToId(id));
        }
    }, []);

    const overlayStage = useWaynaEnterAnimation('section', {
        enabled: Boolean(targetId),
        onComplete: handleComplete,
    });

    const goToSection = useCallback((id) => {
        if (!id) {
            return;
        }
        if (prefersReducedMotion() || consumeWaynaEnterSkip()) {
            scrollToId(id);
            return;
        }
        pendingIdRef.current = id;
        setTargetId(id);
    }, []);

    return (
        <WaynaSectionNavContext.Provider value={{ goToSection }}>
            {children}
            {targetId && <WaynaEnterOverlay variant="section" stage={overlayStage} />}
        </WaynaSectionNavContext.Provider>
    );
}

export function useWaynaSectionNav() {
    const ctx = useContext(WaynaSectionNavContext);
    return ctx ?? { goToSection: scrollToId };
}
