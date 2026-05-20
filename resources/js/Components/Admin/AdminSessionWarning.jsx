import Modal from '@/Components/Modal';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

/**
 * Aviso antes de que expire la sesión admin (S3-05).
 */
export default function AdminSessionWarning() {
    const adminSession = usePage().props.adminSession;
    const [show, setShow] = useState(false);
    const [minutosRestantes, setMinutosRestantes] = useState(0);
    const expiresAtRef = useRef(0);

    useEffect(() => {
        if (!adminSession) {
            return undefined;
        }

        const lifetimeMs = adminSession.lifetimeMinutes * 60 * 1000;
        const warnMs = adminSession.warnMinutes * 60 * 1000;
        expiresAtRef.current = adminSession.lastActivity * 1000 + lifetimeMs;

        const revisar = () => {
            const ahora = Date.now();
            const expira = expiresAtRef.current;

            if (ahora >= expira) {
                router.post(route('logout'));
                return;
            }

            const restante = expira - ahora;

            if (restante <= warnMs) {
                setShow(true);
                setMinutosRestantes(Math.max(1, Math.ceil(restante / 60000)));
            } else {
                setShow(false);
            }
        };

        revisar();
        const id = setInterval(revisar, 15000);

        return () => clearInterval(id);
    }, [adminSession]);

    const extender = () => {
        router.post(
            route('admin.session.touch'),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    if (!adminSession) {
                        return;
                    }
                    expiresAtRef.current =
                        Date.now() + adminSession.lifetimeMinutes * 60 * 1000;
                    setShow(false);
                },
            },
        );
    };

    if (!adminSession) {
        return null;
    }

    return (
        <Modal show={show} onClose={() => {}} closeable={false} maxWidth="md">
            <div className="p-6">
                <h2 className="text-lg font-bold text-wayna-950">Tu sesión va a expirar</h2>
                <p className="mt-2 text-sm text-stone-600">
                    Por seguridad, el panel admin cierra la sesión tras{' '}
                    {adminSession.lifetimeMinutes} minutos sin actividad.
                    {minutosRestantes > 0 && (
                        <>
                            {' '}
                            Quedan unos <strong>{minutosRestantes}</strong> minuto
                            {minutosRestantes === 1 ? '' : 's'}.
                        </>
                    )}
                </p>
                <div className="mt-6 flex flex-wrap gap-3">
                    <button
                        type="button"
                        className="btn-wayna-primary"
                        onClick={extender}
                    >
                        Seguir conectada
                    </button>
                    <button
                        type="button"
                        className="rounded-xl border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50"
                        onClick={() => router.post(route('logout'))}
                    >
                        Cerrar sesión
                    </button>
                </div>
            </div>
        </Modal>
    );
}
