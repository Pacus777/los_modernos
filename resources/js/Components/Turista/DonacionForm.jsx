import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { bolivianosAUsd, formatearUsd } from '@/utils/tipoCambioTurista';

export default function DonacionForm({ tipoPagos, montoInicial = 0 }) {
    const { props } = usePage();
    const [data, setData] = useState({
        tipo_pago_id: tipoPagos[0]?.id ?? null,
        metodo: tipoPagos[0]?.codigo ?? tipoPagos[0]?.nombre ?? '',
        monto: montoInicial,
        referencia_pago: '',
    });

    const [tipoPagoSeleccionado, setTipoPagoSeleccionado] = useState(tipoPagos[0] ?? null);
    const [processing, setProcessing] = useState(false);

    // Ejemplo: tipoCambio viene de props del perfil (T-A35)
    const tipoCambio = props.tipoCambio ?? { activo: false, usd_por_bs: 0 };

    const seleccionarTipoPago = (tipoPago) => {
        setTipoPagoSeleccionado(tipoPago);
        setData((prev) => ({
            ...prev,
            tipo_pago_id: tipoPago.id,
            metodo: tipoPago.codigo ?? tipoPago.nombre,
        }));
    };

    const handleMontoChange = (e) => {
        const valor = parseFloat(e.target.value) || 0;
        setData((prev) => ({ ...prev, monto: valor }));
    };

    const equivalenteUsd = bolivianosAUsd(data.monto, tipoCambio.usd_por_bs);

    return (
        <form className="p-4 border rounded-lg bg-white shadow-sm">
            <div>
                <label className="block font-bold text-sm text-wayna-950">
                    Monto (Bs)
                </label>
                <input
                    type="number"
                    min="1"
                    step="0.01"
                    value={data.monto}
                    onChange={handleMontoChange}
                    className="mt-1 block w-full rounded-xl border border-wayna-200 p-2 text-sm shadow-sm"
                />
                {tipoCambio.activo && equivalenteUsd > 0 && (
                    <p className="mt-1 text-xs text-stone-600">
                        ≈ {formatearUsd(equivalenteUsd)}
                    </p>
                )}
            </div>

            <div className="mt-4">
                <label className="block font-bold text-sm text-wayna-950">
                    Selecciona el método de pago
                </label>

                {tipoPagos.length > 1 ? (
                    <div className="mt-2 grid grid-cols-1 gap-2 min-[400px]:grid-cols-2">
                        {tipoPagos.map((tipoPago) => (
                            <button
                                key={tipoPago.id}
                                type="button"
                                onClick={() => seleccionarTipoPago(tipoPago)}
                                disabled={processing}
                                className={
                                    Number(data.tipo_pago_id) === Number(tipoPago.id)
                                        ? 'rounded-xl border border-wayna-500 bg-wayna-500 text-white p-2 font-bold shadow-sm'
                                        : 'rounded-xl border border-wayna-200 bg-surface-card text-wayna-800 p-2 font-bold hover:border-wayna-300 hover:bg-wayna-50'
                                }
                            >
                                {tipoPago.nombre}
                            </button>
                        ))}
                    </div>
                ) : (
                    <p className="mt-2 text-sm font-semibold text-wayna-800">
                        {tipoPagos[0]?.nombre}
                    </p>
                )}

                {/* Bloque de visualización por método */}
                {tipoPagoSeleccionado && (
                    <div className="mt-3">
                        {tipoPagoSeleccionado.codigo === 'efectivo' && (
                            <div className="p-3 border rounded-lg bg-amber-50 text-amber-900">
                                Pago en efectivo: Debes validar en caja al finalizar.
                            </div>
                        )}
                        {tipoPagoSeleccionado.codigo === 'qr' && (
                            <div className="p-3 border rounded-lg bg-blue-50 text-blue-900">
                                Pago QR: Escanea el código con tu app.
                            </div>
                        )}
                        {tipoPagoSeleccionado.codigo === 'tarjeta' && (
                            <div className="p-3 border rounded-lg bg-green-50 text-green-900">
                                Pago con tarjeta: Se procesará automáticamente.
                            </div>
                        )}
                        {tipoPagoSeleccionado.codigo === 'transferencia' && (
                            <div className="p-3 border rounded-lg bg-purple-50 text-purple-900">
                                Transferencia: Usa los datos de la cuenta para completar.
                            </div>
                        )}
                    </div>
                )}
            </div>

            <div className="mt-4">
                <button
                    type="submit"
                    className="rounded-xl bg-wayna-700 px-4 py-2.5 text-white font-bold shadow-sm hover:bg-wayna-800"
                    disabled={processing}
                >
                    Confirmar donación
                </button>
            </div>
        </form>
    );
}