import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

/**
 * Dashboard temporal del administrador.
 *
 * Esta pantalla solo sirve para probar que el usuario con rol admin
 * es redirigido correctamente después del login.
 */
export default function Dashboard() {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Panel Administrador
                </h2>
            }
        >
            <Head title="Panel Administrador" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            Bienvenido al panel general del administrador.
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}