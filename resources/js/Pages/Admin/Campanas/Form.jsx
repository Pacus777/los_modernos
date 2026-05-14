import AdminBackLink from '@/Components/Admin/AdminBackLink';
import {
    adminBackdropTall,
    adminFormFooterPrimaryBtn,
    adminFormFooterSecondaryBtn,
    adminInputClass,
    adminLabelField,
    adminLabelUpper,
    adminSectionCard,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

function fechaParaInput(val) {
    if (!val) {
        return '';
    }
    return String(val).split('T')[0];
}

/**
 * Formulario crear / editar campaña — estilo WAYNA (T-32 / PB-13).
 * Emprendedores llegan como prop desde el controller (sin fetch extra).
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
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        {esEdicion ? 'Editar campaña' : 'Nueva campaña'}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        {esEdicion
                            ? 'Actualizá la meta, fechas o estado. El monto recaudado lo calcula el sistema con las donaciones validadas.'
                            : 'Definí la meta visible para turistas y vinculá la campaña al emprendedor correcto.'}
                    </p>
                </div>
            }
        >
            <Head title={esEdicion ? 'Editar campaña — Wayna' : 'Nueva campaña — Wayna'} />

            <div className="relative py-8">
                <div className={adminBackdropTall} aria-hidden />

                <div className="relative mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <AdminBackLink href={route('admin.campanas.index')} />

                    <div className="overflow-hidden rounded-3xl border border-wayna-200/90 bg-white shadow-2xl shadow-wayna-900/[0.08] ring-1 ring-black/[0.03]">
                        <div className="bg-gradient-to-r from-wayna-600 via-wayna-500 to-orange-500 px-6 py-6 sm:px-8">
                            <h3 className="text-lg font-bold text-white sm:text-xl">
                                {esEdicion ? 'Datos de la campaña' : 'Alta de campaña'}
                            </h3>
                            <p className="mt-1 text-sm text-orange-50/95">
                                Completá los campos. El selector de emprendedor usa la lista que envió el servidor.
                            </p>
                        </div>

                        <form onSubmit={submit} className="space-y-0">
                            <div className="space-y-8 bg-gradient-to-b from-white to-wayna-50/40 p-6 sm:p-8">
                                <div className={adminSectionCard}>
                                    <p className={adminLabelUpper}>Vinculación</p>
                                    <div className="mt-4 space-y-5">
                                        <div>
                                            <label htmlFor="emprendedor_id" className={adminLabelField}>
                                                Emprendedor
                                            </label>
                                            <select
                                                id="emprendedor_id"
                                                value={data.emprendedor_id}
                                                onChange={(e) =>
                                                    setData('emprendedor_id', e.target.value)
                                                }
                                                className={adminInputClass}
                                                required
                                            >
                                                <option value="">Seleccionar emprendedor…</option>
                                                {emprendedores.map((opt) => (
                                                    <option key={opt.id} value={opt.id}>
                                                        {opt.label}
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.emprendedor_id && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {errors.emprendedor_id}
                                                </p>
                                            )}
                                        </div>

                                        <div className="rounded-xl border border-wayna-200/80 bg-gradient-to-r from-wayna-50 to-orange-50/60 px-4 py-3 text-sm leading-relaxed text-wayna-950">
                                            <span className="font-bold text-wayna-800">Regla Wayna:</span> solo
                                            puede haber{' '}
                                            <span className="font-semibold">una campaña activa</span> por
                                            emprendedor. Si ya existe una, finalizala o desactivala antes de activar
                                            otra.
                                        </div>
                                    </div>
                                </div>

                                <div className={adminSectionCard}>
                                    <p className={adminLabelUpper}>Campaña y meta</p>
                                    <div className="mt-4 space-y-5">
                                        <div>
                                            <label htmlFor="titulo" className={adminLabelField}>
                                                Título público
                                            </label>
                                            <input
                                                id="titulo"
                                                type="text"
                                                value={data.titulo}
                                                onChange={(e) => setData('titulo', e.target.value)}
                                                className={adminInputClass}
                                                placeholder="Ej. Apoyo a artesanías de la Chiquitanía"
                                                required
                                            />
                                            {errors.titulo && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {errors.titulo}
                                                </p>
                                            )}
                                        </div>

                                        <div>
                                            <label htmlFor="meta_apoyo" className={adminLabelField}>
                                                Meta de apoyo (Bs)
                                            </label>
                                            <div className="relative mt-2">
                                                <span className="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-wayna-600">
                                                    Bs
                                                </span>
                                                <input
                                                    id="meta_apoyo"
                                                    type="number"
                                                    min="0.01"
                                                    step="0.01"
                                                    value={data.meta_apoyo}
                                                    onChange={(e) => setData('meta_apoyo', e.target.value)}
                                                    className={`${adminInputClass} pl-11`}
                                                    required
                                                />
                                            </div>
                                            {errors.meta_apoyo && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {errors.meta_apoyo}
                                                </p>
                                            )}
                                        </div>

                                        {esEdicion && campana && (
                                            <div className="flex flex-col gap-2 rounded-xl border border-wayna-200 bg-wayna-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                                <div>
                                                    <p className="text-xs font-bold uppercase tracking-wide text-wayna-800">
                                                        Recaudado validado
                                                    </p>
                                                    <p className="mt-1 text-xs text-stone-600">
                                                        Se actualiza solo con donaciones validadas.
                                                    </p>
                                                </div>
                                                <p className="font-mono text-xl font-bold tabular-nums text-wayna-900">
                                                    {Number(campana.monto_recaudado).toFixed(2)}
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                <div className={adminSectionCard}>
                                    <p className={adminLabelUpper}>Fechas (opcional)</p>
                                    <div className="mt-4 grid gap-5 sm:grid-cols-2">
                                        <div>
                                            <label htmlFor="fecha_inicio" className={adminLabelField}>
                                                Inicio
                                            </label>
                                            <input
                                                id="fecha_inicio"
                                                type="date"
                                                value={data.fecha_inicio}
                                                onChange={(e) =>
                                                    setData('fecha_inicio', e.target.value)
                                                }
                                                className={adminInputClass}
                                            />
                                            {errors.fecha_inicio && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {errors.fecha_inicio}
                                                </p>
                                            )}
                                        </div>
                                        <div>
                                            <label htmlFor="fecha_fin" className={adminLabelField}>
                                                Fin
                                            </label>
                                            <input
                                                id="fecha_fin"
                                                type="date"
                                                value={data.fecha_fin}
                                                onChange={(e) => setData('fecha_fin', e.target.value)}
                                                className={adminInputClass}
                                            />
                                            {errors.fecha_fin && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {errors.fecha_fin}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className={adminSectionCard}>
                                    <p className={adminLabelUpper}>Estado</p>
                                    <div className="mt-4">
                                        <label htmlFor="estado" className={adminLabelField}>
                                            Visibilidad para turistas
                                        </label>
                                        <select
                                            id="estado"
                                            value={data.estado}
                                            onChange={(e) => setData('estado', e.target.value)}
                                            className={adminInputClass}
                                        >
                                            <option value="activa">Activa (visible en perfil público)</option>
                                            <option value="inactiva">Inactiva</option>
                                            <option value="finalizada">Finalizada</option>
                                        </select>
                                        {errors.estado && (
                                            <p className="mt-2 text-sm font-medium text-red-600">{errors.estado}</p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col-reverse gap-3 border-t border-wayna-100 bg-white/95 px-6 py-5 sm:flex-row sm:justify-end sm:px-8">
                                <Link
                                    href={route('admin.campanas.index')}
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
                                            ? 'Guardar cambios'
                                            : 'Crear campaña'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
