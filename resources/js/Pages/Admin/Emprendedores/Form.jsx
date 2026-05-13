import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

/**
 * Formulario reutilizable para crear y editar emprendedores.
 *
 * Esta página recibe desde Laravel:
 * - modo: "crear" o "editar"
 * - emprendedor: null cuando se crea, objeto cuando se edita
 *
 * No se usa useEffect ni fetch porque los datos llegan como props de Inertia.
 */
export default function Form({ modo, emprendedor }) {
    /*
    |--------------------------------------------------------------------------
    | Determinar si estamos creando o editando
    |--------------------------------------------------------------------------
    |
    | Usamos esta variable para cambiar:
    | - título de la página
    | - texto del botón
    | - ruta de envío
    | - método HTTP usado por Inertia
    |
    */

    const esEdicion = modo === 'editar';

    /*
    |--------------------------------------------------------------------------
    | Estado del formulario con useForm
    |--------------------------------------------------------------------------
    |
    | useForm maneja:
    | - datos del formulario
    | - errores de validación enviados por Laravel
    | - estado de carga del botón
    | - envío de datos con Inertia
    |
    */

    const { data, setData, post, processing, errors, reset } = useForm({
        nombre: emprendedor?.nombre || '',
        apellidos: emprendedor?.apellidos || '',
        descripcion: emprendedor?.descripcion || '',
        estado: emprendedor?.estado || 'activo',
        meta_monto: emprendedor?.meta_monto || '',
        fotografia: null,
    });

    /*
    |--------------------------------------------------------------------------
    | Enviar formulario
    |--------------------------------------------------------------------------
    |
    | Crear:
    | POST /admin/emprendedores
    |
    | Editar:
    | POST /admin/emprendedores/{id} con _method = PUT
    |
    | Usamos forceFormData porque el formulario puede incluir imagen.
    |
    */

    const submit = (e) => {
        e.preventDefault();

        if (esEdicion) {
            router.post(
                route('admin.emprendedores.update', emprendedor.id),
                {
                    ...data,
                    _method: 'put',
                },
                {
                    forceFormData: true,
                    preserveScroll: true,
                }
            );

            return;
        }

        post(route('admin.emprendedores.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset('fotografia');
            },
        });
    };

    /*
    |--------------------------------------------------------------------------
    | Vista previa de fotografía actual
    |--------------------------------------------------------------------------
    |
    | Si estamos editando y el emprendedor ya tiene fotografía guardada,
    | la mostramos desde /storage.
    |
    | Recuerda que para esto debe existir:
    | php artisan storage:link
    |
    */

    const fotografiaActual = emprendedor?.fotografia
        ? `/storage/${emprendedor.fotografia}`
        : null;

    return (
        <AdminLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {esEdicion ? 'Editar emprendedor' : 'Nuevo emprendedor'}
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        {esEdicion
                            ? 'Actualiza los datos del emprendedor seleccionado.'
                            : 'Registra un nuevo emprendedor dentro del sistema WAYNA.'}
                    </p>
                </div>
            }
        >
            <Head title={esEdicion ? 'Editar emprendedor' : 'Nuevo emprendedor'} />

            <div className="py-8">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-xl bg-white shadow-sm">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-base font-semibold text-gray-900">
                                Datos del emprendedor
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Completa la información básica que será usada en el panel administrativo
                                y en el perfil público.
                            </p>
                        </div>

                        <form
                            onSubmit={submit}
                            encType="multipart/form-data"
                            className="space-y-6 p-6"
                        >
                            <div className="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label
                                        htmlFor="nombre"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Nombre
                                    </label>

                                    <input
                                        id="nombre"
                                        type="text"
                                        value={data.nombre}
                                        onChange={(e) => setData('nombre', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                        placeholder="Ejemplo: Juan"
                                    />

                                    {errors.nombre && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.nombre}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label
                                        htmlFor="apellidos"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Apellidos
                                    </label>

                                    <input
                                        id="apellidos"
                                        type="text"
                                        value={data.apellidos}
                                        onChange={(e) => setData('apellidos', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                        placeholder="Ejemplo: Pérez Mamani"
                                    />

                                    {errors.apellidos && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.apellidos}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label
                                    htmlFor="descripcion"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Descripción
                                </label>

                                <textarea
                                    id="descripcion"
                                    rows="4"
                                    value={data.descripcion}
                                    onChange={(e) => setData('descripcion', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                    placeholder="Describe brevemente la historia o actividad del emprendedor."
                                />

                                {errors.descripcion && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.descripcion}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-6 md:grid-cols-2">
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
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                    >
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>

                                    {errors.estado && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.estado}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label
                                        htmlFor="meta_monto"
                                        className="block text-sm font-medium text-gray-700"
                                    >
                                        Meta económica Bs.
                                    </label>

                                    <input
                                        id="meta_monto"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.meta_monto}
                                        onChange={(e) => setData('meta_monto', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                                        placeholder="Ejemplo: 500"
                                    />

                                    {errors.meta_monto && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {errors.meta_monto}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label
                                    htmlFor="fotografia"
                                    className="block text-sm font-medium text-gray-700"
                                >
                                    Fotografía
                                </label>

                                {fotografiaActual && (
                                    <div className="mb-3 mt-2">
                                        <p className="mb-2 text-sm text-gray-500">
                                            Fotografía actual:
                                        </p>
                                        <img
                                            src={fotografiaActual}
                                            alt="Fotografía actual del emprendedor"
                                            className="h-28 w-28 rounded-lg object-cover"
                                        />
                                    </div>
                                )}

                                <input
                                    id="fotografia"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    onChange={(e) => setData('fotografia', e.target.files[0])}
                                    className="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-wayna-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-wayna-700 hover:file:bg-wayna-100"
                                />

                                <p className="mt-1 text-xs text-gray-500">
                                    Formatos permitidos: JPG, JPEG, PNG o WEBP. Tamaño máximo: 2 MB.
                                </p>

                                {errors.fotografia && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.fotografia}
                                    </p>
                                )}
                            </div>

                            {esEdicion && emprendedor?.qr_url && (
                                <div className="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    <p className="text-sm font-medium text-gray-700">
                                        Código QR generado
                                    </p>
                                    <p className="mt-1 text-sm text-gray-500">
                                        El QR se genera automáticamente y no se modifica desde este formulario.
                                    </p>

                                    <a
                                        href={`/storage/${emprendedor.qr_url}`}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="mt-2 inline-block text-sm font-semibold text-wayna-600 hover:text-wayna-800"
                                    >
                                        Ver QR
                                    </a>
                                </div>
                            )}

                            <div className="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                                <Link
                                    href={route('admin.emprendedores.index')}
                                    className="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                >
                                    Cancelar
                                </Link>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-md bg-wayna-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-wayna-700 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    {processing
                                        ? 'Guardando...'
                                        : esEdicion
                                            ? 'Actualizar emprendedor'
                                            : 'Guardar emprendedor'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}