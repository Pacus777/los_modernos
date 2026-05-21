import AdminEmprendedorCuentaPanel from '@/Components/Admin/AdminEmprendedorCuentaPanel';
import { modalWaynaBodyCompact, modalWaynaFooter, modalWaynaShell } from '@/Components/Admin/adminUi';
import Modal from '@/Components/Modal';

/**
 * E-03 — Credenciales de acceso desde el directorio de emprendedores.
 */
export default function EmprendedorCuentaModal({
    show = false,
    onClose,
    emprendedor = null,
    cuenta: cuentaProp = {},
    fotoSrc = null,
}) {
    if (!emprendedor) {
        return null;
    }

    const cuenta =
        cuentaProp?.tiene_cuenta || cuentaProp?.email
            ? cuentaProp
            : emprendedor?.cuenta ?? cuentaProp;

    const nombreCompleto =
        emprendedor.nombre_completo ||
        `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim() ||
        'Emprendedor';

    return (
        <Modal show={show} onClose={onClose} maxWidth="2xl">
            <div className={modalWaynaShell}>
                <div className="header-wayna-gradient shrink-0 px-4 py-4 sm:px-6">
                    <div className="flex items-start justify-between gap-3">
                        <div className="min-w-0 flex-1">
                            <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-100/95">
                                Cuenta de acceso
                            </p>
                            <h2 className="mt-1 text-lg font-bold text-white sm:text-xl">
                                Credenciales — {nombreCompleto}
                            </h2>
                            <p className="mt-1 text-sm text-orange-50/90">
                                Creá o reenviá el acceso al panel del emprendedor.
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={onClose}
                            className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-xl font-bold text-white ring-1 ring-white/30 transition hover:bg-white/25"
                            aria-label="Cerrar"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <div className={`${modalWaynaBodyCompact} p-0`}>
                    <AdminEmprendedorCuentaPanel
                        emprendedorId={emprendedor.id}
                        emprendedor={{
                            nombre: emprendedor.nombre,
                            apellidos: emprendedor.apellidos,
                            nombre_completo: nombreCompleto,
                        }}
                        cuenta={cuenta ?? {}}
                        nombreSugerido={nombreCompleto}
                        fotoSrc={fotoSrc}
                        soloContenido
                        desdeListado
                    />
                </div>

                <div className={`${modalWaynaFooter} flex justify-end`}>
                    <button
                        type="button"
                        onClick={onClose}
                        className="btn-wayna-secondary w-full sm:w-auto sm:min-w-[8rem]"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </Modal>
    );
}
