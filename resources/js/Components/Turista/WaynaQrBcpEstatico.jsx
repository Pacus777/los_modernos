import { useState } from 'react';
import { useTranslation } from 'react-i18next';

const PASOS = ['step1', 'step2', 'step3', 'step4', 'step5'];

/**
 * S2-07 — QR BCP estático de WAYNA + instrucciones numeradas.
 */
export default function WaynaQrBcpEstatico({
    config = {},
    monto = null,
    referenciaPago = null,
    variant = 'confirmacion',
}) {
    const { t, i18n } = useTranslation();
    const [ampliado, setAmpliado] = useState(false);

    if (!config?.habilitado) {
        return null;
    }

    const locale = i18n.language?.startsWith('en') ? 'en-US' : 'es-BO';
    const montoFormateado =
        monto !== null && monto !== ''
            ? Number(monto).toLocaleString(locale, {
                  minimumFractionDigits: 0,
                  maximumFractionDigits: 2,
              })
            : null;

    const esVistaPrevia = variant === 'preview';

    return (
        <div className="overflow-hidden rounded-2xl border-2 border-[#003882]/20 bg-gradient-to-b from-sky-50 to-white shadow-sm">
            <div className="flex items-center gap-2 border-b border-[#003882]/15 bg-[#003882] px-4 py-3 text-white">
                <span className="rounded-md bg-white/15 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                    {config.banco ?? 'BCP'}
                </span>
                <span className="text-sm font-bold">{config.titular ?? 'WAYNA'}</span>
            </div>

            <div className="space-y-4 p-4 sm:p-5">
                <div>
                    <h3 className="text-base font-bold text-stone-900">
                        {t('tourist.bcpQr.title')}
                    </h3>
                    <p className="mt-1 text-sm leading-relaxed text-stone-700">
                        {esVistaPrevia
                            ? t('tourist.bcpQr.subtitlePreview')
                            : t('tourist.bcpQr.subtitleConfirm')}
                    </p>
                </div>

                <ol className="space-y-2.5">
                    {PASOS.map((clave, index) => (
                        <li
                            key={clave}
                            className="flex gap-3 rounded-xl border border-sky-100 bg-white/90 px-3 py-2.5 text-sm text-stone-800"
                        >
                            <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#003882] text-xs font-bold text-white">
                                {index + 1}
                            </span>
                            <span className="pt-0.5 leading-snug">
                                {t(`tourist.bcpQr.${clave}`)}
                            </span>
                        </li>
                    ))}
                </ol>

                {(montoFormateado || referenciaPago) && (
                    <div className="grid gap-2 rounded-xl border border-amber-200 bg-amber-50/80 p-3 text-sm text-amber-950 sm:grid-cols-2">
                        {montoFormateado && (
                            <p>
                                <span className="block text-xs font-semibold uppercase tracking-wide text-amber-800/80">
                                    {t('tourist.bcpQr.amountLabel')}
                                </span>
                                <span className="text-lg font-black">
                                    {t('common.currencyBs')} {montoFormateado}
                                </span>
                            </p>
                        )}
                        {referenciaPago && (
                            <p className="sm:text-right">
                                <span className="block text-xs font-semibold uppercase tracking-wide text-amber-800/80">
                                    {t('tourist.confirmation.referenceLabel')}
                                </span>
                                <span className="break-all font-mono text-base font-bold">
                                    {referenciaPago}
                                </span>
                            </p>
                        )}
                    </div>
                )}

                <div className="flex flex-col items-center gap-2">
                    <button
                        type="button"
                        onClick={() => setAmpliado((v) => !v)}
                        className="touch-target rounded-2xl border-2 border-[#003882]/25 bg-white p-3 shadow-inner transition hover:border-[#003882]/50 focus:outline-none focus:ring-2 focus:ring-sky-500/40"
                        aria-label={t('tourist.bcpQr.qrAlt')}
                    >
                        <img
                            src={config.imagen_url}
                            alt=""
                            className={`mx-auto object-contain transition-all ${
                                ampliado
                                    ? 'h-[min(18rem,80vw)] w-[min(18rem,80vw)]'
                                    : 'h-[min(12rem,70vw)] w-[min(12rem,70vw)]'
                            }`}
                        />
                    </button>
                    <p className="text-center text-xs font-semibold text-[#003882]">
                        {ampliado
                            ? t('tourist.bcpQr.tapToShrink')
                            : t('tourist.bcpQr.tapToEnlarge')}
                    </p>
                </div>

                <p className="rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-xs leading-relaxed text-stone-600">
                    {t('tourist.bcpQr.footnote')}
                </p>
            </div>
        </div>
    );
}
