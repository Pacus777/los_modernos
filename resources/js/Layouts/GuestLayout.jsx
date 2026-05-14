import ApplicationLogo from '@/Components/ApplicationLogo';
import LanguageSelector from '@/Components/LanguageSelector';
import { Link } from '@inertiajs/react';

/**
 * Invitado / turista — fondos claros y acentos naranja WAYNA.
 *
 * @param {'card'|'full'} variant
 */
export default function GuestLayout({ children, variant = 'card' }) {
    if (variant === 'full') {
        return (
            <div className="min-h-screen bg-gradient-to-b from-wayna-50 to-orange-50/40 antialiased">
                <header className="mx-auto flex w-full max-w-lg items-center justify-between px-4 py-4 sm:max-w-xl sm:px-6">
                    <Link href="/" className="text-sm font-bold text-wayna-700">
                        Wayna Conecta
                    </Link>

                    <LanguageSelector />
                </header>

                <main className="mx-auto w-full max-w-lg px-4 pb-8 sm:max-w-xl sm:px-6">
                    {children}
                </main>
            </div>
        );
    }

    return (
        <div className="flex min-h-screen flex-col items-center bg-gradient-to-b from-wayna-50 via-white to-orange-50/30 px-4 pt-8 sm:justify-center sm:px-0 sm:pt-0">
            <div className="absolute right-4 top-4">
                <LanguageSelector />
            </div>

            <div className="shrink-0">
                <Link
                    href="/"
                    className="block rounded-2xl ring-2 ring-wayna-200/60 ring-offset-4 ring-offset-wayna-50"
                >
                    <ApplicationLogo className="h-20 w-20 fill-current text-wayna-600" />
                </Link>
            </div>

            <div className="mt-8 w-full max-w-md overflow-hidden rounded-2xl border border-wayna-100 bg-white px-6 py-6 shadow-lg shadow-wayna-900/5 sm:rounded-2xl">
                {children}
            </div>
        </div>
    );
}