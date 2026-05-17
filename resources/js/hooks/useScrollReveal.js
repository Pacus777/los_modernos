import { useEffect, useRef, useState } from 'react';

/**
 * Revela el elemento al entrar en viewport (respeta prefers-reduced-motion).
 */
export function useScrollReveal({ threshold = 0.12, rootMargin = '0px 0px -8% 0px' } = {}) {
    const ref = useRef(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const node = ref.current;

        if (!node) {
            return undefined;
        }

        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reduced) {
            setVisible(true);
            return undefined;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setVisible(true);
                    observer.disconnect();
                }
            },
            { threshold, rootMargin },
        );

        observer.observe(node);

        return () => observer.disconnect();
    }, [threshold, rootMargin]);

    return { ref, visible };
}
