import PendienteEfectivoCard from '@/Components/Cajero/PendienteEfectivoCard';
import TableScrollRegion from '@/Components/TableScrollRegion';
import CajeroLayout from '@/Layouts/CajeroLayout';
import { useConfirmDialog } from '@/hooks/useConfirmDialog';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';

/**
 * Pantalla única del cajero.
 *
 * Muestra únicamente donaciones en efectivo pendientes de confirmación.
 * No usa fetch ni useEffect porque los datos llegan desde Laravel
 * como props de Inertia.
 */
export default function Efectivo({ pendientes }) {
    const { flash } = usePage().props;
    const { requestConfirm, ConfirmDialogPortal } = useConfirmDialog();

    /*
    |--------------------------------------------------------------------------
    | Estado local para saber qué botón está procesando
    |--------------------------------------------------------------------------
    |
    | Esto evita que el cajero presione varias veces el mismo botón
    | mientras Inertia procesa la confirmación.
    |
    */

    const [procesandoId, setProcesandoId] = useState(null);

    /**
     * Confirma una donación en efectivo.
     *
     * Usa router.post() porque la ruta definida en Laravel es:
     * POST /cajero/efectivo/{donacion}/confirmar
     */
    const confirmarPago = (donacion) => {
        const emprendedor = obtenerNombreEmprendedor(donacion);

        requestConfirm({
            title: 'Confirmar pago en efectivo',
            message: `¿Confirmás que recibiste Bs. ${formatearMonto(donacion.monto)} en caja para ${emprendedor}? Esta acción registra el pago como validado.`,
            confirmLabel: 'Sí, confirmar pago',
            cancelLabel: 'Cancelar',
            variant: 'success',
            onConfirm: ({ close, setProcessing }) => {
                setProcessing(true);
                setProcesandoId(donacion.id);

                router.post(
                    route('cajero.efectivo.confirmar.store', donacion.id),
                    {},
                    {
                        preserveScroll: true,
                        onFinish: () => {
                            setProcesandoId(null);
                            setProcessing(false);
                            close();
                        },
                    },
                );
            },
        });
    };

    /**
     * Obtiene el nombre del emprendedor asociado a la donación.
     *
     * La relación real es:
     * donacion -> campana -> emprendedor
     */
    const obtenerNombreEmprendedor = (donacion) => {
        const emprendedor = donacion.campana?.emprendedor;

        if (!emprendedor) {
            return 'Emprendedor no identificado';
        }

        return `${emprendedor.nombre} ${emprendedor.apellidos}`;
    };

    /**
     * Formatea montos en bolivianos.
     */
    const formatearMonto = (monto) => {
        return Number(monto || 0).toFixed(2);
    };

    /**
     * Formatea fecha básica.
     */
    const formatearFecha = (fecha) => {
        if (!fecha) {
            return 'Sin fecha';
        }

        return new Date(fecha).toLocaleString('es-BO', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <CajeroLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Confirmación de efectivo
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Donaciones en efectivo pendientes de confirmación física.
                    </p>
                </div>
            }
        >
            <Head title="Confirmar efectivo" />

            <div className="py-8">
                <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div className="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {flash.success}
                        </div>
                    )}

                    {flash?.error && (
                        <div className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {flash.error}
                        </div>
                    )}

                    <div className="overflow-hidden rounded-xl bg-white shadow-sm">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-base font-semibold text-gray-900">
                                Pagos pendientes
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Confirma solo los pagos que ya fueron recibidos físicamente.
                            </p>
                        </div>

                        <div className="space-y-3 p-4 md:hidden">
                            {pendientes.data.length === 0 && (
                                <p className="py-6 text-center text-sm text-gray-500">
                                    No hay pagos en efectivo pendientes.
                                </p>
                            )}
                            {pendientes.data.map((donacion) => (
                                <PendienteEfectivoCard
                                    key={donacion.id}
                                    donacion={donacion}
                                    nombreEmprendedor={obtenerNombreEmprendedor(donacion)}
                                    montoFormateado={formatearMonto(donacion.monto)}
                                    fechaFormateada={formatearFecha(donacion.created_at)}
                                    onConfirmar={confirmarPago}
                                    confirmando={procesandoId === donacion.id}
                                    modo="boton"
                                />
                            ))}
                        </div>

                        <TableScrollRegion className="hidden md:block">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Emprendedor
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Campaña
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Monto
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Referencia
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Fecha
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {pendientes.data.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan="6"
                                                className="px-6 py-10 text-center text-sm text-gray-500"
                                            >
                                                No hay pagos en efectivo pendientes.
                                            </td>
                                        </tr>
                                    )}

                                    {pendientes.data.map((donacion) => (
                                        <tr key={donacion.id} className="hover:bg-gray-50">
                                            <td className="whitespace-nowrap px-6 py-4">
                                                <div className="text-sm font-semibold text-gray-900">
                                                    {obtenerNombreEmprendedor(donacion)}
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    Estado: {donacion.estado_pago}
                                                </div>
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {donacion.campana?.titulo || 'Sin campaña'}
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">
                                                Bs. {formatearMonto(donacion.monto)}
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-sm">
                                                <span className="font-mono text-xs font-bold tracking-wide text-wayna-900">
                                                    {donacion.referencia_pago || 'Sin referencia'}
                                                </span>
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                                {formatearFecha(donacion.created_at)}
                                            </td>

                                            <td className="whitespace-nowrap px-6 py-4 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() => confirmarPago(donacion)}
                                                    disabled={procesandoId === donacion.id}
                                                    className="touch-target min-h-11 rounded-md bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                                                >
                                                    {procesandoId === donacion.id
                                                        ? 'Confirmando...'
                                                        : 'Confirmar pago'}
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </TableScrollRegion>

                        {pendientes.links && pendientes.links.length > 3 && (
                            <div className="border-t border-gray-200 px-6 py-4">
                                <div className="flex flex-wrap gap-2">
                                    {pendientes.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            preserveScroll
                                            className={`rounded-md px-3 py-1.5 text-sm ${
                                                link.active
                                                    ? 'bg-green-600 text-white'
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
            <ConfirmDialogPortal />
        </CajeroLayout>
    );
}