import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';

/**
 * Listado de campañas (admin). Datos desde Laravel vía Inertia.
 */
export default function Index({ campanas }) {
    const { flash } = usePage().props;

    const etiquetaEstado = (estado) => {
        const map = {
            activa: 'bg-green-100 text-green-800',
            inactiva: 'bg-gray-100 text-gray-700',
            finalizada: 'bg-amber-100 text-amber-900',
        };
        return map[estado] || 'bg-gray-100 text-gray-700';
    };

    const eliminarCampaña = (campana) => {
        const tieneDonaciones = (campana.donaciones_count ?? 0) > 0;
        const msg = tieneDonaciones
            ? 'Esta campaña tiene donaciones: se marcará como finalizada (no se borra el historial). ¿Continuar?'
            : `¿Eliminar la campaña "${campana.titulo}"? Solo podés hacerlo si no tiene donaciones registradas.`;

        if (!window.confirm(msg)) {
            return;
        }

        router.delete(route('admin.campanas.destroy', campana.id), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            Campañas
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Gestión de campañas de apoyo vinculadas a emprendedores WAYNA.
                        </p>
                    </div>

                    <Link
                        href={route('admin.campanas.create')}
                        className="rounded-lg bg-wayna-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-wayna-700"
                    >
                        Nueva campaña
                    </Link>
                </div>
            }
        >
            <Head title="Campañas" />

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
                                Lista de campañas
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Solo puede haber una campaña activa por emprendedor a la vez.
                            </p>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Título
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Emprendedor
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Meta Bs
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Recaudado Bs
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Estado
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {campanas.data.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan="6"
                                                className="px-6 py-8 text-center text-sm text-gray-500"
                                            >
                                                No hay campañas registradas todavía.
                                            </td>
                                        </tr>
                                    )}

                                    {campanas.data.map((c) => (
                                        <tr key={c.id} className="hover:bg-gray-50">
                                            <td className="max-w-xs px-6 py-4">
                                                <div className="truncate text-sm font-semibold text-gray-900">
                                                    {c.titulo}
                                                </div>
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {c.emprendedor
                                                    ? `${c.emprendedor.nombre} ${c.emprendedor.apellidos}`
                                                    : '—'}
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {Number(c.meta_apoyo).toFixed(2)}
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {Number(c.monto_recaudado).toFixed(2)}
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${etiquetaEstado(c.estado)}`}
                                                >
                                                    {c.estado}
                                                </span>
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                                <div className="flex justify-end gap-2">
                                                    <Link
                                                        href={route('admin.campanas.edit', c.id)}
                                                        className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Editar
                                                    </Link>
                                                    <button
                                                        type="button"
                                                        onClick={() => eliminarCampaña(c)}
                                                        className="rounded-md border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50"
                                                    >
                                                        Eliminar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {campanas.links && campanas.links.length > 3 && (
                            <div className="border-t border-gray-200 px-6 py-4">
                                <div className="flex flex-wrap gap-2">
                                    {campanas.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            preserveScroll
                                            className={`rounded-md px-3 py-1.5 text-sm ${
                                                link.active
                                                    ? 'bg-wayna-600 text-white'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                            } ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
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
        </AdminLayout>
    );
}
