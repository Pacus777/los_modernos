import WaynaEnterTransition from '@/Components/Wayna/WaynaEnterTransition';

/**
 * Intro visual al montar el landing (sin texto: solo logo, luz y apertura iris).
 */
export default function LandingEnterExperience({ children }) {
    return (
        <WaynaEnterTransition variant="full" revealStage>
            {children}
        </WaynaEnterTransition>
    );
}
