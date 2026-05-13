import CajeroLayout from '@/Layouts/CajeroLayout';
import { Head } from '@inertiajs/react';

/**
 * Pantalla temporal del cajero.
 *
 * Esta pantalla solo sirve para probar que el usuario con rol cajero
 * es redirigido correctamente después del login.
 */
export default function Efectivo() {
    return (
        <CajeroLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-wayna-950">
                    Panel Cajero
                </h2>
            }
        >
            <Head title="Pagos en efectivo" />

            <div className="py-12">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-xl border border-wayna-100 bg-white shadow-sm shadow-wayna-900/5">
                        <div className="p-6 text-stone-700">
                            Pantalla temporal para confirmar pagos en efectivo.
                        </div>
                    </div>
                </div>
            </div>
        </CajeroLayout>
    );
}