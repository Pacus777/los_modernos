import WaynaEnterOverlay from '@/Components/Wayna/WaynaEnterOverlay';
import { useWaynaEnterAnimation } from '@/hooks/useWaynaEnterAnimation';

/**
 * Transición Wayna al montar una página o vista.
 * variant: full (landing) | navigate (perfil, punto) | section (scroll interno)
 * revealStage: animación escalonada del contenido (solo landing).
 */
export default function WaynaEnterTransition({
    children,
    variant = 'navigate',
    revealStage = false,
    className = '',
}) {
    const stage = useWaynaEnterAnimation(variant);

    if (revealStage) {
        return (
            <div className={`landing-enter-root landing-enter-root--${stage} ${className}`.trim()}>
                <WaynaEnterOverlay variant={variant} stage={stage} />
                <div
                    className={`landing-enter-stage ${stage === 'ready' ? 'landing-enter-stage--ready' : ''}`}
                >
                    {children}
                </div>
            </div>
        );
    }

    return (
        <div className={`wayna-enter-root wayna-enter-root--${stage} ${className}`.trim()}>
            <WaynaEnterOverlay variant={variant} stage={stage} />
            <div className={stage === 'ready' ? 'wayna-enter-content--ready' : ''}>{children}</div>
        </div>
    );
}
