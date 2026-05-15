import { useState } from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Referencia de pago WAYNA visible y copiable (T-A25).
 */
export default function ReferenciaPagoDestacada({
    referencia,
    variant = 'turista',
    className = '',
}) {
    const { t } = useTranslation();
    const [copiado, setCopiado] = useState(false);

    if (!referencia?.trim()) {
        return null;
    }

    const codigo = referencia.trim();

    const copiar = async () => {
        try {
            await navigator.clipboard.writeText(codigo);
            setCopiado(true);
            window.setTimeout(() => setCopiado(false), 2000);
        } catch {
            setCopiado(false);
        }
    };

    const esAdmin = variant === 'admin';

    return (
        <div
            className={`rounded-2xl border-2 px-4 py-4 text-center shadow-inner ${
                esAdmin
                    ? 'border-wayna-300 bg-gradient-to-b from-wayna-50 to-white'
                    : 'border-wayna-400/80 bg-gradient-to-b from-wayna-50/95 via-white to-wayna-50/40 ring-2 ring-wayna-200/60'
            } ${className}`}
            role="region"
            aria-label={t('tourist.confirmation.referenceLabel')}
        >
            <p className="text-[11px] font-bold uppercase tracking-[0.2em] text-wayna-800">
                {t('tourist.confirmation.referenceLabel')}
            </p>
            <p className="mt-1 text-xs text-stone-600">
                {t('tourist.confirmation.referenceHint')}
            </p>
            <p className="mt-3 break-all font-mono text-xl font-black tracking-wide text-wayna-950 sm:text-2xl">
                {codigo}
            </p>
            <button
                type="button"
                onClick={copiar}
                className="btn-wayna-secondary mt-4 text-xs sm:text-sm"
            >
                {copiado
                    ? t('tourist.confirmation.referenceCopied')
                    : t('tourist.confirmation.referenceCopy')}
            </button>
        </div>
    );
}
