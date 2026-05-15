import Modal from '@/Components/Modal';

const CONFIRM_STYLES = {
    danger: 'rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/40 disabled:cursor-not-allowed disabled:opacity-60',
    primary:
        'btn-wayna-primary !rounded-xl !py-2.5 !text-sm !normal-case !tracking-normal',
    success:
        'rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 disabled:cursor-not-allowed disabled:opacity-60',
};

/**
 * Diálogo de confirmación interno (sustituye window.confirm).
 */
export default function ConfirmDialog({
    show = false,
    onClose,
    onConfirm,
    title = '¿Confirmar acción?',
    message = '',
    confirmLabel = 'Confirmar',
    cancelLabel = 'Cancelar',
    variant = 'danger',
    processing = false,
}) {
    const handleConfirm = () => {
        onConfirm?.();
    };

    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <div className="overflow-hidden rounded-lg">
                <div className="header-wayna-gradient px-5 py-4">
                    <h2
                        id="confirm-dialog-title"
                        className="text-lg font-bold text-white"
                    >
                        {title}
                    </h2>
                </div>

                <div className="bg-surface-card px-5 py-5">
                    <p
                        id="confirm-dialog-description"
                        className="text-sm leading-relaxed text-stone-600"
                    >
                        {message}
                    </p>

                    <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3">
                        <button
                            type="button"
                            onClick={onClose}
                            disabled={processing}
                            className="rounded-xl border border-surface-200 bg-surface px-4 py-2.5 text-sm font-bold text-wayna-900 transition hover:bg-wayna-50 disabled:opacity-60"
                        >
                            {cancelLabel}
                        </button>
                        <button
                            type="button"
                            onClick={handleConfirm}
                            disabled={processing}
                            className={CONFIRM_STYLES[variant] ?? CONFIRM_STYLES.danger}
                        >
                            {processing ? 'Procesando…' : confirmLabel}
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    );
}
