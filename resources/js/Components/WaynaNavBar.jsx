import { WaynaBrand } from '@/Components/ApplicationLogo';

/**
 * Cabecera naranja Wayna (#f07e26) con logo grande.
 */
export default function WaynaNavBar({
    href = '/',
    children,
    className = '',
    logoSize = 'nav-lg',
    sticky = false,
}) {
    return (
        <header
            className={`nav-wayna-bar ${sticky ? 'sticky top-0 z-50' : ''} ${className}`.trim()}
        >
            <div className="mx-auto flex min-h-[4.25rem] max-w-7xl flex-wrap items-center justify-between gap-x-3 gap-y-2 px-4 py-2 sm:min-h-[4.75rem] sm:flex-nowrap sm:gap-4 sm:px-6 sm:py-0 lg:px-8">
                <WaynaBrand href={href} size={logoSize} tone="on-brand" className="shrink-0" />

                {children ? (
                    <div className="flex min-w-0 flex-1 flex-wrap items-center justify-end gap-2 sm:flex-nowrap sm:gap-3">
                        {children}
                    </div>
                ) : (
                    <span className="w-10 shrink-0 sm:w-14" aria-hidden />
                )}
            </div>
        </header>
    );
}
