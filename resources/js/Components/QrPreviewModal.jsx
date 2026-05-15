import Modal from '@/Components/Modal';

/**
 * Vista ampliada de código QR (sin abrir pestaña del navegador).
 */
export default function QrPreviewModal({
    show = false,
    onClose,
    src,
    title = 'Código QR',
    subtitle = '',
}) {
    if (!src) {
        return null;
    }

    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <div className="overflow-hidden">
                <div className="header-wayna-gradient px-5 py-4">
                    <h2 className="text-lg font-bold text-white">{title}</h2>
                    {subtitle ? (
                        <p className="mt-1 text-sm text-white/90">{subtitle}</p>
                    ) : null}
                </div>

                <div className="flex flex-col items-center gap-4 bg-surface-card px-5 py-6">
                    <div className="rounded-2xl border-2 border-wayna-200 bg-white p-4 shadow-inner">
                        <img
                            src={src}
                            alt={title}
                            className="h-56 w-56 max-w-full object-contain sm:h-64 sm:w-64"
                        />
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="btn-wayna-primary w-full sm:w-auto"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </Modal>
    );
}
