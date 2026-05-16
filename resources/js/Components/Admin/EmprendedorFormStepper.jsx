/**
 * Indicador visual de pasos (T-A7) para el formulario de emprendedor.
 */
export const PASOS_EMPRENDEDOR = [
    { id: 1, titulo: 'Datos básicos', corto: '1' },
    { id: 2, titulo: 'Tipo y región', corto: '2' },
    { id: 3, titulo: 'Meta y estado', corto: '3' },
    { id: 4, titulo: 'Confirmación', corto: '4' },
];

export default function EmprendedorFormStepper({ pasoActual, onSeleccionar }) {
    return (
        <nav aria-label="Pasos del registro" className="px-4 py-6 sm:px-8">
            <ol className="flex items-start justify-between gap-1">
                {PASOS_EMPRENDEDOR.map((paso, indice) => {
                    const completado = pasoActual > paso.id;
                    const activo = pasoActual === paso.id;
                    const ultimo = indice === PASOS_EMPRENDEDOR.length - 1;

                    return (
                        <li
                            key={paso.id}
                            className={`flex flex-1 flex-col items-center ${ultimo ? 'flex-none' : ''}`}
                        >
                            <div className="flex w-full items-center">
                                <button
                                    type="button"
                                    onClick={() => onSeleccionar?.(paso.id)}
                                    disabled={!onSeleccionar || paso.id > pasoActual}
                                    className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold transition sm:h-10 sm:w-10 sm:text-sm ${
                                        activo
                                            ? 'bg-wayna-600 text-white shadow-md ring-4 ring-wayna-200'
                                            : completado
                                              ? 'bg-wayna-500 text-white shadow-sm'
                                              : 'bg-white text-stone-500 ring-2 ring-wayna-200'
                                    } ${onSeleccionar && (completado || activo) ? 'cursor-pointer hover:scale-105' : ''}`}
                                    aria-current={activo ? 'step' : undefined}
                                >
                                    {completado ? (
                                        <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden>
                                            <path
                                                fillRule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    ) : (
                                        paso.corto
                                    )}
                                </button>
                                {!ultimo && (
                                    <div
                                        className={`mx-1 h-0.5 flex-1 rounded-full sm:mx-2 ${
                                            completado ? 'bg-wayna-400' : 'bg-wayna-100'
                                        }`}
                                        aria-hidden
                                    />
                                )}
                            </div>
                            <p
                                className={`mt-2 hidden max-w-[5.5rem] text-center text-[10px] font-bold leading-tight sm:block sm:text-xs ${
                                    activo ? 'text-wayna-800' : 'text-stone-500'
                                }`}
                            >
                                {paso.titulo}
                            </p>
                        </li>
                    );
                })}
            </ol>
            <p className="mt-3 text-center text-xs font-semibold text-wayna-800 sm:hidden">
                Paso {pasoActual} de {PASOS_EMPRENDEDOR.length}:{' '}
                {PASOS_EMPRENDEDOR.find((p) => p.id === pasoActual)?.titulo}
            </p>
        </nav>
    );
}
