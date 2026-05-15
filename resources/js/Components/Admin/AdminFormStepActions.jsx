import { adminFormFooterPrimaryBtn, adminFormFooterSecondaryBtn } from '@/Components/Admin/adminUi';
import { Link } from '@inertiajs/react';

/**
 * Barra de acciones del formulario admin por pasos (T-A8).
 * Usar arriba (sticky) y abajo; ambas ejecutan la misma acción.
 */
export default function AdminFormStepActions({
    sticky = false,
    cancelHref,
    cancelLabel = 'Cancelar',
    paso,
    totalPasos,
    processing,
    onAnterior,
    onSiguiente,
    guardarLabel = 'Guardar',
    guardandoLabel = 'Guardando…',
    siguienteLabel = 'Siguiente',
}) {
    const esUltimo = paso >= totalPasos;

    const barClass = sticky
        ? 'sticky top-0 z-10 border-b border-wayna-100 bg-white/95 px-6 py-4 shadow-sm shadow-wayna-900/[0.04] backdrop-blur-sm sm:px-8'
        : 'border-t border-wayna-100 bg-white/95 px-6 py-5 sm:px-8';

    return (
        <div
            className={`flex flex-col-reverse gap-3 ${barClass} sm:flex-row sm:items-center sm:justify-between`}
        >
            <Link href={cancelHref} className={adminFormFooterSecondaryBtn}>
                {cancelLabel}
            </Link>
            <div className="flex flex-col-reverse gap-2 sm:flex-row sm:gap-3">
                {paso > 1 && (
                    <button
                        type="button"
                        onClick={onAnterior}
                        disabled={processing}
                        className={adminFormFooterSecondaryBtn}
                    >
                        Anterior
                    </button>
                )}
                {esUltimo ? (
                    <button
                        type="submit"
                        disabled={processing}
                        className={adminFormFooterPrimaryBtn}
                    >
                        {processing ? guardandoLabel : guardarLabel}
                    </button>
                ) : (
                    <button
                        type="button"
                        onClick={onSiguiente}
                        disabled={processing}
                        className={adminFormFooterPrimaryBtn}
                    >
                        {siguienteLabel}
                    </button>
                )}
            </div>
        </div>
    );
}
