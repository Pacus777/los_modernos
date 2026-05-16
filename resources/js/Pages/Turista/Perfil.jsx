import GuestLayout from '@/Layouts/GuestLayout';
import DonacionForm from '@/Components/Turista/DonacionForm';
import BarraProgreso from '@/Components/Turista/BarraProgreso';
import { Head } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import ChatWidget from '@/Components/Turista/ChatWidget';
import PerfilMediosEmprendedor from '@/Components/Turista/PerfilMediosEmprendedor';

export default function Perfil({
    emprendedor,
    medios,
    campanaActiva,
    campanasActivas = [],
    progreso,
    tipoPagos,
}) {
    const { t } = useTranslation();

    const nombreCompleto = `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim();

    const fotoUrl = emprendedor.foto_portada ?? null;

    return (
        <GuestLayout variant="full">
            <Head title={t('tourist.profile.headTitle', { name: nombreCompleto })} />

            <section className="overflow-hidden rounded-3xl bg-white shadow-xl shadow-wayna-900/10">
                <div className="relative h-48 bg-gradient-to-br from-wayna-100 to-surface-muted sm:h-64">
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
                        <div className="mt-2 flex flex-wrap gap-1.5">
                            {emprendedor.tipo_emprendimiento_etiqueta ? (
                                <span className="inline-flex rounded-full bg-white/20 px-3 py-0.5 text-xs font-bold text-white ring-1 ring-white/30">
                                    {emprendedor.tipo_emprendimiento_etiqueta}
                                </span>
                            ) : null}
                            {emprendedor.departamento_etiqueta ? (
                                <span className="inline-flex rounded-full bg-white/15 px-3 py-0.5 text-xs font-bold text-orange-50 ring-1 ring-white/25">
                                    {emprendedor.departamento_etiqueta}
                                </span>
                            ) : null}
                        </div>
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

                    {medios?.foto_empresa && (
                        <div>
                            <h2 className="text-lg font-bold text-gray-900">
                                {t('tourist.profile.businessPhotoTitle')}
                            </h2>
                            <img
                                src={medios.foto_empresa}
                                alt={t('tourist.profile.businessPhotoAlt', {
                                    name: nombreCompleto,
                                })}
                                className="mt-3 max-h-80 w-full rounded-2xl object-cover shadow-md ring-1 ring-wayna-100"
                            />
                        </div>
                    )}

                    <PerfilMediosEmprendedor
                        medios={medios}
                        nombreEmprendimiento={nombreCompleto}
                    />

                    {campanaActiva ? (
                        <BarraProgreso
                            porcentaje={progreso?.porcentaje ?? 0}
                            montoRecaudado={progreso?.monto_recaudado ?? 0}
                            meta={progreso?.meta ?? 0}
                            titulo={campanaActiva.titulo}
                        />
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
                        key={
                            campanasActivas.length > 0
                                ? campanasActivas.map((c) => c.id).join('-')
                                : 'sin-campana'
                        }
                        campanasActivas={campanasActivas}
                        tipoPagos={tipoPagos}
                    />
                </div>
            </section>

            <ChatWidget />
        </GuestLayout>
    );
}
