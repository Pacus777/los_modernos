import WaynaQrBcpEstatico from '@/Components/Turista/WaynaQrBcpEstatico';
import { etiquetaTipoPagoT } from '@/utils/catalogosI18n';
import { clasificarMetodoPago } from '@/utils/clasificarMetodoPago';
import { bolivianosAUsd, formatearUsd } from '@/utils/tipoCambioTurista';
import { useForm, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useRef } from 'react';
import { useTranslation } from 'react-i18next';

function generarPaymentUuid() {
    if (
        typeof crypto !== 'undefined' &&
        typeof crypto.randomUUID === 'function'
    ) {
        return crypto.randomUUID();
    }

    return `pay-${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

export default function DonacionForm({
    campanasActivas = [],
    tipoPagos = [],
    visitanteNombrePrefill = '',
}) {
    const { t, i18n } = useTranslation();
    const tipoCambio = usePage().props.tipoCambio ?? {
        activo: false,
        usd_por_bs: 0,
    };
    const waynaBcpQr = usePage().props.waynaBcpQr ?? { habilitado: false };

    const montosRapidos = [5, 10, 20, 50];

    /*
    |--------------------------------------------------------------------------
    | S2-05: payment_uuid + bloqueo anti doble clic
    |--------------------------------------------------------------------------
    |
    | payment_uuid identifica este intento de pago desde el frontend.
    | submitLockRef evita doble envío inmediato aunque React todavía no haya
    | actualizado el estado processing.
    |
    */

    const paymentUuidInicial = useMemo(() => generarPaymentUuid(), []);
    const submitLockRef = useRef(false);

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
        visitante_nombre: visitanteNombrePrefill ?? '',
        monto: 10,
        metodo: tipoPagoInicial?.codigo ?? tipoPagoInicial?.nombre ?? 'qr_digital',
        referencia_pago: '',
        payment_uuid: paymentUuidInicial,
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
        if (processing) return;

        setData('monto', monto);
    };

    const seleccionarTipoPago = (tipoPago) => {
        if (processing) return;

        setData('tipo_pago_id', tipoPago.id);
        setData('metodo', tipoPago.codigo ?? tipoPago.nombre);
    };

    const campanaIdValido =
        listaCampanas.length > 0 &&
        listaCampanas.some((c) => String(c.id) === String(data.campana_id));

    const puedeEnviar =
        campanaIdValido &&
        data.tipo_pago_id &&
        Number(data.monto) > 0 &&
        !processing;

    const submit = (e) => {
        e.preventDefault();

        if (!puedeEnviar || submitLockRef.current) {
            return;
        }

        submitLockRef.current = true;

        post(route('turista.donaciones.store'), {
            preserveScroll: true,
            onError: () => {
                submitLockRef.current = false;
            },
            onCancel: () => {
                submitLockRef.current = false;
            },
            onFinish: () => {
                submitLockRef.current = false;
            },
        });
    };

    const tipoPagoSeleccionado = useMemo(
        () =>
            tipoPagos.find((tp) => Number(tp.id) === Number(data.tipo_pago_id)) ??
            null,
        [tipoPagos, data.tipo_pago_id],
    );

    const claseMetodoPago = useMemo(
        () => clasificarMetodoPago(tipoPagoSeleccionado, data.metodo),
        [tipoPagoSeleccionado, data.metodo],
    );

    const instruccionMetodoPago = useMemo(() => {
        if (claseMetodoPago === 'efectivo') {
            return t('tourist.donationForm.instructionCash');
        }

        if (claseMetodoPago === 'qr') {
            return t('tourist.donationForm.instructionQr');
        }

        return t('tourist.donationForm.instructionOther');
    }, [claseMetodoPago, t]);

    const estiloInstruccion =
        claseMetodoPago === 'efectivo'
            ? 'border-amber-200 bg-amber-50 text-amber-950'
            : claseMetodoPago === 'qr'
              ? 'border-wayna-200 bg-wayna-50 text-wayna-950'
              : 'border-stone-200 bg-stone-50 text-stone-800';

    const equivalenteUsd = useMemo(() => {
        if (!tipoCambio.activo) {
            return null;
        }

        return bolivianosAUsd(data.monto, tipoCambio.usd_por_bs);
    }, [data.monto, tipoCambio.activo, tipoCambio.usd_por_bs]);

    const localeMoneda = i18n.language?.startsWith('en') ? 'en-US' : 'es-BO';

    const formatearBs = (valor) =>
        Number(valor).toLocaleString(localeMoneda, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        });

    return (
        <form
            onSubmit={submit}
            className="rounded-2xl border border-surface-200 bg-surface-card p-4 shadow-sm"
        >
            <input
                type="hidden"
                name="payment_uuid"
                value={data.payment_uuid}
                readOnly
            />

            <h2 className="text-lg font-bold text-gray-900">
                {t('tourist.donationForm.chooseTitle')}
            </h2>

            <p className="mt-1 text-sm text-gray-500">
                {t('tourist.donationForm.chooseSubtitle')}
            </p>

            <div className="mt-4">
                <label
                    htmlFor="donacion-visitante-nombre"
                    className="block text-sm font-medium text-gray-700"
                >
                    {t('tourist.donationForm.visitorNameLabel')}
                    <span className="ml-1 font-normal text-gray-400">
                        ({t('tourist.donationForm.optional')})
                    </span>
                </label>

                <input
                    id="donacion-visitante-nombre"
                    type="text"
                    maxLength={120}
                    value={data.visitante_nombre}
                    onChange={(e) =>
                        setData('visitante_nombre', e.target.value)
                    }
                    disabled={processing}
                    autoComplete="name"
                    className="input-wayna mt-1 w-full"
                    placeholder={t(
                        'tourist.donationForm.visitorNamePlaceholder',
                    )}
                />

                <p className="mt-1.5 text-xs text-gray-500">
                    {t('tourist.donationForm.visitorNameHint')}
                </p>

                {errors.visitante_nombre && (
                    <p className="mt-2 text-sm text-red-600">
                        {errors.visitante_nombre}
                    </p>
                )}
            </div>

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
                        className="input-wayna mt-1 w-full"
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

            <div className="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                {montosRapidos.map((monto) => (
                    <button
                        key={monto}
                        type="button"
                        onClick={() => seleccionarMonto(monto)}
                        disabled={processing}
                        className={
                            Number(data.monto) === monto
                                ? 'btn-wayna-chip-selected'
                                : 'btn-wayna-chip'
                        }
                    >
                        {t('common.currencyBs')} {monto}
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
                    className="input-wayna mt-1 w-full"
                    placeholder={t('tourist.donationForm.amountPlaceholder')}
                />

                {errors.monto && (
                    <p className="mt-2 text-sm text-red-600">
                        {errors.monto}
                    </p>
                )}

                {equivalenteUsd !== null && (
                    <p
                        className="mt-2 flex flex-wrap items-center gap-1.5 rounded-xl border border-sky-200/80 bg-sky-50/90 px-3 py-2.5 text-sm text-sky-950"
                        role="status"
                        aria-live="polite"
                    >
                        <span className="font-semibold text-sky-900">
                            {t('common.currencyBs')} {formatearBs(data.monto)}
                        </span>

                        <span className="text-sky-700" aria-hidden>
                            ≈
                        </span>

                        <span className="font-bold text-sky-950">
                            {formatearUsd(equivalenteUsd, localeMoneda)}
                        </span>

                        <span className="w-full text-[11px] font-medium text-sky-800/80">
                            {t('tourist.donationForm.exchangeRateHint', {
                                usdToBob: Number(tipoCambio.usd_to_bob ?? 0).toLocaleString(
                                    localeMoneda,
                                    { minimumFractionDigits: 2, maximumFractionDigits: 2 },
                                ),
                                compra: Number(tipoCambio.compra ?? tipoCambio.usd_to_bob ?? 0).toLocaleString(
                                    localeMoneda,
                                    { minimumFractionDigits: 2, maximumFractionDigits: 2 },
                                ),
                            })}
                        </span>
                    </p>
                )}
            </div>

            {tipoPagos.length > 0 && (
                <div className="mt-4">
                    <p className="text-sm font-medium text-gray-700">
                        {t('tourist.donationForm.paymentMethod')}
                    </p>

                    {tipoPagos.length > 1 ? (
                        <div className="mt-2 grid grid-cols-1 gap-2 min-[400px]:grid-cols-2">
                            {tipoPagos.map((tipoPago) => (
                                <button
                                    key={tipoPago.id}
                                    type="button"
                                    onClick={() => seleccionarTipoPago(tipoPago)}
                                    disabled={processing}
                                    className={
                                        Number(data.tipo_pago_id) ===
                                        Number(tipoPago.id)
                                            ? 'btn-wayna-chip-payment-selected'
                                            : 'btn-wayna-chip-payment'
                                    }
                                >
                                    {etiquetaTipoPagoT(t, tipoPago)}
                                </button>
                            ))}
                        </div>
                    ) : (
                        <p className="mt-2 text-sm font-semibold text-wayna-800">
                            {etiquetaTipoPagoT(t, tipoPagos[0])}
                        </p>
                    )}

                    {tipoPagoSeleccionado && (
                        <div
                            className={`mt-3 rounded-xl border px-3 py-3 text-sm leading-relaxed ${estiloInstruccion}`}
                            role="status"
                            aria-live="polite"
                        >
                            {instruccionMetodoPago}
                        </div>
                    )}

                    {claseMetodoPago === 'qr' && waynaBcpQr?.habilitado && (
                        <div className="mt-4">
                            <WaynaQrBcpEstatico
                                config={waynaBcpQr}
                                monto={data.monto}
                                variant="preview"
                            />
                        </div>
                    )}

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

            {errors.payment_uuid && (
                <p
                    className="mt-3 text-sm font-medium text-red-600"
                    role="alert"
                >
                    {errors.payment_uuid}
                </p>
            )}

            <button
                type="submit"
                disabled={!puedeEnviar}
                aria-busy={processing}
                className={`btn-wayna-primary touch-target mt-5 min-h-11 w-full ${
                    puedeEnviar
                        ? ''
                        : 'cursor-not-allowed !bg-stone-300 !shadow-none hover:!bg-stone-300'
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