import AdminBackLink from '@/Components/Admin/AdminBackLink';
import {
    adminBackdropTall,
    adminFileInputClass,
    adminFormFooterPrimaryBtn,
    adminFormFooterSecondaryBtn,
    adminInputClass,
    adminLabelField,
    adminLabelUpper,
    adminSectionCard,
    adminTextareaClass,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

/**
 * Formulario crear / editar emprendedor — estilo WAYNA unificado con campañas.
 */
export default function Form({ modo, emprendedor }) {
    const esEdicion = modo === 'editar';

    const { data, setData, post, processing, errors, reset } = useForm({
        nombre: emprendedor?.nombre || '',
        apellidos: emprendedor?.apellidos || '',
        descripcion: emprendedor?.descripcion || '',
        estado: emprendedor?.estado || 'activo',
        meta_monto: emprendedor?.meta_monto || '',
        fotografia: null,
    });

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

    const fotografiaActual = emprendedor?.fotografia
        ? `/storage/${emprendedor.fotografia}`
        : null;

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        {esEdicion ? 'Editar emprendedor' : 'Nuevo emprendedor'}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        {esEdicion
                            ? 'Actualizá datos y foto; el QR de perfil se mantiene salvo que cambie la lógica del sistema.'
                            : 'Registrá un emprendedor nuevo: se generará el QR de perfil al guardar.'}
                    </p>
                </div>
            }
        >
            <Head title={esEdicion ? 'Editar emprendedor — Wayna' : 'Nuevo emprendedor — Wayna'} />

            <div className="relative py-8">
                <div className={adminBackdropTall} aria-hidden />

                <div className="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <AdminBackLink href={route('admin.emprendedores.index')} />

                    <div className="overflow-hidden rounded-3xl border border-wayna-200/90 bg-white shadow-2xl shadow-wayna-900/[0.08] ring-1 ring-black/[0.03]">
                        <div className="bg-gradient-to-r from-wayna-600 via-wayna-500 to-orange-500 px-6 py-6 sm:px-8">
                            <h3 className="text-lg font-bold text-white sm:text-xl">Datos del emprendedor</h3>
                            <p className="mt-1 text-sm text-orange-50/95">
                                Información del panel y del perfil público que ve el turista.
                            </p>
                        </div>

                        <form
                            onSubmit={submit}
                            encType="multipart/form-data"
                            className="space-y-0"
                        >
                            <div className="space-y-8 bg-gradient-to-b from-white to-wayna-50/40 p-6 sm:p-8">
                                <div className={adminSectionCard}>
                                    <p className={adminLabelUpper}>Identidad</p>
                                    <div className="mt-4 grid gap-5 sm:grid-cols-2">
                                        <div>
                                            <label htmlFor="nombre" className={adminLabelField}>
                                                Nombre
                                            </label>
                                            <input
                                                id="nombre"
                                                type="text"
                                                value={data.nombre}
                                                onChange={(e) => setData('nombre', e.target.value)}
                                                className={adminInputClass}
                                                placeholder="Ej. Camila"
                                                required
                                            />
                                            {errors.nombre && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {errors.nombre}
                                                </p>
                                            )}
                                        </div>
                                        <div>
                                            <label htmlFor="apellidos" className={adminLabelField}>
                                                Apellidos
                                            </label>
                                            <input
                                                id="apellidos"
                                                type="text"
                                                value={data.apellidos}
                                                onChange={(e) => setData('apellidos', e.target.value)}
                                                className={adminInputClass}
                                                placeholder="Ej. Sánchez López"
                                                required
                                            />
                                            {errors.apellidos && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {errors.apellidos}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="mt-5">
                                        <label htmlFor="descripcion" className={adminLabelField}>
                                            Descripción
                                        </label>
                                        <textarea
                                            id="descripcion"
                                            rows={4}
                                            value={data.descripcion}
                                            onChange={(e) => setData('descripcion', e.target.value)}
                                            className={adminTextareaClass}
                                            placeholder="Historia o actividad que verá el turista en Wayna."
                                        />
                                        {errors.descripcion && (
                                            <p className="mt-2 text-sm font-medium text-red-600">
                                                {errors.descripcion}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className={adminSectionCard}>
                                    <p className={adminLabelUpper}>Estado y meta</p>
                                    <div className="mt-4 grid gap-5 sm:grid-cols-2">
                                        <div>
                                            <label htmlFor="estado" className={adminLabelField}>
                                                Estado
                                            </label>
                                            <select
                                                id="estado"
                                                value={data.estado}
                                                onChange={(e) => setData('estado', e.target.value)}
                                                className={adminInputClass}
                                            >
                                                <option value="activo">Activo (visible)</option>
                                                <option value="inactivo">Inactivo</option>
                                            </select>
                                            {errors.estado && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {errors.estado}
                                                </p>
                                            )}
                                        </div>
                                        <div>
                                            <label htmlFor="meta_monto" className={adminLabelField}>
                                                Meta económica referencial (Bs)
                                            </label>
                                            <div className="relative mt-2">
                                                <span className="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-wayna-600">
                                                    Bs
                                                </span>
                                                <input
                                                    id="meta_monto"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    value={data.meta_monto}
                                                    onChange={(e) => setData('meta_monto', e.target.value)}
                                                    className={`${adminInputClass} pl-11`}
                                                    placeholder="0.00"
                                                    required
                                                />
                                            </div>
                                            {errors.meta_monto && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {errors.meta_monto}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className={adminSectionCard}>
                                    <p className={adminLabelUpper}>Fotografía</p>
                                    <div className="mt-4">
                                        {fotografiaActual && (
                                            <div className="mb-4">
                                                <p className="mb-2 text-xs font-bold uppercase tracking-wide text-wayna-800">
                                                    Vista actual
                                                </p>
                                                <img
                                                    src={fotografiaActual}
                                                    alt=""
                                                    className="h-32 w-32 rounded-2xl object-cover ring-2 ring-wayna-100 shadow-md"
                                                />
                                            </div>
                                        )}
                                        <label htmlFor="fotografia" className={adminLabelField}>
                                            Archivo (opcional al editar)
                                        </label>
                                        <input
                                            id="fotografia"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            onChange={(e) => setData('fotografia', e.target.files[0])}
                                            className={adminFileInputClass}
                                        />
                                        <p className="mt-2 text-xs text-stone-500">
                                            JPG, PNG o WEBP. Máximo 2 MB.
                                        </p>
                                        {errors.fotografia && (
                                            <p className="mt-2 text-sm font-medium text-red-600">
                                                {errors.fotografia}
                                            </p>
                                        )}
                                    </div>
                                </div>

                                {esEdicion && emprendedor?.qr_url && (
                                    <div className="rounded-2xl border border-wayna-200 bg-gradient-to-r from-wayna-50 to-orange-50/60 px-5 py-4">
                                        <p className="text-xs font-bold uppercase tracking-wide text-wayna-800">
                                            Código QR de perfil
                                        </p>
                                        <p className="mt-1 text-sm text-stone-600">
                                            Generado al crear el emprendedor; no se edita desde acá.
                                        </p>
                                        <a
                                            href={`/storage/${emprendedor.qr_url}`}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="mt-2 inline-flex text-sm font-bold text-wayna-700 underline decoration-wayna-300 hover:text-wayna-900"
                                        >
                                            Abrir QR
                                        </a>
                                    </div>
                                )}
                            </div>

                            <div className="flex flex-col-reverse gap-3 border-t border-wayna-100 bg-white/95 px-6 py-5 sm:flex-row sm:justify-end sm:px-8">
                                <Link
                                    href={route('admin.emprendedores.index')}
                                    className={adminFormFooterSecondaryBtn}
                                >
                                    Cancelar
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className={adminFormFooterPrimaryBtn}
                                >
                                    {processing
                                        ? 'Guardando…'
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
