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
            className={`nav-wayna-bar ${sticky ? 'sticky top-0 z-40' : ''} ${className}`.trim()}
        >
            <div className="mx-auto flex h-[4.25rem] max-w-7xl items-center justify-between gap-4 px-4 sm:h-[4.75rem] sm:px-6 lg:px-8">
                <WaynaBrand href={href} size={logoSize} tone="on-brand" />

                {children ? (
                    <div className="flex shrink-0 items-center gap-3">{children}</div>
                ) : (
                    <span className="w-10 sm:w-14" aria-hidden />
                )}
            </div>
        </header>
    );
}
