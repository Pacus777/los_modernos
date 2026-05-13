import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';

/**
 * Página de listado de emprendedores.
 *
 * Esta página recibe los datos desde Laravel mediante Inertia.
 * No se usa fetch, axios ni useEffect porque el Controller ya envía
 * la lista de emprendedores como prop.
 */
export default function Index({ emprendedores }) {
    /*
    |--------------------------------------------------------------------------
    | Props globales de Inertia
    |--------------------------------------------------------------------------
    |
    | Si en HandleInertiaRequests se comparte flash.success, aquí podemos
    | mostrar mensajes después de crear, editar o desactivar un emprendedor.
    |
    */

    const { flash } = usePage().props;

    /**
     * Desactiva un emprendedor.
     *
     * En nuestro controller, el método destroy no elimina físicamente
     * el registro. Solo cambia el estado a "inactivo".
     *
     * Usamos router.delete porque la ruta viene de Route::resource().
     */
    const desactivarEmprendedor = (emprendedor) => {
        const confirmar = window.confirm(
            `¿Seguro que deseas desactivar a ${emprendedor.nombre} ${emprendedor.apellidos}?`
        );

        if (!confirmar) {
            return;
        }

        router.delete(route('admin.emprendedores.destroy', emprendedor.id), {
            preserveScroll: true,
        });
    };

    /**
     * Construye la URL pública de una fotografía guardada en storage.
     *
     * En la base de datos guardamos algo como:
     * emprendedores/fotografias/archivo.jpg
     *
     * En el navegador se accede como:
     * /storage/emprendedores/fotografias/archivo.jpg
     */
    const obtenerUrlFotografia = (fotografia) => {
        if (!fotografia) {
            return null;
        }

        return `/storage/${fotografia}`;
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            Emprendedores
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Gestión de emprendedores registrados en WAYNA.
                        </p>
                    </div>

                    <Link
                        href={route('admin.emprendedores.create')}
                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
                    >
                        Nuevo emprendedor
                    </Link>
                </div>
            }
        >
            <Head title="Emprendedores" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div className="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {flash.success}
                        </div>
                    )}

                    <div className="overflow-hidden rounded-xl bg-white shadow-sm">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-base font-semibold text-gray-900">
                                Lista de emprendedores
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Desde esta sección se puede editar o desactivar un emprendedor.
                            </p>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Emprendedor
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Meta
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Estado
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            QR
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {emprendedores.data.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan="5"
                                                className="px-6 py-8 text-center text-sm text-gray-500"
                                            >
                                                No hay emprendedores registrados todavía.
                                            </td>
                                        </tr>
                                    )}

                                    {emprendedores.data.map((emprendedor) => (
                                        <tr key={emprendedor.id} className="hover:bg-gray-50">
                                            <td className="whitespace-nowrap px-6 py-4">
                                                <div className="flex items-center gap-3">
                                                    {obtenerUrlFotografia(emprendedor.fotografia) ? (
                                                        <img
                                                            src={obtenerUrlFotografia(
                                                                emprendedor.fotografia
                                                            )}
                                                            alt={`Fotografía de ${emprendedor.nombre}`}
                                                            className="h-11 w-11 rounded-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500">
                                                            {emprendedor.nombre?.charAt(0)}
                                                        </div>
                                                    )}

                                                    <div>
                                                        <div className="text-sm font-semibold text-gray-900">
                                                            {emprendedor.nombre} {emprendedor.apellidos}
                                                        </div>
                                                        <div className="max-w-sm truncate text-sm text-gray-500">
                                                            {emprendedor.descripcion || 'Sin descripción'}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                Bs. {Number(emprendedor.meta_monto).toFixed(2)}
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${
                                                        emprendedor.estado === 'activo'
                                                            ? 'bg-green-100 text-green-700'
                                                            : 'bg-gray-100 text-gray-700'
                                                    }`}
                                                >
                                                    {emprendedor.estado}
                                                </span>
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {emprendedor.qr_url ? (
                                                    <a
                                                        href={`/storage/${emprendedor.qr_url}`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="font-medium text-indigo-600 hover:text-indigo-800"
                                                    >
                                                        Ver QR
                                                    </a>
                                                ) : (
                                                    <span className="text-gray-400">
                                                        Pendiente
                                                    </span>
                                                )}
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                                <div className="flex justify-end gap-2">
                                                    <Link
                                                        href={route(
                                                            'admin.emprendedores.edit',
                                                            emprendedor.id
                                                        )}
                                                        className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Editar
                                                    </Link>

                                                    {emprendedor.estado === 'activo' && (
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                desactivarEmprendedor(emprendedor)
                                                            }
                                                            className="rounded-md border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50"
                                                        >
                                                            Desactivar
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {emprendedores.links && emprendedores.links.length > 3 && (
                            <div className="border-t border-gray-200 px-6 py-4">
                                <div className="flex flex-wrap gap-2">
                                    {emprendedores.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            preserveScroll
                                            className={`rounded-md px-3 py-1.5 text-sm ${
                                                link.active
                                                    ? 'bg-indigo-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                            } ${
                                                !link.url
                                                    ? 'cursor-not-allowed opacity-50'
                                                    : ''
                                            }`}
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}