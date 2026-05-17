import { useTranslation } from 'react-i18next';

const ENLACES = [
    { key: 'whatsapp', labelKey: 'tourist.profile.socialWhatsApp', className: 'bg-emerald-600 hover:bg-emerald-700' },
    { key: 'instagram', labelKey: 'tourist.profile.socialInstagram', className: 'bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-90' },
    { key: 'facebook', labelKey: 'tourist.profile.socialFacebook', className: 'bg-blue-700 hover:bg-blue-800' },
    {
        key: 'tiktok',
        labelKey: 'tourist.profile.socialTikTok',
        className: 'bg-stone-900 hover:bg-black',
    },
    {
        key: 'sitio_web',
        labelKey: 'tourist.profile.socialWebsite',
        className: 'bg-wayna-800 hover:bg-wayna-900',
    },
];

export default function PerfilRedesSociales({ redes }) {
    const { t } = useTranslation();

    const activos = ENLACES.filter(({ key }) => Boolean(redes?.[key]));

    if (activos.length === 0) {
        return null;
    }

    return (
        <section className="rounded-2xl border border-wayna-100 bg-wayna-50/40 p-5">
            <h2 className="text-lg font-bold text-gray-900">{t('tourist.profile.socialTitle')}</h2>
            <p className="mt-1 text-sm text-stone-600">{t('tourist.profile.socialSubtitle')}</p>

            <ul className="mt-4 flex flex-wrap gap-2">
                {activos.map(({ key, labelKey, className }) => (
                    <li key={key}>
                        <a
                            href={redes[key]}
                            target="_blank"
                            rel="noopener noreferrer"
                            className={`inline-flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-sm font-bold text-white shadow-md transition ${className}`}
                        >
                            {t(labelKey)}
                        </a>
                    </li>
                ))}
            </ul>
        </section>
    );
}
