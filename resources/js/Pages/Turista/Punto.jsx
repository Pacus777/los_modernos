import GuestLayout from '@/Layouts/GuestLayout';
import { etiquetaTipoEmprendimiento } from '@/utils/tipoEmprendimiento';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import ChatWidget from '@/Components/Turista/ChatWidget';

/**
 * Página pública de punto físico.
 *
 * Se muestra cuando el turista escanea un QR de ubicación:
 * /punto/{slug}
 *
 * No usa fetch ni useEffect.
 * Los datos llegan desde Laravel por props de Inertia.
 */
export default function Punto({ punto, emprendedores = [] }) {
    const { t } = useTranslation();

    const obtenerFoto = (emprendedor) => {
        if (!emprendedor?.fotografia) {
            return null;
        }

        return `/storage/${emprendedor.fotografia}`;
    };

    const obtenerNombreCompleto = (emprendedor) => {
        return `${emprendedor.nombre || ''} ${emprendedor.apellidos || ''}`.trim();
    };

    return (
        <GuestLayout variant="full">
            <Head title={`${punto?.nombre || 'Punto Wayna'} — Wayna`} />

            <main className="min-h-screen bg-gradient-to-b from-wayna-50 via-white to-white">
                <section className="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
                    {/* Encabezado del punto */}
                    <div className="rounded-3xl border border-wayna-100 bg-white/90 p-6 shadow-sm sm:p-8">
                        <p className="text-xs font-bold uppercase tracking-[0.22em] text-wayna-600">
                            {t('punto.etiqueta', 'Punto Wayna')}
                        </p>

                        <h1 className="mt-2 text-3xl font-black tracking-tight text-wayna-950 sm:text-4xl">
                            {punto?.nombre}
                        </h1>

                        {punto?.ubicacion && (
                            <p className="mt-2 text-sm font-semibold text-wayna-700">
                                {punto.ubicacion}
                            </p>
                        )}

                        {punto?.descripcion && (
                            <p className="mt-4 max-w-3xl text-sm leading-relaxed text-stone-600 sm:text-base">
                                {punto.descripcion}
                            </p>
                        )}

                        <p className="mt-5 text-sm leading-relaxed text-stone-600">
                            {t(
                                'punto.instruccion',
                                'Elige un emprendedor para conocer su historia y realizar un aporte.'
                            )}
                        </p>
                    </div>

                    {/* Lista de emprendedores */}
                    <div className="mt-6">
                        <div className="mb-4 flex items-center justify-between">
                            <div>
                                <h2 className="text-xl font-black text-wayna-950">
                                    {t('punto.emprendedoresDisponibles', 'Emprendedores disponibles')}
                                </h2>
                                <p className="mt-1 text-sm text-stone-600">
                                    {t(
                                        'punto.descripcionLista',
                                        'Personas asociadas a este punto físico.'
                                    )}
                                </p>
                            </div>
                        </div>

                        {emprendedores.length === 0 && (
                            <div className="rounded-3xl border border-wayna-100 bg-white p-8 text-center shadow-sm">
                                <p className="text-sm font-semibold text-stone-700">
                                    {t(
                                        'punto.sinEmprendedores',
                                        'No hay emprendedores activos asociados a este punto por el momento.'
                                    )}
                                </p>
                            </div>
                        )}

                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {emprendedores.map((emprendedor) => {
                                const foto = obtenerFoto(emprendedor);
                                const nombreCompleto = obtenerNombreCompleto(emprendedor);
                                const tipoEtiqueta = etiquetaTipoEmprendimiento(
                                    emprendedor.tipo_emprendimiento,
                                );

                                return (
                                    <article
                                        key={emprendedor.id}
                                        className="flex flex-col overflow-hidden rounded-3xl border border-wayna-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                                    >
                                        <div className="aspect-[4/3] bg-wayna-50">
                                            {foto ? (
                                                <img
                                                    src={foto}
                                                    alt={nombreCompleto}
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-full w-full items-center justify-center bg-wayna-100 text-5xl font-black text-wayna-600">
                                                    {emprendedor.nombre?.charAt(0) || 'W'}
                                                </div>
                                            )}
                                        </div>

                                        <div className="flex flex-1 flex-col p-5">
                                            <p className="text-xs font-bold uppercase tracking-wide text-wayna-600">
                                                {t('punto.emprendedor', 'Emprendedor')}
                                            </p>

                                            <h3 className="mt-1 text-lg font-black text-wayna-950">
                                                {nombreCompleto}
                                            </h3>

                                            {tipoEtiqueta ? (
                                                <span className="mt-2 inline-flex w-fit rounded-full bg-wayna-100 px-2.5 py-0.5 text-xs font-bold text-wayna-800">
                                                    {tipoEtiqueta}
                                                </span>
                                            ) : null}

                                            <p className="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-stone-600">
                                                {emprendedor.descripcion ||
                                                    t(
                                                        'punto.sinDescripcion',
                                                        'Conoce su historia y apoya su meta.'
                                                    )}
                                            </p>

                                            <Link
                                                href={`/emprendedor/${emprendedor.id}`}
                                                className="mt-5 inline-flex items-center justify-center rounded-2xl bg-wayna-700 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-wayna-800"
                                            >
                                                {t('punto.verPerfil', 'Ver perfil completo')}
                                            </Link>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                    </div>
                </section>
            </main>

            return (
                <GuestLayout variant="full">
                    <Head title={`${punto?.nombre || 'Punto Wayna'} — Wayna`} />

                    <main className="min-h-screen bg-gradient-to-b from-wayna-50 via-white to-white">
                        <section className="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
                            {/* todo tu contenido actual */}
                        </section>
                    </main>

                    <ChatWidget />
                </GuestLayout>
            );

        </GuestLayout>
    );
}