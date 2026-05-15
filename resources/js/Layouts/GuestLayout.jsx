import LanguageSelector from '@/Components/LanguageSelector';
import WaynaNavBar from '@/Components/WaynaNavBar';

/**
 * Invitado / turista — barra #f07e26, contenido sobre base #E8E6E0.
 *
 * @param {'card'|'full'} variant
 */
export default function GuestLayout({
    children,
    variant = 'card',
    navHref = '/',
    contentClassName = '',
}) {
    if (variant === 'full') {
        return (
            <div className="min-h-screen bg-surface antialiased">
                <WaynaNavBar href={navHref}>
                    <LanguageSelector variant="on-brand" />
                </WaynaNavBar>

                <main
                    className={`mx-auto w-full max-w-lg px-4 py-6 sm:max-w-xl sm:px-6 lg:max-w-2xl ${contentClassName}`.trim()}
                >
                    {children}
                </main>
            </div>
        );
    }

    return (
        <div className="flex min-h-screen flex-col bg-surface">
            <WaynaNavBar href={navHref}>
                <LanguageSelector variant="on-brand" />
            </WaynaNavBar>

            <div className="flex flex-1 flex-col items-center justify-center px-4 py-8 sm:px-6">
                <div className="w-full max-w-md overflow-hidden rounded-2xl border border-wayna-200/80 bg-surface-card px-6 py-6 shadow-xl shadow-wayna-900/10 sm:rounded-3xl">
                    {children}
                </div>
            </div>
        </div>
    );
}
