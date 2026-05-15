import QrPreviewModal from '@/Components/QrPreviewModal';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Pantalla posterior al POST de donación (T-26).
 * Prioriza props del servidor (ruta con id de donación) para que un cambio de idioma
 * no pierda QR/referencia; el flash de Laravel solo dura un request.
 */
export default function Confirmacion() {
    const { t } = useTranslation();
    const page = usePage();
    const flash = page.props.flash ?? {};
    const confirmacion = page.props.confirmacion ?? null;

    const qrUrl = page.props.qr_pago_url ?? flash.qr_pago_url;
    const referencia =
        confirmacion?.referencia_pago ?? flash.referencia_pago;
    const successMessage =
        page.props.success ??
        flash.success ??
        t('tourist.confirmation.successFallback');
    const tieneDatosDonacion = Boolean(qrUrl || referencia);

    const metodo = confirmacion?.metodo ?? '';
    const esQrEfectivo =
        (typeof metodo === 'string' &&
            metodo.toLowerCase().includes('efectivo')) ||
        (typeof qrUrl === 'string' &&
            qrUrl.includes('/donaciones/qrs/efectivo/'));

    const [qrAmpliado, setQrAmpliado] = useState(false);

    return (
        <GuestLayout variant="full">
            <Head title={t('tourist.confirmation.headTitle')} />

            <section className="overflow-hidden rounded-3xl border border-wayna-100 bg-white shadow-xl shadow-wayna-900/10">
                <div className="header-wayna-gradient border-b border-wayna-400/30 px-5 py-6">
                    <h1 className="text-xl font-bold sm:text-2xl">
                        {t('tourist.confirmation.title')}
                    </h1>
                    <p className="mt-2 text-sm text-orange-50/95">
                        {t('tourist.confirmation.intro')}
                    </p>
                </div>

                <div className="space-y-6 p-5 sm:p-6">
                    {!tieneDatosDonacion ? (
                        <div className="rounded-2xl bg-wayna-50 px-4 py-6 text-center">
                            <p className="text-sm font-medium text-wayna-900">
                                {t('tourist.confirmation.emptyTitle')}
                            </p>
                            <p className="mt-2 text-sm text-stone-600">
                                {t('tourist.confirmation.emptyBody')}
                            </p>
                            <Link
                                href="/"
                                className="btn-wayna-primary mt-4"
                            >
                                {t('tourist.confirmation.emptyCta')}
                            </Link>
                        </div>
                    ) : (
                        <>
                            <p className="text-center text-base leading-relaxed text-stone-700">
                                {successMessage}
                            </p>

                            {referencia && (
                                <div className="rounded-2xl border border-wayna-100 bg-wayna-50/80 px-4 py-3 text-center">
                                    <p className="text-xs font-semibold uppercase tracking-wide text-wayna-800">
                                        {t('tourist.confirmation.referenceLabel')}
                                    </p>
                                    <p className="mt-1 break-all font-mono text-sm font-semibold text-wayna-950">
                                        {referencia}
                                    </p>
                                </div>
                            )}

                            {qrUrl && (
                                <div className="flex flex-col items-center gap-3">
                                    <button
                                        type="button"
                                        onClick={() => setQrAmpliado(true)}
                                        className="rounded-2xl border-2 border-wayna-200 bg-white p-4 shadow-inner transition hover:border-wayna-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-wayna-500/40"
                                        aria-label={t('tourist.confirmation.qrAlt')}
                                    >
                                        <img
                                            src={qrUrl}
                                            alt=""
                                            className="pointer-events-none h-56 w-56 max-w-full object-contain sm:h-64 sm:w-64"
                                        />
                                    </button>
                                    <p className="text-center text-xs font-semibold text-wayna-700">
                                        Tocá el QR para verlo en grande
                                    </p>
                                    <p className="max-w-md text-center text-sm text-stone-600">
                                        {esQrEfectivo
                                            ? t('tourist.confirmation.helpCash')
                                            : t('tourist.confirmation.helpDigital')}
                                    </p>
                                </div>
                            )}

                            <div className="flex flex-col gap-3 border-t border-wayna-100 pt-4 sm:flex-row sm:justify-center">
                                <Link
                                    href="/"
                                    className="inline-flex flex-1 items-center justify-center rounded-xl border border-wayna-200 bg-white px-4 py-3 text-center text-sm font-semibold text-wayna-800 transition hover:bg-wayna-50 sm:flex-none"
                                >
                                    {t('common.back')}
                                </Link>
                            </div>
                        </>
                    )}
                </div>
            </section>

            <QrPreviewModal
                show={qrAmpliado}
                src={qrUrl}
                title={t('tourist.confirmation.title')}
                subtitle={
                    esQrEfectivo
                        ? t('tourist.confirmation.helpCash')
                        : t('tourist.confirmation.helpDigital')
                }
                onClose={() => setQrAmpliado(false)}
            />
        </GuestLayout>
    );
}
