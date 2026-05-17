import LanguageSelector from '@/Components/LanguageSelector';
import ChatWidget, { ChatWidgetProvider } from '@/Components/Turista/ChatWidget';
import WaynaNavBar from '@/Components/WaynaNavBar';

/**
 * Invitado / turista — barra #f07e26, contenido sobre base #E8E6E0.
 *
 * @param {'card'|'full'} variant
 * @param {number|null} [chatContextEmprendedorId] - Contexto del chat en perfil público.
 */
export default function GuestLayout({
    children,
    variant = 'card',
    navHref = '/',
    contentClassName = '',
    chatContextEmprendedorId = null,
}) {
    const navExtras = <LanguageSelector variant="on-brand" compact />;

    if (variant === 'full') {
        return (
            <ChatWidgetProvider contextEmprendedorId={chatContextEmprendedorId}>
                <div className="min-h-screen bg-surface antialiased">
                    <WaynaNavBar href={navHref} sticky>
                        {navExtras}
                    </WaynaNavBar>

                    <main
                        className={`mx-auto w-full max-w-lg px-4 py-6 sm:max-w-xl sm:px-6 lg:max-w-2xl ${contentClassName}`.trim()}
                    >
                        {children}
                    </main>

                    <ChatWidget />
                </div>
            </ChatWidgetProvider>
        );
    }

    return (
        <ChatWidgetProvider contextEmprendedorId={chatContextEmprendedorId}>
            <div className="flex min-h-screen flex-col bg-surface">
                <WaynaNavBar href={navHref} sticky>
                    {navExtras}
                </WaynaNavBar>

                <div className="flex flex-1 flex-col items-center justify-center px-4 py-8 sm:px-6">
                    <div className="w-full max-w-md overflow-hidden rounded-2xl border border-wayna-200/80 bg-surface-card px-6 py-6 shadow-xl shadow-wayna-900/10 sm:rounded-3xl">
                        {children}
                    </div>
                </div>

                <ChatWidget />
            </div>
        </ChatWidgetProvider>
    );
}
