import GuestLayout from '@/Layouts/GuestLayout';
import ChatWidget from '@/Components/Turista/ChatWidget';
import { etiquetaDepartamentoT, etiquetaTipoEmprendimientoT } from '@/utils/catalogosI18n';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

/**
 * Página pública de punto físico.
 *
 * Se muestra cuando el turista escanea un QR de ubicación:
 * /punto/{slug}
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

    const tituloPagina = punto?.nombre
        ? t('punto.headTitle', { name: punto.nombre })
        : t('punto.headTitleFallback');

    return (
        <GuestLayout variant="full" contentClassName="!max-w-5xl sm:!max-w-5xl lg:!max-w-5xl">
            <Head title={tituloPagina} />

            <section className="overflow-hidden rounded-3xl border border-wayna-100 bg-white/90 shadow-sm">
                <div className="p-5 sm:p-8">
                    <p className="text-xs font-bold uppercase tracking-[0.22em] text-wayna-600">
                        {t('punto.etiqueta')}
                    </p>

                    <h1 className="mt-2 text-2xl font-black tracking-tight text-wayna-950 sm:text-4xl">
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
                        {t('punto.instruccion')}
                    </p>
                </div>

                <div className="border-t border-wayna-100 px-5 pb-6 pt-5 sm:px-8 sm:pb-8">
                    <div className="mb-4">
                        <h2 className="text-lg font-black text-wayna-950 sm:text-xl">
                            {t('punto.emprendedoresDisponibles')}
                        </h2>
                        <p className="mt-1 text-sm text-stone-600">
                            {t('punto.descripcionLista')}
                        </p>
                    </div>

                    {emprendedores.length === 0 && (
                        <div className="rounded-2xl border border-wayna-100 bg-wayna-50/50 p-6 text-center">
                            <p className="text-sm font-semibold text-stone-700">
                                {t('punto.sinEmprendedores')}
                            </p>
                        </div>
                    )}

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {emprendedores.map((emprendedor) => {
                            const foto = obtenerFoto(emprendedor);
                            const nombreCompleto = obtenerNombreCompleto(emprendedor);
                            const tipoEtiqueta = etiquetaTipoEmprendimientoT(
                                t,
                                emprendedor.tipo_emprendimiento,
                            );
                            const deptoEtiqueta = etiquetaDepartamentoT(
                                t,
                                emprendedor.departamento,
                            );

                            return (
                                <article
                                    key={emprendedor.id}
                                    className="flex flex-col overflow-hidden rounded-2xl border border-wayna-100 bg-white shadow-sm transition hover:shadow-md"
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

                                    <div className="flex flex-1 flex-col p-4 sm:p-5">
                                        <p className="text-xs font-bold uppercase tracking-wide text-wayna-600">
                                            {t('punto.emprendedor')}
                                        </p>

                                        <h3 className="mt-1 text-lg font-black text-wayna-950">
                                            {nombreCompleto}
                                        </h3>

                                        {tipoEtiqueta || deptoEtiqueta ? (
                                            <div className="mt-2 flex flex-wrap gap-1.5">
                                                {tipoEtiqueta ? (
                                                    <span className="inline-flex w-fit rounded-full bg-wayna-100 px-2.5 py-0.5 text-xs font-bold text-wayna-800">
                                                        {tipoEtiqueta}
                                                    </span>
                                                ) : null}
                                                {deptoEtiqueta ? (
                                                    <span className="inline-flex w-fit rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-bold text-stone-800">
                                                        {deptoEtiqueta}
                                                    </span>
                                                ) : null}
                                            </div>
                                        ) : null}

                                        <p className="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-stone-600">
                                            {emprendedor.descripcion || t('punto.sinDescripcion')}
                                        </p>

                                        <Link
                                            href={`/emprendedor/${emprendedor.id}`}
                                            className="touch-target mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-2xl bg-wayna-700 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-wayna-800 active:scale-[0.99]"
                                        >
                                            {t('punto.verPerfil')}
                                        </Link>
                                    </div>
                                </article>
                            );
                        })}
                    </div>
                </div>
            </section>

            <ChatWidget />
        </GuestLayout>
    );
}

