import AuthLoginVisual from '@/Components/Auth/AuthLoginVisual';
import LanguageSelector from '@/Components/LanguageSelector';
import WaynaNavBar from '@/Components/WaynaNavBar';
import { useReloadOnHistoryRestore } from '@/hooks/useReloadOnHistoryRestore';
import { useSecureGuestHistory } from '@/hooks/useSecureGuestHistory';

/**
 * Login estilo split con ondas — formulario a la izquierda, visual Wayna a la derecha.
 */
export default function AuthLoginLayout({ children, navHref = '/' }) {
    useReloadOnHistoryRestore();
    useSecureGuestHistory();

    return (
        <div className="auth-login-split flex min-h-screen flex-col bg-surface antialiased">
            <WaynaNavBar href={navHref} sticky>
                <LanguageSelector variant="on-brand" compact />
            </WaynaNavBar>

            <div className="relative flex min-h-0 flex-1 flex-col lg:flex-row">
                <section className="auth-login-split__form relative flex flex-1 flex-col justify-center px-6 py-10 sm:px-10 lg:px-14 xl:px-20">
                    <div
                        className="pointer-events-none absolute -left-8 top-8 h-24 w-24 rounded-full bg-wayna-400/20 blur-2xl"
                        aria-hidden
                    />
                    <div
                        className="pointer-events-none absolute bottom-12 left-6 hidden h-16 w-16 opacity-90 lg:block"
                        aria-hidden
                    >
                        <svg viewBox="0 0 64 64" fill="none" className="h-full w-full">
                            <path
                                d="M32 4C20 18 8 28 8 40a24 24 0 1048 0c0-12-12-22-24-36z"
                                fill="#f07e26"
                                fillOpacity="0.35"
                            />
                            <path
                                d="M28 36c-6 4-10 10-10 16 0 8 6 12 14 12 10 0 18-8 18-18 0-10-12-18-22-10z"
                                fill="#d96d1c"
                                fillOpacity="0.5"
                            />
                        </svg>
                    </div>

                    <div className="auth-login-split__content relative z-10 mx-auto w-full max-w-md">
                        {children}
                    </div>

                    <svg
                        className="auth-login-split-curve pointer-events-none absolute -right-px top-0 hidden h-full w-14 text-[#f7f5f2] lg:block"
                        viewBox="0 0 56 800"
                        preserveAspectRatio="none"
                        aria-hidden
                    >
                        <path
                            fill="currentColor"
                            d="M56,0 C28,120 8,280 20,400 C32,520 48,640 56,800 L56,0 Z"
                        />
                    </svg>
                </section>

                <AuthLoginVisual />
            </div>
        </div>
    );
}
