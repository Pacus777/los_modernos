import GuestLayout from '@/Layouts/GuestLayout';
import DonacionForm from '@/Components/Turista/DonacionForm';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function Perfil({ emprendedor, campanaActiva, progreso, tipoPagos }) {
    const { t } = useTranslation();

    const nombreCompleto = `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim();

    const fotoUrl = emprendedor.fotografia
        ? `/storage/${emprendedor.fotografia}`
        : null;

    const porcentaje = progreso?.porcentaje ?? 0;

    return (
        <GuestLayout variant="full">
            <Head title={t('tourist.profile.headTitle', { name: nombreCompleto })} />

            <section className="overflow-hidden rounded-3xl bg-white shadow-xl shadow-wayna-900/10">
                <div className="relative h-64 bg-gradient-to-br from-wayna-100 to-orange-100">
                    {fotoUrl ? (
                        <img
                            src={fotoUrl}
                            alt={nombreCompleto}
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <div className="flex h-full items-center justify-center px-6 text-center">
                            <span className="text-lg font-semibold text-wayna-700">
                                {t('tourist.profile.photoPlaceholder')}
                            </span>
                        </div>
                    )}

                    <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-5">
                        <p className="text-sm font-medium text-orange-100">
                            {t('tourist.profile.badge')}
                        </p>

                        <h1 className="text-2xl font-bold text-white">
                            {nombreCompleto}
                        </h1>
                    </div>
                </div>

                <div className="space-y-6 p-5">
                    <div>
                        <h2 className="text-lg font-bold text-gray-900">
                            {t('tourist.profile.storyTitle')}
                        </h2>

                        <p className="mt-2 text-sm leading-6 text-gray-600">
                            {emprendedor.descripcion || t('tourist.profile.storyFallback')}
                        </p>
                    </div>

                    {campanaActiva ? (
                        <div className="rounded-2xl border border-wayna-100 bg-wayna-50 p-4">
                            <div className="flex items-start justify-between gap-4">
                                <div>
                                    <p className="text-sm font-medium text-wayna-700">
                                        {t('tourist.profile.goalSectionTitle')}
                                    </p>

                                    <h3 className="mt-1 text-lg font-bold text-gray-900">
                                        {campanaActiva.titulo}
                                    </h3>
                                </div>

                                <span className="rounded-full bg-white px-3 py-1 text-sm font-bold text-wayna-700 shadow-sm">
                                    {porcentaje}%
                                </span>
                            </div>

                            <div className="mt-4 h-3 overflow-hidden rounded-full bg-white">
                                <div
                                    className="h-full rounded-full bg-wayna-600 transition-all duration-700"
                                    style={{ width: `${porcentaje}%` }}
                                />
                            </div>

                            <div className="mt-3 grid grid-cols-2 gap-3 text-sm">
                                <div className="rounded-xl bg-white p-3">
                                    <p className="text-gray-500">
                                        {t('tourist.profile.raised')}
                                    </p>
                                    <p className="font-bold text-gray-900">
                                        Bs{' '}
                                        {Number(progreso?.monto_recaudado ?? 0).toFixed(2)}
                                    </p>
                                </div>

                                <div className="rounded-xl bg-white p-3">
                                    <p className="text-gray-500">
                                        {t('tourist.profile.goal')}
                                    </p>
                                    <p className="font-bold text-gray-900">
                                        Bs {Number(progreso?.meta ?? 0).toFixed(2)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="space-y-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                            <h3 className="text-base font-bold text-amber-950">
                                {t('tourist.profile.noCampaignTitle')}
                            </h3>
                            <p className="text-sm leading-relaxed text-amber-900/90">
                                {t('tourist.profile.noCampaignBody')}
                            </p>
                            {Number(progreso?.meta ?? 0) > 0 && (
                                <p className="text-sm text-amber-800">
                                    <span className="font-medium">
                                        {t('tourist.profile.referenceMetaNote')}:
                                    </span>{' '}
                                    Bs {Number(progreso.meta).toFixed(2)}
                                </p>
                            )}
                        </div>
                    )}

                    <DonacionForm
                        key={campanaActiva?.id ?? 'sin-campana'}
                        campanaActiva={campanaActiva}
                        tipoPagos={tipoPagos}
                    />
                </div>
            </section>
        </GuestLayout>
    );
}
