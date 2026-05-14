import { useForm } from '@inertiajs/react';
import { useEffect, useMemo } from 'react';
import { useTranslation } from 'react-i18next';

export default function DonacionForm({ campanasActivas = [], tipoPagos = [] }) {
    const { t } = useTranslation();
    const montosRapidos = [5, 10, 20, 50];

    const listaCampanas = useMemo(
        () => (Array.isArray(campanasActivas) ? campanasActivas : []),
        [campanasActivas],
    );

    const tipoPagoInicial = tipoPagos[0] ?? null;

    const primeraCampanaId = listaCampanas[0]?.id ?? '';

    const { data, setData, post, processing, errors } = useForm({
        campana_id: primeraCampanaId,
        tipo_pago_id: tipoPagoInicial?.id ?? '',
        visitante_id: '',
        monto: 10,
        metodo: tipoPagoInicial?.codigo ?? tipoPagoInicial?.nombre ?? 'qr_digital',
        referencia_pago: '',
    });

    const idsKey = listaCampanas.map((c) => c.id).join(',');

    useEffect(() => {
        if (listaCampanas.length === 0) {
            setData('campana_id', '');
            return;
        }

        const validIds = new Set(listaCampanas.map((c) => Number(c.id)));
        const current = Number(data.campana_id);

        if (!Number.isFinite(current) || !validIds.has(current)) {
            setData('campana_id', listaCampanas[0].id);
        }
    }, [idsKey, listaCampanas, setData, data.campana_id]);

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

    const campanaIdValido =
        listaCampanas.length > 0 &&
        listaCampanas.some((c) => String(c.id) === String(data.campana_id));

    const puedeEnviar =
        campanaIdValido &&
        data.tipo_pago_id &&
        Number(data.monto) > 0 &&
        !processing;

    return (
        <form
            onSubmit={submit}
            className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
        >
            <h2 className="text-lg font-bold text-gray-900">
                {t('tourist.donationForm.chooseTitle')}
            </h2>

            <p className="mt-1 text-sm text-gray-500">
                {t('tourist.donationForm.chooseSubtitle')}
            </p>

            {listaCampanas.length === 0 && (
                <div className="mt-4 rounded-xl bg-yellow-50 p-3 text-sm text-yellow-700">
                    {t('tourist.donationForm.noActiveCampaign')}
                </div>
            )}

            {listaCampanas.length > 0 && (
                <div className="mt-4">
                    <label
                        htmlFor="donacion-campana-id"
                        className="block text-sm font-medium text-gray-700"
                    >
                        {t('tourist.donationForm.campaignLabel')}
                    </label>
                    <select
                        id="donacion-campana-id"
                        value={data.campana_id}
                        onChange={(e) => setData('campana_id', e.target.value)}
                        disabled={processing}
                        className="mt-1 block w-full rounded-xl border-gray-300 focus:border-wayna-500 focus:ring-wayna-500"
                    >
                        {listaCampanas.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.titulo}
                            </option>
                        ))}
                    </select>
                </div>
            )}

            {tipoPagos.length === 0 && (
                <div className="mt-4 rounded-xl bg-yellow-50 p-3 text-sm text-yellow-700">
                    {t('tourist.donationForm.noPaymentTypes')}
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
                    {t('tourist.donationForm.otherAmount')}
                </label>

                <input
                    type="number"
                    min="1"
                    step="0.01"
                    value={data.monto}
                    onChange={(e) => setData('monto', e.target.value)}
                    disabled={processing}
                    className="mt-1 block w-full rounded-xl border-gray-300 focus:border-wayna-500 focus:ring-wayna-500"
                    placeholder={t('tourist.donationForm.amountPlaceholder')}
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
                        {t('tourist.donationForm.paymentMethod')}
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
                    ? t('tourist.donationForm.processing')
                    : t('tourist.donationForm.confirmWithAmount', {
                          amount: data.monto,
                      })}
            </button>

            <p className="mt-3 text-center text-xs text-gray-400">
                {t('tourist.donationForm.pendingNote')}
            </p>
        </form>
    );
}
