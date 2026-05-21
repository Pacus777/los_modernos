import WaynaEnterTransition from '@/Components/Wayna/WaynaEnterTransition';
import GuestLayout from '@/Layouts/GuestLayout';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import DonacionForm from '@/Components/Turista/DonacionForm';
import BarraProgreso from '@/Components/Turista/BarraProgreso';
import PerfilCabeceraInsta from '@/Components/Turista/PerfilCabeceraInsta';
import PerfilGaleriaGrid from '@/Components/Turista/PerfilGaleriaGrid';
import SeguirEmprendedorCard from '@/Components/Turista/SeguirEmprendedorCard';
import { Head, Link, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

function scrollToDonar() {
    document.getElementById('donar')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function ContenidoPerfil({
    emprendedor,
    medios,
    redes,
    campanaActiva,
    campanasActivas,
    progreso,
    tipoPagos,
    visitanteNombrePrefill,
    seguimiento,
    mostrarVolverExplorar,
    esPerfilPropio,
    volverHref,
    volverTexto,
}) {
    const { t } = useTranslation();
    const nombreCompleto = `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim();

    return (
        <>
            {mostrarVolverExplorar ? (
                <p className="mb-3">
                    <Link
                        href={volverHref}
                        className="inline-flex items-center gap-1 text-sm font-bold text-wayna-700 underline decoration-wayna-300 underline-offset-4 hover:text-wayna-900"
                    >
                        ← {volverTexto}
                    </Link>
                </p>
            ) : null}

            <article className="perfil-ig overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-lg shadow-wayna-900/5">
                <PerfilCabeceraInsta
                    emprendedor={emprendedor}
                    medios={medios}
                    redes={redes}
                    progreso={progreso}
                    onDonar={scrollToDonar}
                />

                {campanaActiva ? (
                    <div className="border-t border-stone-100 px-4 pb-4 sm:px-5">
                        <BarraProgreso
                            porcentaje={progreso?.porcentaje ?? 0}
                            montoRecaudado={progreso?.monto_recaudado ?? 0}
                            meta={progreso?.meta ?? 0}
                            titulo={campanaActiva.titulo}
                        />
                    </div>
                ) : Number(progreso?.meta ?? 0) > 0 ? (
                    <div className="mx-4 mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 sm:mx-5">
                        <p className="text-xs font-semibold text-amber-900">
                            {t('tourist.profile.referenceMetaNote')}: {t('common.currencyBs')}{' '}
                            {Number(progreso.meta).toFixed(2)}
                        </p>
                    </div>
                ) : null}

                <PerfilGaleriaGrid medios={medios} nombreEmprendimiento={nombreCompleto} />
            </article>

            {!esPerfilPropio ? (
                <SeguirEmprendedorCard
                    emprendedorId={emprendedor.id}
                    seguimiento={seguimiento}
                />
            ) : null}

            <section
                id="donar"
                className="scroll-mt-24 mt-8 rounded-2xl border border-wayna-200 bg-white p-5 shadow-lg shadow-wayna-900/5 sm:p-6"
            >
                <header className="mb-5 border-b border-wayna-100 pb-4">
                    <h2 className="text-xl font-black text-wayna-950">
                        {t('tourist.profile.supportSectionTitle')}
                    </h2>
                    <p className="mt-1 text-sm text-stone-600">
                        {t('tourist.profile.supportSectionSubtitle')}
                    </p>
                </header>

                {campanasActivas.length === 0 ? (
                    <div className="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <h3 className="text-base font-bold text-amber-950">
                            {t('tourist.profile.noCampaignTitle')}
                        </h3>
                        <p className="mt-2 text-sm leading-relaxed text-amber-900/90">
                            {t('tourist.profile.noCampaignBody')}
                        </p>
                    </div>
                ) : (
                    <DonacionForm
                        key={campanasActivas.map((c) => c.id).join('-')}
                        campanasActivas={campanasActivas}
                        tipoPagos={tipoPagos}
                        visitanteNombrePrefill={visitanteNombrePrefill}
                    />
                )}
            </section>
        </>
    );
}

export default function Perfil({
    emprendedor,
    medios,
    redes,
    campanaActiva,
    campanasActivas = [],
    progreso,
    tipoPagos,
    visitanteNombrePrefill = '',
    seguimiento = {},
    esPerfilPropio = false,
}) {
    const { t } = useTranslation();
    const { auth } = usePage().props;
    const esEmprendedor = auth?.role === 'emprendedor';

    const nombreCompleto = `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim();

    const contenido = (
        <ContenidoPerfil
            emprendedor={emprendedor}
            medios={medios}
            redes={redes}
            campanaActiva={campanaActiva}
            campanasActivas={campanasActivas}
            progreso={progreso}
            tipoPagos={tipoPagos}
            visitanteNombrePrefill={visitanteNombrePrefill}
            seguimiento={seguimiento}
            esPerfilPropio={esPerfilPropio}
            mostrarVolverExplorar={!esEmprendedor}
            volverHref="/"
            volverTexto={t('explore.backToExplore')}
        />
    );

    if (esEmprendedor) {
        return (
            <WaynaEnterTransition variant="navigate">
                <EmprendedorLayout
                    chatContextEmprendedorId={emprendedor.id}
                    contentClassName="mx-auto w-full max-w-lg px-4 py-6 sm:max-w-xl sm:px-6 lg:max-w-2xl"
                    header={
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                                Vista previa
                            </p>
                            <h2 className="mt-1 text-2xl font-bold text-wayna-950">
                                Tu perfil público
                            </h2>
                            <p className="mt-1 text-sm text-stone-600">{nombreCompleto}</p>
                        </div>
                    }
                >
                    <Head title={t('tourist.profile.headTitle', { name: nombreCompleto })} />
                    {contenido}
                </EmprendedorLayout>
            </WaynaEnterTransition>
        );
    }

    return (
        <WaynaEnterTransition variant="navigate">
            <GuestLayout
                variant="full"
                contentClassName="max-w-lg sm:max-w-xl lg:max-w-2xl"
                chatContextEmprendedorId={emprendedor.id}
            >
                <Head title={t('tourist.profile.headTitle', { name: nombreCompleto })} />
                {contenido}
            </GuestLayout>
        </WaynaEnterTransition>
    );
}
