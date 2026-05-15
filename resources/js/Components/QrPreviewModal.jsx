import {
    modalWaynaBody,
    modalWaynaFooter,
    modalWaynaShell,
} from '@/Components/Admin/adminUi';
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
            <div className={modalWaynaShell}>
                <div className="header-wayna-gradient shrink-0 px-4 py-3 sm:px-5">
                    <h2 className="text-base font-bold text-white sm:text-lg">{title}</h2>
                    {subtitle ? (
                        <p className="mt-1 text-sm text-white/90">{subtitle}</p>
                    ) : null}
                </div>

                <div
                    className={`${modalWaynaBody} flex flex-col items-center gap-3 px-4 py-4 sm:px-5 sm:py-5`}
                >
                    <div className="rounded-2xl border-2 border-wayna-200 bg-white p-3 shadow-inner">
                        <img
                            src={src}
                            alt={title}
                            className="h-44 w-44 max-w-full object-contain sm:h-48 sm:w-48"
                        />
                    </div>
                </div>

                <div className={`${modalWaynaFooter} flex justify-center`}>
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
