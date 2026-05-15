import ConfirmDialog from '@/Components/ConfirmDialog';
import { useCallback, useState } from 'react';

const INITIAL = {
    open: false,
    title: '',
    message: '',
    confirmLabel: 'Confirmar',
    cancelLabel: 'Cancelar',
    variant: 'danger',
    processing: false,
    onConfirm: null,
};

/**
 * Hook para confirmaciones sin window.confirm.
 */
export function useConfirmDialog() {
    const [state, setState] = useState(INITIAL);

    const close = useCallback(() => {
        setState(INITIAL);
    }, []);

    const requestConfirm = useCallback((options) => {
        setState({
            ...INITIAL,
            open: true,
            title: options.title ?? INITIAL.title,
            message: options.message ?? '',
            confirmLabel: options.confirmLabel ?? 'Confirmar',
            cancelLabel: options.cancelLabel ?? 'Cancelar',
            variant: options.variant ?? 'danger',
            onConfirm: options.onConfirm ?? null,
        });
    }, []);

    const setProcessing = useCallback((processing) => {
        setState((prev) => ({ ...prev, processing }));
    }, []);

    const handleConfirm = useCallback(() => {
        if (state.onConfirm) {
            state.onConfirm({ close, setProcessing });
        } else {
            close();
        }
    }, [state.onConfirm, close, setProcessing]);

    const ConfirmDialogPortal = useCallback(
        () => (
            <ConfirmDialog
                show={state.open}
                onClose={close}
                onConfirm={handleConfirm}
                title={state.title}
                message={state.message}
                confirmLabel={state.confirmLabel}
                cancelLabel={state.cancelLabel}
                variant={state.variant}
                processing={state.processing}
            />
        ),
        [state, close, handleConfirm],
    );

    return { requestConfirm, close, setProcessing, ConfirmDialogPortal };
}
