import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import {
    adminFormFooterPrimaryBtn,
    adminFormFooterSecondaryBtn,
    adminListCardOuter,
    adminPrimaryGradientBtn,
} from '@/Components/Admin/adminUi';
import BarraProgreso from '@/Components/Turista/BarraProgreso';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import { campanaVigentePorFechas } from '@/utils/campanaVigente';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useConfirmDialog } from '@/hooks/useConfirmDialog';

const ETIQUETA_ESTADO = {
    activa: 'Activa',
    inactiva: 'Inactiva',
    finalizada: 'Finalizada',
};

function badgeEstado(estado, visibleEnPerfil) {
    const base = 'inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide';
    if (estado === 'activa' && visibleEnPerfil) {
        return `${base} bg-emerald-100 text-emerald-800`;
    }
    if (estado === 'activa') {
        return `${base} bg-amber-100 text-amber-900`;
    }
    if (estado === 'finalizada') {
        return `${base} bg-stone-200 text-stone-700`;
    }
    return `${base} bg-stone-100 text-stone-600`;
}

function textoEstado(campana) {
    if (campana.estado === 'activa' && campana.visible_en_perfil) {
        return 'Visible en tu perfil';
    }
    if (campana.estado === 'activa' && !campanaVigentePorFechas(campana)) {
        return 'Activa (fuera de fechas)';
    }
    return ETIQUETA_ESTADO[campana.estado] ?? campana.estado;
}

function formatearFecha(iso) {
    if (!iso) {
        return '—';
    }
    return new Date(iso + 'T12:00:00').toLocaleDateString('es-BO', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

/**
 * E-08 — Gestión de metas propias (/emprendedor/mis-metas).
 */
export default function Index({ gestion = {} }) {
    const { flash } = usePage().props;
    const { requestConfirm, ConfirmDialogPortal } = useConfirmDialog();
    const campanas = gestion.campanas ?? [];
    const puedeCrear = gestion.puede_crear ?? true;
    const activa = campanas.find((c) => c.es_activa) ?? null;

    return (
        <EmprendedorLayout
            contentClassName="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8"
            header={
                <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                            Metas de apoyo
                        </p>
                        <h2 className="mt-1 text-2xl font-bold text-wayna-950">Mis metas</h2>
                        <p className="mt-1 text-sm text-stone-600">
                            Gestioná tu campaña activa y revisá el historial de metas anteriores.
                        </p>
                    </div>
                    {puedeCrear ? (
                        <Link href={route('emprendedor.meta.create')} className={adminPrimaryGradientBtn}>
                            Crear meta
                        </Link>
                    ) : null}
                </div>
            }
        >
            <Head title="Mis metas — WAYNA" />

            <AdminFlashSuccess message={flash?.success} />
            {flash?.error ? (
                <p className="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                    {flash.error}
                </p>
            ) : null}

            {activa ? (
                <section className="mb-6 rounded-2xl border border-wayna-200 bg-gradient-to-br from-wayna-50/80 to-white p-5 shadow-sm">
                    <div className="mb-4 flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                                Meta activa
                            </p>
                            <h3 className="mt-1 text-lg font-black text-wayna-950">{activa.titulo}</h3>
                            <p className="mt-1 text-xs text-stone-600">
                                {formatearFecha(activa.fecha_inicio)} — {formatearFecha(activa.fecha_fin)}
                            </p>
                        </div>
                        <span className={badgeEstado(activa.estado, activa.visible_en_perfil)}>
                            {textoEstado(activa)}
                        </span>
                    </div>
                    <BarraProgreso
                        porcentaje={activa.porcentaje ?? 0}
                        montoRecaudado={activa.monto_recaudado ?? 0}
                        meta={activa.meta_apoyo ?? 0}
                        titulo={activa.titulo}
                    />
                    <div className="mt-5 flex flex-wrap gap-3">
                        <Link href={route('emprendedor.meta.edit')} className={adminFormFooterPrimaryBtn}>
                            Editar meta
                        </Link>
                        <button
                            type="button"
                            className={adminFormFooterSecondaryBtn}
                            onClick={() =>
                                requestConfirm({
                                    title: '¿Cerrar tu meta?',
                                    message:
                                        'Dejará de mostrarse en tu perfil público. Las donaciones ya registradas se conservan.',
                                    confirmLabel: 'Sí, cerrar meta',
                                    onConfirm: () =>
                                        router.post(route('emprendedor.meta.close', activa.id)),
                                })
                            }
                        >
                            Cerrar meta
                        </button>
                    </div>
                </section>
            ) : (
                <div className="mb-6 rounded-2xl border border-wayna-200 bg-wayna-50/50 px-5 py-5 text-center">
                    <p className="text-sm text-stone-700">
                        No tenés una meta activa. Creá una para que los turistas vean tu progreso en el
                        perfil público.
                    </p>
                    <Link
                        href={route('emprendedor.meta.create')}
                        className={`mt-4 ${adminPrimaryGradientBtn}`}
                    >
                        Crear mi meta de apoyo
                    </Link>
                </div>
            )}

            <section className={adminListCardOuter}>
                <header className="border-b border-wayna-100 px-5 py-4">
                    <h3 className="text-sm font-bold text-wayna-950">Todas tus metas</h3>
                    <p className="mt-0.5 text-xs text-stone-500">
                        {campanas.length === 0
                            ? 'Cuando crees una campaña aparecerá aquí.'
                            : `${campanas.length} campaña(s) en total`}
                    </p>
                </header>

                {campanas.length === 0 ? (
                    <p className="px-5 py-8 text-center text-sm text-stone-500">
                        Todavía no registraste ninguna meta de apoyo.
                    </p>
                ) : (
                    <ul className="divide-y divide-stone-100">
                        {campanas.map((campana) => (
                            <li
                                key={campana.id}
                                className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div className="min-w-0 flex-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <p className="font-bold text-wayna-950">{campana.titulo}</p>
                                        <span
                                            className={badgeEstado(
                                                campana.estado,
                                                campana.visible_en_perfil,
                                            )}
                                        >
                                            {textoEstado(campana)}
                                        </span>
                                    </div>
                                    <p className="mt-1 text-xs text-stone-500">
                                        Meta Bs {Number(campana.meta_apoyo).toFixed(2)} · Recaudado Bs{' '}
                                        {Number(campana.monto_recaudado).toFixed(2)} (
                                        {Number(campana.porcentaje).toFixed(0)}%)
                                    </p>
                                    <p className="mt-0.5 text-xs text-stone-400">
                                        {formatearFecha(campana.fecha_inicio)} —{' '}
                                        {formatearFecha(campana.fecha_fin)}
                                    </p>
                                </div>
                                {campana.es_activa ? (
                                    <Link
                                        href={route('emprendedor.meta.edit')}
                                        className="shrink-0 text-sm font-bold text-wayna-700 underline underline-offset-2 hover:text-wayna-900"
                                    >
                                        Editar
                                    </Link>
                                ) : null}
                            </li>
                        ))}
                    </ul>
                )}
            </section>

            <ConfirmDialogPortal />
        </EmprendedorLayout>
    );
}
