import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

/**
 * Dashboard temporal del administrador.
 *
 * Esta pantalla solo sirve para probar que el usuario con rol admin
 * es redirigido correctamente después del login.
 */
export default function Dashboard() {
    return (
        <AdminLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-wayna-950">
                    Panel Administrador
                </h2>
            }
        >
            <Head title="Panel Administrador" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-xl border border-wayna-100 bg-white shadow-sm shadow-wayna-900/5">
                        <div className="border-b border-wayna-50 bg-wayna-50/50 px-6 py-4">
                            <p className="text-sm font-medium text-wayna-900">
                                Bienvenido al panel general del administrador.
                            </p>
                        </div>
                        <div className="p-6 text-stone-700">
                            Usa el menú lateral para ir a <strong className="text-wayna-800">Emprendedores</strong> y gestionar el catálogo WAYNA.
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}