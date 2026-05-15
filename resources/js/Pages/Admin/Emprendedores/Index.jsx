import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import {
    adminBackdropShort,
    adminListCardHeader,
    adminListCardOuter,
    adminPaginationBtnActive,
    adminPaginationBtnIdle,
    adminPrimaryGradientBtn,
    adminTableActionDanger,
    adminTableActionEdit,
    adminTableHeadRow,
    adminTableRowHover,
} from '@/Components/Admin/adminUi';
import EmprendedorDetalleModal from '@/Components/Admin/EmprendedorDetalleModal';
import EmprendedorQrModal from '@/Components/Admin/EmprendedorQrModal';
import AdminLayout from '@/Layouts/AdminLayout';
import { useConfirmDialog } from '@/hooks/useConfirmDialog';
import { etiquetaTipoEmprendimiento } from '@/utils/tipoEmprendimiento';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

function IconoMas({ className }) {
    return (
        <svg className={className} viewBox="0 0 20 20" fill="currentColor" aria-hidden>
            <path d="M10 3.25a.75.75 0 01.75.75v5.25H16a.75.75 0 010 1.5h-5.25V16a.75.75 0 01-1.5 0v-5.25H4a.75.75 0 010-1.5h5.25V4a.75.75 0 01.75-.75z" />
        </svg>
    );
}

function IconoPersonas({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden>
            <path
                stroke="currentColor"
                strokeWidth="1.5"
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm11-1v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
        </svg>
    );
}

/**
 * Listado de emprendedores — panel admin WAYNA (unificado con campañas).
 */
