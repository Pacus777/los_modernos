import CajeroLayout from '@/Layouts/CajeroLayout';
import { Head, Link, useForm } from '@inertiajs/react';

/**
 * Confirmación de donación en efectivo (URL del QR, T-39 paso 4).
 */
export default function ConfirmarEfectivo({ donacion }) {
    const form = useForm({});

    const enviar = (e) => {
        e.preventDefault();
        form.post(route('cajero.efectivo.confirmar.store', donacion.id), {
            preserveScroll: true,
        });
    };

    return (
        <CajeroLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-wayna-950">
                    Confirmar efectivo
                </h2>
            }
        >
            <Head title="Confirmar pago en efectivo — WAYNA" />

            <div className="mx-auto max-w-lg">
                <div className="overflow-hidden rounded-xl border border-wayna-100 bg-white shadow-sm shadow-wayna-900/5">
                    <div className="border-b border-wayna-100 bg-wayna-50/50 px-5 py-4">
                        <p className="text-sm text-stone-600">
                            Verificá los datos y confirmá solo si recibiste el
                            efectivo en caja.
                        </p>
                    </div>
                    <dl className="divide-y divide-wayna-100 px-5 py-3 text-sm">
                        <div className="flex justify-between gap-4 py-2">
                            <dt className="text-stone-500">Donación</dt>
                            <dd className="font-mono font-semibold text-wayna-950">
                                #{donacion.id}
                            </dd>
                        </div>
                        <div className="flex justify-between gap-4 py-2">
                            <dt className="text-stone-500">Monto</dt>
                            <dd className="font-semibold text-wayna-950">
                                Bs {Number(donacion.monto).toFixed(2)}
                            </dd>
                        </div>
                        <div className="flex justify-between gap-4 py-2">
                            <dt className="text-stone-500">Referencia</dt>
                            <dd className="break-all text-right text-stone-800">
                                {donacion.referencia_pago ?? '—'}
                            </dd>
                        </div>
                        <div className="flex justify-between gap-4 py-2">
                            <dt className="text-stone-500">Campaña</dt>
                            <dd className="text-right text-stone-800">
                                {donacion.campana?.titulo ?? '—'}
                            </dd>
                        </div>
                        <div className="flex justify-between gap-4 py-2">
                            <dt className="text-stone-500">Emprendedor</dt>
                            <dd className="text-right text-stone-800">
                                {donacion.campana?.emprendedor?.nombre ?? '—'}
                            </dd>
                        </div>
                    </dl>
                    <form
                        onSubmit={enviar}
                        className="flex flex-col gap-3 border-t border-wayna-100 bg-wayna-50/30 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <Link
                            href={route('cajero.efectivo.pendientes')}
                            className="text-center text-sm font-semibold text-wayna-700 hover:text-wayna-900 sm:text-left"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-60"
                        >
                            {form.processing
                                ? 'Confirmando…'
                                : 'Confirmar recepción en caja'}
                        </button>
                    </form>
                </div>
            </div>
        </CajeroLayout>
    );
}
