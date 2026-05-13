import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

/**
 * Pantalla temporal del cajero.
 *
 * Esta pantalla solo sirve para probar que el usuario con rol cajero
 * es redirigido correctamente después del login.
 */
export default function Efectivo() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Panel Cajero
                </h2>
            }
        >
            <Head title="Pagos en efectivo" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            Pantalla temporal para confirmar pagos en efectivo.
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}