export default function Index({ emprendedores }) {
    const { flash } = usePage().props;
    const { requestConfirm, ConfirmDialogPortal } = useConfirmDialog();
    const [detalle, setDetalle] = useState({ open: false, emprendedor: null });
    const [qrPreview, setQrPreview] = useState({
        open: false,
        emprendedor: null,
        qrSrc: '',
        fotoSrc: null,
    });

    const desactivarEmprendedor = (emprendedor) => {
        requestConfirm({
            title: 'Desactivar emprendedor',
            message: `¿Seguro que deseas desactivar a ${emprendedor.nombre} ${emprendedor.apellidos}? No se borra el historial; solo deja de aparecer como activo.`,
            confirmLabel: 'Sí, desactivar',
            variant: 'danger',
            onConfirm: ({ close }) => {
                close();
                router.delete(route('admin.emprendedores.destroy', emprendedor.id), {
                    preserveScroll: true,
                });
            },
        });
    };

    const abrirDetalle = (emprendedor) => {
        setDetalle({ open: true, emprendedor });
    };

    const cerrarDetalle = () => {
        setDetalle({ open: false, emprendedor: null });
    };

    const verQrEmprendedor = (emprendedor) => {
        if (!emprendedor.qr_url) {
            return;
        }
        setQrPreview({
            open: true,
            emprendedor,
            qrSrc: `/storage/${emprendedor.qr_url}`,
            fotoSrc: obtenerUrlFotografia(emprendedor.fotografia),
        });
    };

    const verQrDesdeDetalle = (emprendedor) => {
        cerrarDetalle();
        verQrEmprendedor(emprendedor);
    };

    const detenerClic = (e) => {
        e.stopPropagation();
    };

    const obtenerUrlFotografia = (fotografia) => {
        if (!fotografia) {
            return null;
        }
        return `/storage/${fotografia}`;
    };

    const estiloEstado = (estado) =>
        estado === 'activo'
            ? 'bg-wayna-100 text-wayna-900 ring-1 ring-wayna-300/80 shadow-sm shadow-wayna-500/10'
            : 'bg-stone-100 text-stone-700 ring-1 ring-stone-200';

    const totalLista = emprendedores?.data?.length ?? 0;

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                            Wayna admin
                        </p>
                        <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                            Emprendedores
                        </h2>
                        <p className="mt-2 max-w-xl text-sm leading-relaxed text-stone-600">
                            Alta, edición y estado de quienes aparecen en el perfil público y en las campañas de
                            apoyo.
                        </p>
                    </div>

                    <Link
                        href={route('admin.emprendedores.create')}
                        className={`group self-start sm:self-auto ${adminPrimaryGradientBtn}`}
                    >
                        <IconoMas className="h-4 w-4 opacity-95" />
                        Nuevo emprendedor
                    </Link>
                </div>
            }
        >
            <Head title="Emprendedores — Wayna" />

            <div className="relative py-8">
                <div className={adminBackdropShort} aria-hidden />

                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <AdminFlashSuccess message={flash?.success} />

                    <div className="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-wayna-200/80 bg-white/90 px-4 py-3 shadow-sm backdrop-blur-sm">
                        <div className="flex items-center gap-2 text-sm text-stone-600">
                            <IconoPersonas className="h-5 w-5 text-wayna-600" />
                            <span>
                                Mostrando{' '}
                                <strong className="font-semibold text-wayna-900">{totalLista}</strong> en esta
                                página
                            </span>
                        </div>
                    </div>

                    <div className={adminListCardOuter}>
                        <div className={adminListCardHeader}>
                            <h3 className="text-lg font-bold text-wayna-950">Directorio</h3>
                            <p className="mt-1 text-sm text-stone-600">
                                Hacé clic en una fila para ver la ficha completa. Editá o desactivá desde las
                                acciones.
                            </p>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-wayna-100">
                                <thead>
                                    <tr className={adminTableHeadRow}>
                                        <th className="px-6 py-4 sm:px-8">Emprendedor</th>
                                        <th className="px-4 py-4 text-right">Meta Bs</th>
                                        <th className="px-4 py-4">Estado</th>
                                        <th className="px-4 py-4">QR</th>
                                        <th className="px-6 py-4 text-right sm:px-8">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-wayna-100 bg-white">
                                    {emprendedores.data.length === 0 && (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-16 text-center sm:px-8">
                                                <div className="mx-auto max-w-md rounded-2xl border border-dashed border-wayna-200 bg-wayna-50/50 px-6 py-10">
                                                    <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-wayna-100 to-surface-muted text-wayna-500">
                                                        <IconoPersonas className="h-7 w-7" />
                                                    </div>
                                                    <p className="text-base font-semibold text-wayna-950">
                                                        Aún no hay emprendedores
                                                    </p>
                                                    <p className="mt-2 text-sm leading-relaxed text-stone-600">
                                                        Creá el primero para generar su QR de perfil y poder asignarle
                                                        campañas.
                                                    </p>
                                                    <Link
                                                        href={route('admin.emprendedores.create')}
                                                        className={`mt-6 inline-flex ${adminPrimaryGradientBtn}`}
                                                    >
                                                        Registrar emprendedor
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    )}

                                    {emprendedores.data.map((emprendedor) => (
                                        <tr
                                            key={emprendedor.id}
                                            role="button"
                                            tabIndex={0}
                                            onClick={() => abrirDetalle(emprendedor)}
                                            onKeyDown={(e) => {
                                                if (e.key === 'Enter' || e.key === ' ') {
                                                    e.preventDefault();
                                                    abrirDetalle(emprendedor);
                                                }
                                            }}
                                            className={`${adminTableRowHover} cursor-pointer`}
                                            aria-label={`Ver ficha de ${emprendedor.nombre} ${emprendedor.apellidos}`}
                                        >
                                            <td className="px-6 py-4 sm:px-8">
                                                <div className="flex items-center gap-3">
                                                    {obtenerUrlFotografia(emprendedor.fotografia) ? (
                                                        <img
                                                            src={obtenerUrlFotografia(emprendedor.fotografia)}
                                                            alt=""
                                                            className="h-12 w-12 rounded-2xl object-cover ring-2 ring-wayna-100 shadow-sm"
                                                        />
                                                    ) : (
                                                        <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-wayna-100 to-surface-muted text-sm font-bold text-wayna-700 ring-2 ring-wayna-100">
                                                            {emprendedor.nombre?.charAt(0)}
                                                        </div>
                                                    )}
                                                    <div className="min-w-0">
                                                        <div className="truncate font-semibold text-wayna-950">
                                                            {emprendedor.nombre} {emprendedor.apellidos}
                                                        </div>
                                                        {etiquetaTipoEmprendimiento(emprendedor.tipo_emprendimiento) ? (
                                                            <span className="mt-0.5 inline-flex rounded-full bg-wayna-50 px-2 py-0.5 text-[10px] font-bold text-wayna-800 ring-1 ring-wayna-200/80">
                                                                {etiquetaTipoEmprendimiento(emprendedor.tipo_emprendimiento)}
                                                            </span>
                                                        ) : null}
                                                        <div className="max-w-xs truncate text-sm text-stone-500">
                                                            {emprendedor.descripcion || 'Sin descripción'}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td className="whitespace-nowrap px-4 py-4 text-right font-mono text-sm font-semibold text-wayna-900">
                                                {Number(emprendedor.meta_monto).toFixed(2)}
                                            </td>

                                            <td className="whitespace-nowrap px-4 py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-3 py-1 text-xs font-bold capitalize ${estiloEstado(emprendedor.estado)}`}
                                                >
                                                    {emprendedor.estado}
                                                </span>
                                            </td>

                                            <td
                                                className="whitespace-nowrap px-4 py-4 text-sm"
                                                onClick={detenerClic}
                                            >
                                                {emprendedor.qr_url ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => verQrEmprendedor(emprendedor)}
                                                        className="font-bold text-wayna-600 underline decoration-wayna-300 decoration-2 underline-offset-2 transition hover:text-wayna-800"
                                                    >
                                                        Ver QR
                                                    </button>
                                                ) : (
                                                    <span className="text-stone-400">Pendiente</span>
                                                )}
                                            </td>

                                            <td
                                                className="whitespace-nowrap px-6 py-4 text-right sm:px-8"
                                                onClick={detenerClic}
                                            >
                                                <div className="flex flex-wrap justify-end gap-2">
                                                    <Link
                                                        href={route('admin.emprendedores.edit', emprendedor.id)}
                                                        className={adminTableActionEdit}
                                                    >
                                                        Editar
                                                    </Link>
                                                    {emprendedor.estado === 'activo' && (
                                                        <button
                                                            type="button"
                                                            onClick={() => desactivarEmprendedor(emprendedor)}
                                                            className={adminTableActionDanger}
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
                            <div className="border-t border-wayna-100 bg-wayna-50/30 px-6 py-4 sm:px-8">
                                <div className="flex flex-wrap gap-2">
                                    {emprendedores.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            preserveScroll
                                            className={`${link.active ? adminPaginationBtnActive : adminPaginationBtnIdle} ${!link.url ? 'pointer-events-none opacity-40' : ''}`}
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
            <ConfirmDialogPortal />
            <EmprendedorDetalleModal
                show={detalle.open}
                emprendedor={detalle.emprendedor}
                fotoSrc={
                    detalle.emprendedor
                        ? obtenerUrlFotografia(detalle.emprendedor.fotografia)
                        : null
                }
                qrSrc={
                    detalle.emprendedor?.qr_url
                        ? `/storage/${detalle.emprendedor.qr_url}`
                        : null
                }
                onVerQr={verQrDesdeDetalle}
                onClose={cerrarDetalle}
            />
            <EmprendedorQrModal
                show={qrPreview.open}
                emprendedor={qrPreview.emprendedor}
                qrSrc={qrPreview.qrSrc}
                fotoSrc={qrPreview.fotoSrc}
                editHref={
                    qrPreview.emprendedor
                        ? route('admin.emprendedores.edit', qrPreview.emprendedor.id)
                        : null
                }
                onClose={() =>
                    setQrPreview({
                        open: false,
                        emprendedor: null,
                        qrSrc: '',
                        fotoSrc: null,
                    })
                }
            />
        </AdminLayout>
    );
}
