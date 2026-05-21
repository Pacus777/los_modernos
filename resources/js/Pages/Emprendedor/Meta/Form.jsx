import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import {
    adminFormFooterPrimaryBtn,
    adminFormFooterSecondaryBtn,
    adminFormStack,
    adminInputClass,
    adminInputMoneyWrap,
    adminLabelUpper,
    adminSectionCard,
} from '@/Components/Admin/adminUi';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import { fechaLocalHoy } from '@/utils/campanaVigente';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

function fechaParaInput(val) {
    if (!val) {
        return '';
    }
    return String(val).split('T')[0];
}

/**
 * E-09 — Crear o editar la meta de apoyo (campaña activa del emprendedor).
 */
export default function Form({ modo, campana, fechaHoy: fechaHoyProp }) {
    const { flash } = usePage().props;
    const esEdicion = modo === 'editar';
    const hoy = fechaHoyProp || fechaLocalHoy();

    const { data, setData, post, put, processing, errors } = useForm({
        titulo: campana?.titulo ?? '',
        meta_apoyo: campana?.meta_apoyo ?? '',
        fecha_inicio: fechaParaInput(campana?.fecha_inicio) || hoy,
        fecha_fin: fechaParaInput(campana?.fecha_fin) || '',
    });

    const enviar = (e) => {
        e.preventDefault();

        if (esEdicion) {
            put(route('emprendedor.meta.update', campana.id));
            return;
        }

        post(route('emprendedor.meta.store'));
    };

    return (
        <EmprendedorLayout
            contentClassName="mx-auto max-w-2xl px-4 py-6 sm:px-6 lg:px-8"
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Meta de apoyo
                    </p>
                    <h2 className="mt-1 text-2xl font-bold text-wayna-950">
                        {esEdicion ? 'Editar tu meta' : 'Crear tu meta de apoyo'}
                    </h2>
                    <p className="mt-1 text-sm text-stone-600">
                        Definí cuánto querés recaudar y en qué fechas. Los turistas verán el progreso en tu
                        perfil público.
                    </p>
                </div>
            }
        >
            <Head title={esEdicion ? 'Editar meta — WAYNA' : 'Crear meta — WAYNA'} />

            <AdminFlashSuccess message={flash?.success} />
            {flash?.error ? (
                <p className="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                    {flash.error}
                </p>
            ) : null}

            <form onSubmit={enviar} className={adminSectionCard}>
                <div className={adminFormStack}>
                    <div>
                        <label htmlFor="titulo" className={adminLabelUpper}>
                            Título de la campaña
                        </label>
                        <input
                            id="titulo"
                            type="text"
                            className={`${adminInputClass} mt-1 w-full`}
                            value={data.titulo}
                            onChange={(e) => setData('titulo', e.target.value)}
                            placeholder="Ej. Ampliar mi taller de tejido"
                            maxLength={255}
                        />
                        {errors.titulo ? (
                            <p className="mt-1 text-sm text-red-600">{errors.titulo}</p>
                        ) : null}
                    </div>

                    <div>
                        <label htmlFor="meta_apoyo" className={adminLabelUpper}>
                            Monto a recaudar (Bs)
                        </label>
                        <div className={`${adminInputMoneyWrap} mt-1`}>
                            <span className="text-sm font-bold text-wayna-700">Bs</span>
                            <input
                                id="meta_apoyo"
                                type="number"
                                min="0.01"
                                step="0.01"
                                className={adminInputClass}
                                value={data.meta_apoyo}
                                onChange={(e) => setData('meta_apoyo', e.target.value)}
                                placeholder="5000"
                            />
                        </div>
                        {errors.meta_apoyo ? (
                            <p className="mt-1 text-sm text-red-600">{errors.meta_apoyo}</p>
                        ) : null}
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label htmlFor="fecha_inicio" className={adminLabelUpper}>
                                Fecha de inicio
                            </label>
                            <input
                                id="fecha_inicio"
                                type="date"
                                className={`${adminInputClass} mt-1 w-full`}
                                value={data.fecha_inicio}
                                onChange={(e) => setData('fecha_inicio', e.target.value)}
                            />
                            {errors.fecha_inicio ? (
                                <p className="mt-1 text-sm text-red-600">{errors.fecha_inicio}</p>
                            ) : null}
                        </div>
                        <div>
                            <label htmlFor="fecha_fin" className={adminLabelUpper}>
                                Fecha de fin
                            </label>
                            <input
                                id="fecha_fin"
                                type="date"
                                className={`${adminInputClass} mt-1 w-full`}
                                value={data.fecha_fin}
                                min={data.fecha_inicio || undefined}
                                onChange={(e) => setData('fecha_fin', e.target.value)}
                            />
                            {errors.fecha_fin ? (
                                <p className="mt-1 text-sm text-red-600">{errors.fecha_fin}</p>
                            ) : null}
                        </div>
                    </div>

                    {errors.estado ? (
                        <p className="text-sm text-red-600">{errors.estado}</p>
                    ) : null}

                    <p className="rounded-xl border border-wayna-100 bg-wayna-50/60 px-4 py-3 text-xs text-stone-600">
                        Solo podés tener <strong>una meta activa</strong> a la vez. Al cerrarla, el historial de
                        aportes se conserva y podés crear una nueva más adelante.
                    </p>
                </div>

                <div className="mt-8 flex flex-wrap gap-3 border-t border-wayna-100 pt-6">
                    <button
                        type="submit"
                        disabled={processing}
                        className={adminFormFooterPrimaryBtn}
                    >
                        {processing
                            ? 'Guardando…'
                            : esEdicion
                              ? 'Guardar cambios'
                              : 'Activar mi meta'}
                    </button>
                    <Link href={route('emprendedor.mis-metas.index')} className={adminFormFooterSecondaryBtn}>
                        Volver a mis metas
                    </Link>
                </div>
            </form>
        </EmprendedorLayout>
    );
}
