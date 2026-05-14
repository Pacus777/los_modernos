import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

function fechaParaInput(val) {
    if (!val) {
        return '';
    }
    return String(val).split('T')[0];
}

/**
 * Formulario crear / editar campaña. Recibe emprendedores como prop desde el controller.
 */
export default function Form({ modo, campana, emprendedores = [] }) {
    const esEdicion = modo === 'editar';

    const { data, setData, post, put, processing, errors } = useForm({
        emprendedor_id: campana?.emprendedor_id ?? '',
        titulo: campana?.titulo ?? '',
        meta_apoyo: campana?.meta_apoyo ?? '',
        fecha_inicio: fechaParaInput(campana?.fecha_inicio),
        fecha_fin: fechaParaInput(campana?.fecha_fin),
        estado: campana?.estado ?? 'activa',
    });

    const submit = (e) => {
        e.preventDefault();
        if (esEdicion) {
            put(route('admin.campanas.update', campana.id), {
                preserveScroll: true,
            });
            return;
        }
        post(route('admin.campanas.store'), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {esEdicion ? 'Editar campaña' : 'Nueva campaña'}
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Asociá la campaña a un emprendedor y definí meta y fechas.
                    </p>
                </div>
            }
        >
            <Head title={esEdicion ? 'Editar campaña' : 'Nueva campaña'} />

            <div className="py-8">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link
                            href={route('admin.campanas.index')}
                            className="text-sm font-medium text-wayna-700 hover:text-wayna-900"
                        >
                            ← Volver al listado
                        </Link>
                    </div>

                    <form
                        onSubmit={submit}
                        className="overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
                    >
                        <div className="space-y-6">
                            <div>
                                <label
                                    htmlFor="emprendedor_id"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Emprendedor
                                </label>
                                <select
                                    id="emprendedor_id"
                                    value={data.emprendedor_id}
                                    onChange={(e) =>
                                        setData('emprendedor_id', e.target.value)
                                    }
                                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                    required
                                >
                                    <option value="">Seleccionar…</option>
                                    {emprendedores.map((opt) => (
                                        <option key={opt.id} value={opt.id}>
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                                {errors.emprendedor_id && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.emprendedor_id}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="titulo"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Título
                                </label>
                                <input
                                    id="titulo"
                                    type="text"
                                    value={data.titulo}
                                    onChange={(e) => setData('titulo', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                    required
                                />
                                {errors.titulo && (
                                    <p className="mt-1 text-sm text-red-600">{errors.titulo}</p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="meta_apoyo"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Meta de apoyo (Bs)
                                </label>
                                <input
                                    id="meta_apoyo"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    value={data.meta_apoyo}
                                    onChange={(e) => setData('meta_apoyo', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                    required
                                />
                                {errors.meta_apoyo && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.meta_apoyo}
                                    </p>
                                )}
                            </div>

                            {esEdicion && campana && (
                                <div className="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                    <span className="font-medium">Monto recaudado (validado):</span>{' '}
                                    Bs {Number(campana.monto_recaudado).toFixed(2)} — se actualiza
                                    automáticamente con las donaciones.
                                </div>
                            )}

                            <div className="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label
                                        htmlFor="fecha_inicio"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Fecha inicio
                                    </label>
                                    <input
                                        id="fecha_inicio"
                                        type="date"
                                        value={data.fecha_inicio}
                                        onChange={(e) =>
                                            setData('fecha_inicio', e.target.value)
                                        }
                                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                    />
                                    {errors.fecha_inicio && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.fecha_inicio}
                                        </p>
                                    )}
                                </div>
                                <div>
                                    <label
                                        htmlFor="fecha_fin"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Fecha fin
                                    </label>
                                    <input
                                        id="fecha_fin"
                                        type="date"
                                        value={data.fecha_fin}
                                        onChange={(e) => setData('fecha_fin', e.target.value)}
                                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                    />
                                    {errors.fecha_fin && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.fecha_fin}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label
                                    htmlFor="estado"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Estado
                                </label>
                                <select
                                    id="estado"
                                    value={data.estado}
                                    onChange={(e) => setData('estado', e.target.value)}
                                    className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                >
                                    <option value="activa">Activa</option>
                                    <option value="inactiva">Inactiva</option>
                                    <option value="finalizada">Finalizada</option>
                                </select>
                                {errors.estado && (
                                    <p className="mt-1 text-sm text-red-600">{errors.estado}</p>
                                )}
                            </div>
                        </div>

                        <div className="mt-8 flex justify-end gap-3 border-t border-gray-200 pt-6">
                            <Link
                                href={route('admin.campanas.index')}
                                className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-lg bg-wayna-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-wayna-700 disabled:opacity-50"
                            >
                                {processing ? 'Guardando…' : esEdicion ? 'Guardar cambios' : 'Crear campaña'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
