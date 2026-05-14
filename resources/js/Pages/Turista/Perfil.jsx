import GuestLayout from '@/Layouts/GuestLayout';
import { useTranslation } from 'react-i18next';

export default function Perfil({ emprendedor }) {
    const { t } = useTranslation();

    return (
        <GuestLayout>
            <section className="mx-auto max-w-4xl px-4 py-6">
                <h1 className="text-2xl font-bold text-gray-900">
                    {t('tourist.heroTitle')}
                </h1>

                <p className="mt-2 text-gray-600">
                    {t('tourist.heroDescription')}
                </p>
            </section>
        </GuestLayout>
    );
}   