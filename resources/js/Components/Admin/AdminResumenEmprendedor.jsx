/**
 * Recordatorio del emprendedor elegido (formularios por pasos).
 */
export default function AdminResumenEmprendedor({
    titulo = 'Emprendedor',
    nombre,
    tipoEtiqueta = null,
    descripcion = null,
    onCambiar = null,
    textoCambiar = 'Cambiar',
}) {
    if (!nombre) {
        return null;
    }

    return (
        <div className="mb-5 flex flex-col gap-3 rounded-xl border border-wayna-200 bg-gradient-to-r from-wayna-50/90 via-white to-wayna-50/40 px-4 py-3 sm:flex-row sm:items-start sm:justify-between">
            <div className="min-w-0 flex-1">
                <p className="text-[10px] font-bold uppercase tracking-[0.14em] text-wayna-700">
                    {titulo}
                </p>
                <p className="mt-1 text-sm font-bold text-wayna-950">{nombre}</p>
                {tipoEtiqueta ? (
                    <span className="mt-2 inline-flex rounded-full bg-wayna-100 px-2.5 py-0.5 text-xs font-bold text-wayna-800 ring-1 ring-wayna-200/80">
                        {tipoEtiqueta}
                    </span>
                ) : null}
                {descripcion ? (
                    <p className="mt-1 text-sm leading-relaxed text-stone-600">{descripcion}</p>
                ) : null}
            </div>
            {onCambiar ? (
                <button
                    type="button"
                    onClick={onCambiar}
                    className="shrink-0 self-start rounded-lg border border-wayna-200 bg-white px-3 py-1.5 text-xs font-bold text-wayna-700 transition hover:border-wayna-300 hover:bg-wayna-50"
                >
                    {textoCambiar}
                </button>
            ) : null}
        </div>
    );
}
