import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function SeguirEstado({ tipo, emprendedorNombre, emprendedorId }) {
    const { t } = useTranslation();

    const configs = {
        confirmado: {
            border: 'border-emerald-200 bg-emerald-50 text-emerald-950',
            title: t('tourist.follow.pageConfirmedTitle'),
            body: t('tourist.follow.pageConfirmedBody', { name: emprendedorNombre }),
        },
        baja: {
            border: 'border-stone-200 bg-stone-50 text-stone-800',
            title: t('tourist.follow.pageUnsubTitle'),
            body: t('tourist.follow.pageUnsubBody', { name: emprendedorNombre }),
        },
        error: {
            border: 'border-red-200 bg-red-50 text-red-950',
            title: t('tourist.follow.pageErrorTitle'),
            body: t('tourist.follow.pageErrorBody'),
        },
    };

    const config = configs[tipo] ?? configs.error;

    return (
        <GuestLayout variant="full" contentClassName="max-w-md">
            <Head title={config.title} />

            <div className={`rounded-2xl border p-6 shadow-sm ${config.border}`}>
                <h1 className="text-xl font-black">{config.title}</h1>
                <p className="mt-3 text-sm leading-relaxed">{config.body}</p>

                {emprendedorId ? (
                    <Link
                        href={route('turista.emprendedor.show', emprendedorId)}
                        className="btn-wayna-primary mt-6 inline-flex"
                    >
                        {t('tourist.follow.backToProfile')}
                    </Link>
                ) : (
                    <Link href="/" className="btn-wayna-primary mt-6 inline-flex">
                        {t('explore.backToExplore')}
                    </Link>
                )}
            </div>
        </GuestLayout>
    );
}
