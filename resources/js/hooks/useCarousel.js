import { useCallback, useEffect, useState } from 'react';

/**
 * Carrusel ligero sin librerías externas.
 */
export function useCarousel(slideCount, { autoMs = 6000, loop = true } = {}) {
    const [index, setIndex] = useState(0);
    const [paused, setPaused] = useState(false);

    const goTo = useCallback(
        (next) => {
            if (slideCount <= 0) {
                return;
            }
            setIndex(() => {
                if (next < 0) {
                    return loop ? slideCount - 1 : 0;
                }
                if (next >= slideCount) {
                    return loop ? 0 : slideCount - 1;
                }
                return next;
            });
        },
        [slideCount, loop],
    );

    const next = useCallback(() => {
        setIndex((current) => {
            if (slideCount <= 0) {
                return 0;
            }
            const nextIndex = current + 1;
            if (nextIndex >= slideCount) {
                return loop ? 0 : current;
            }
            return nextIndex;
        });
    }, [slideCount, loop]);

    const prev = useCallback(() => {
        setIndex((current) => {
            if (slideCount <= 0) {
                return 0;
            }
            const nextIndex = current - 1;
            if (nextIndex < 0) {
                return loop ? slideCount - 1 : 0;
            }
            return nextIndex;
        });
    }, [slideCount, loop]);

    useEffect(() => {
        if (slideCount <= 1 || paused || autoMs <= 0) {
            return undefined;
        }

        const id = window.setInterval(() => {
            setIndex((current) => (current + 1 >= slideCount ? (loop ? 0 : current) : current + 1));
        }, autoMs);

        return () => window.clearInterval(id);
    }, [slideCount, paused, autoMs, loop]);

    return {
        index,
        goTo,
        next,
        prev,
        paused,
        setPaused,
    };
}
