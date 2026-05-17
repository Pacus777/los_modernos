import { etiquetaDepartamentoT, etiquetaTipoEmprendimientoT } from '@/utils/catalogosI18n';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

function StatColumn({ value, label, icon }) {
    return (
        <div className="flex flex-1 flex-col items-center text-center">
            <span className="text-sm font-black tabular-nums text-stone-900 sm:text-lg md:text-xl">
                {value}
            </span>
            <span className="mt-0.5 flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-stone-500">
                {icon}
                <span className="sr-only">{label}</span>
            </span>
        </div>
    );
}

function IconCamera() {
    return (
        <svg className="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path d="M4 7h3l2-3h6l2 3h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2z" />
            <circle cx="12" cy="13" r="3.5" />
        </svg>
    );
}

function IconCoin() {
    return (
        <svg className="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v10M9 10h4a2 2 0 1 1 0 4h-2" />
        </svg>
    );
}

function IconTarget() {
    return (
        <svg className="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <circle cx="12" cy="12" r="9" />
            <circle cx="12" cy="12" r="4" />
            <circle cx="12" cy="12" r="1" fill="currentColor" />
        </svg>
    );
}

const REDES_ICONOS = [
    {
        key: 'whatsapp',
        className: 'bg-emerald-600 hover:bg-emerald-700',
        labelKey: 'tourist.profile.socialWhatsApp',
        path: 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z',
    },
    {
        key: 'instagram',
        className: 'bg-gradient-to-br from-purple-600 to-pink-500 hover:opacity-90',
        labelKey: 'tourist.profile.socialInstagram',
        path: 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z',
    },
    {
        key: 'facebook',
        className: 'bg-blue-700 hover:bg-blue-800',
        labelKey: 'tourist.profile.socialFacebook',
        path: 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
    },
    {
        key: 'tiktok',
        className: 'bg-stone-900 hover:bg-black',
        labelKey: 'tourist.profile.socialTikTok',
        path: 'M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z',
    },
];

function contarMedios(medios) {
    let n = (medios?.galeria ?? []).length;
    if (medios?.foto_empresa) {
        n += 1;
    }
    if (medios?.video?.tipo && medios.video.tipo !== 'none') {
        n += 1;
    }
    return n;
}

function formatearBs(monto) {
    const n = Number(monto);
    if (!Number.isFinite(n) || n <= 0) {
        return '0';
    }
    if (n >= 1000) {
        return `${(n / 1000).toFixed(1).replace(/\.0$/, '')}k`;
    }
    return n.toFixed(0);
}

export default function PerfilCabeceraInsta({
    emprendedor,
    medios,
    redes,
    progreso,
    onDonar,
}) {
    const { t } = useTranslation();

    const nombreCompleto = `${emprendedor.nombre ?? ''} ${emprendedor.apellidos ?? ''}`.trim();
    const fotoUrl = emprendedor.foto_portada ?? null;
    const totalMedios = contarMedios(medios);
    const porcentaje = Math.round(Number(progreso?.porcentaje ?? 0));

    const redesActivas = useMemo(
        () => REDES_ICONOS.filter(({ key }) => Boolean(redes?.[key])),
        [redes],
    );

    return (
        <header className="px-4 pb-4 pt-5 sm:px-5">
            <div className="flex flex-col gap-4 min-[400px]:flex-row min-[400px]:items-center sm:gap-6">
                <div className="perfil-ig-avatar mx-auto shrink-0 min-[400px]:mx-0">
                    {fotoUrl ? (
                        <img src={fotoUrl} alt="" className="h-full w-full object-cover" />
                    ) : (
                        <span className="flex h-full w-full items-center justify-center bg-wayna-100 text-2xl font-black text-wayna-600">
                            {(emprendedor.nombre?.[0] ?? '?').toUpperCase()}
                        </span>
                    )}
                </div>

                <div className="flex w-full min-w-0 flex-1 justify-between gap-1 min-[400px]:justify-around min-[400px]:gap-2">
                    <StatColumn
                        value={totalMedios}
                        label={t('tourist.profile.statPosts')}
                        icon={<IconCamera />}
                    />
                    <StatColumn
                        value={`Bs ${formatearBs(progreso?.monto_recaudado)}`}
                        label={t('tourist.profile.statRaised')}
                        icon={<IconCoin />}
                    />
                    <StatColumn
                        value={`${porcentaje}%`}
                        label={t('tourist.profile.statGoal')}
                        icon={<IconTarget />}
                    />
                </div>
            </div>

            <div className="mt-4">
                <h1 className="text-base font-bold text-stone-900">{nombreCompleto}</h1>
                <div className="mt-1.5 flex flex-wrap gap-1.5">
                    {(emprendedor.tipo_emprendimiento || emprendedor.tipo_emprendimiento_etiqueta) ? (
                        <span className="rounded-full bg-wayna-100 px-2.5 py-0.5 text-xs font-bold text-wayna-800">
                            {etiquetaTipoEmprendimientoT(t, emprendedor.tipo_emprendimiento) ??
                                emprendedor.tipo_emprendimiento_etiqueta}
                        </span>
                    ) : null}
                    {(emprendedor.departamento || emprendedor.departamento_etiqueta) ? (
                        <span className="rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-bold text-stone-600">
                            {etiquetaDepartamentoT(t, emprendedor.departamento) ??
                                emprendedor.departamento_etiqueta}
                        </span>
                    ) : null}
                </div>
                {emprendedor.descripcion ? (
                    <p className="mt-2 text-sm leading-relaxed text-stone-600 line-clamp-4">
                        {emprendedor.descripcion}
                    </p>
                ) : null}
            </div>

            <div className="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
                <button
                    type="button"
                    onClick={onDonar}
                    className="btn-wayna-primary flex-1 rounded-xl py-3 text-sm font-bold shadow-md transition hover:brightness-105 active:scale-[0.99]"
                >
                    {t('tourist.profile.donateButton')}
                </button>
                {redesActivas.length > 0 && (
                    <div className="flex gap-2 sm:shrink-0">
                        {redesActivas.map(({ key, className, labelKey, path }) => (
                            <a
                                key={key}
                                href={redes[key]}
                                target="_blank"
                                rel="noopener noreferrer"
                                className={`flex h-11 w-11 items-center justify-center rounded-xl text-white shadow-md transition ${className}`}
                                aria-label={t(labelKey)}
                            >
                                <svg className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden>
                                    <path d={path} />
                                </svg>
                            </a>
                        ))}
                    </div>
                )}
            </div>
        </header>
    );
}

