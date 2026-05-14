import { useForm } from '@inertiajs/react';

export default function DonacionForm({ campanaActiva, tipoPagos = [] }) {
    const montosRapidos = [5, 10, 20, 50];

    const tipoPagoInicial = tipoPagos[0] ?? null;

    const { data, setData, post, processing, errors } = useForm({
        campana_id: campanaActiva?.id ?? '',
        tipo_pago_id: tipoPagoInicial?.id ?? '',
        visitante_id: '',
        monto: 10,
        metodo: tipoPagoInicial?.codigo ?? tipoPagoInicial?.nombre ?? 'qr_digital',
        referencia_pago: '',
    });

    const seleccionarMonto = (monto) => {
        setData('monto', monto);
    };

    const seleccionarTipoPago = (tipoPago) => {
        setData('tipo_pago_id', tipoPago.id);
        setData('metodo', tipoPago.codigo ?? tipoPago.nombre);
    };

    const submit = (e) => {
        e.preventDefault();

        post(route('turista.donaciones.store'), {
            preserveScroll: true,
        });
    };

    const puedeEnviar =
        campanaActiva?.id &&
        data.tipo_pago_id &&
        Number(data.monto) > 0 &&
        !processing;

    return (
        <form
            onSubmit={submit}
            className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
        >
            <h2 className="text-lg font-bold text-gray-900">
                Elige tu aporte
            </h2>

            <p className="mt-1 text-sm text-gray-500">
                Selecciona un monto en bolivianos y confirma tu apoyo.
            </p>

            {!campanaActiva && (
                <div className="mt-4 rounded-xl bg-yellow-50 p-3 text-sm text-yellow-700">
                    Este emprendedor todavía no tiene una campaña activa.
                </div>
            )}

            {tipoPagos.length === 0 && (
                <div className="mt-4 rounded-xl bg-yellow-50 p-3 text-sm text-yellow-700">
                    No existen tipos de pago disponibles.
                </div>
            )}

            <div className="mt-4 grid grid-cols-4 gap-2">
                {montosRapidos.map((monto) => (
                    <button
                        key={monto}
                        type="button"
                        onClick={() => seleccionarMonto(monto)}
                        disabled={processing}
                        className={`rounded-xl border px-3 py-3 text-sm font-bold transition ${
                            Number(data.monto) === monto
                                ? 'border-wayna-600 bg-wayna-600 text-white'
                                : 'border-gray-200 bg-white text-gray-700 hover:bg-wayna-50'
                        }`}
                    >
                        Bs {monto}
                    </button>
                ))}
            </div>

            <div className="mt-4">
                <label className="block text-sm font-medium text-gray-700">
                    Otro monto
                </label>

                <input
                    type="number"
                    min="1"
                    step="0.01"
                    value={data.monto}
                    onChange={(e) => setData('monto', e.target.value)}
                    disabled={processing}
                    className="mt-1 block w-full rounded-xl border-gray-300 focus:border-wayna-500 focus:ring-wayna-500"
                    placeholder="Ej. 30"
                />

                {errors.monto && (
                    <p className="mt-2 text-sm text-red-600">
                        {errors.monto}
                    </p>
                )}
            </div>

            {tipoPagos.length > 1 && (
                <div className="mt-4">
                    <p className="text-sm font-medium text-gray-700">
                        Método de pago
                    </p>

                    <div className="mt-2 grid grid-cols-2 gap-2">
                        {tipoPagos.map((tipoPago) => (
                            <button
                                key={tipoPago.id}
                                type="button"
                                onClick={() => seleccionarTipoPago(tipoPago)}
                                disabled={processing}
                                className={`rounded-xl border px-3 py-3 text-sm font-bold transition ${
                                    Number(data.tipo_pago_id) === Number(tipoPago.id)
                                        ? 'border-wayna-600 bg-wayna-600 text-white'
                                        : 'border-gray-200 bg-white text-gray-700 hover:bg-wayna-50'
                                }`}
                            >
                                {tipoPago.nombre}
                            </button>
                        ))}
                    </div>

                    {errors.tipo_pago_id && (
                        <p className="mt-2 text-sm text-red-600">
                            {errors.tipo_pago_id}
                        </p>
                    )}
                </div>
            )}

            {errors.campana_id && (
                <p className="mt-3 text-sm text-red-600">
                    {errors.campana_id}
                </p>
            )}

            <button
                type="submit"
                disabled={!puedeEnviar}
                className={`mt-5 w-full rounded-xl px-4 py-3 text-sm font-bold text-white transition ${
                    puedeEnviar
                        ? 'bg-wayna-600 hover:bg-wayna-700'
                        : 'cursor-not-allowed bg-gray-300'
                }`}
            >
                {processing
                    ? 'Procesando...'
                    : `Confirmar aporte de Bs ${data.monto}`}
            </button>

            <p className="mt-3 text-center text-xs text-gray-400">
                Tu aporte se registrará como pendiente hasta ser confirmado.
            </p>
        </form>
    );
}