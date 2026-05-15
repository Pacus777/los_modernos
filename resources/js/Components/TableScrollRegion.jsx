/**
 * Contenedor de tabla con scroll horizontal en móvil (T-A29).
 */
export default function TableScrollRegion({
    children,
    className = '',
    hint = 'Deslizá para ver todas las columnas →',
}) {
    return (
        <div>
            <p
                className="mb-2 px-1 text-xs font-medium text-stone-500 md:hidden"
                aria-hidden
            >
                {hint}
            </p>
            <div className={`table-scroll-region -mx-1 px-1 ${className}`.trim()}>
                {children}
            </div>
        </div>
    );
}
