import AdminBackLink from '@/Components/Admin/AdminBackLink';
import AdminFormStepActions from '@/Components/Admin/AdminFormStepActions';
import CampanaFormStepper, { PASOS_CAMPANA } from '@/Components/Admin/CampanaFormStepper';
import {
    adminBackdropTall,
    adminInputClass,
    adminLabelField,
    adminLabelUpper,
    adminSectionCard,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

function fechaParaInput(val) {
    if (!val) {
        return '';
    }
    return String(val).split('T')[0];
}

function formatearBs(valor) {
    const n = Number(valor);
    if (Number.isNaN(n)) {
        return '0,00';
    }
    return n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function etiquetaEstado(estado) {
    const map = {
        activa: 'Activa (visible en perfil público)',
        inactiva: 'Inactiva',
        finalizada: 'Finalizada',
    };
    return map[estado] ?? estado;
}

/**
 * Formulario crear / editar campaña por pasos.
 */
export default function Form({ modo, campana, emprendedores = [] }) {
    const esEdicion = modo === 'editar';
    const [paso, setPaso] = useState(1);
    const [erroresPaso, setErroresPaso] = useState({});

    const { data, setData, post, put, processing, errors } = useForm({
        emprendedor_id: campana?.emprendedor_id ?? '',
        titulo: campana?.titulo ?? '',
        meta_apoyo: campana?.meta_apoyo ?? '',
        fecha_inicio: fechaParaInput(campana?.fecha_inicio),
        fecha_fin: fechaParaInput(campana?.fecha_fin),
        estado: campana?.estado ?? 'activa',
    });

    const labelEmprendedor = useMemo(() => {
        const opt = emprendedores.find((e) => String(e.id) === String(data.emprendedor_id));
        return opt?.label ?? '—';
    }, [data.emprendedor_id, emprendedores]);

    useEffect(() => {
        if (errors.emprendedor_id) {
            setPaso(1);
        } else if (errors.titulo || errors.meta_apoyo) {
            setPaso(2);
        } else if (errors.fecha_inicio || errors.fecha_fin || errors.estado) {
            setPaso(3);
        }
    }, [errors]);

    const validarPaso = (numeroPaso) => {
        const locales = {};

        if (numeroPaso === 1) {
            if (!data.emprendedor_id) {
                locales.emprendedor_id = 'Debés elegir un emprendedor.';
            }
        }

        if (numeroPaso === 2) {
            if (!data.titulo.trim()) {
                locales.titulo = 'El título de la campaña es obligatorio.';
            }
            if (data.meta_apoyo === '' || data.meta_apoyo === null) {
                locales.meta_apoyo = 'La meta de apoyo es obligatoria.';
            } else if (Number(data.meta_apoyo) < 0.01) {
                locales.meta_apoyo = 'La meta debe ser mayor a cero.';
            }
        }

        if (numeroPaso === 3) {
            if (data.fecha_inicio && data.fecha_fin) {
                if (new Date(data.fecha_fin) < new Date(data.fecha_inicio)) {
                    locales.fecha_fin =
                        'La fecha de fin debe ser igual o posterior a la de inicio.';
                }
            }
        }

        setErroresPaso(locales);
        return Object.keys(locales).length === 0;
    };

    const irSiguiente = () => {
        if (!validarPaso(paso)) {
            return;
        }
        setPaso((p) => Math.min(PASOS_CAMPANA.length, p + 1));
    };

    const irAnterior = () => {
        setErroresPaso({});
        setPaso((p) => Math.max(1, p - 1));
    };

    const irAPaso = (destino) => {
        if (destino < paso) {
            setErroresPaso({});
            setPaso(destino);
            return;
        }
        for (let p = paso; p < destino; p++) {
            if (!validarPaso(p)) {
                setPaso(p);
                return;
            }
        }
        setPaso(destino);
    };

    const submit = (e) => {
        e.preventDefault();

        if (!validarPaso(1) || !validarPaso(2) || !validarPaso(3)) {
            if (!data.emprendedor_id) {
                setPaso(1);
            } else if (!data.titulo.trim() || data.meta_apoyo === '' || Number(data.meta_apoyo) < 0.01) {
                setPaso(2);
            } else {
                setPaso(3);
            }
            return;
        }

        if (esEdicion) {
            put(route('admin.campanas.update', campana.id), {
                preserveScroll: true,
            });
            return;
        }
        post(route('admin.campanas.store'), {
            preserveScroll: true,
        });
    };

    const error = (campo) => erroresPaso[campo] || errors[campo];

    const fechasResumen =
        data.fecha_inicio || data.fecha_fin
            ? `${data.fecha_inicio || '—'} → ${data.fecha_fin || '—'}`
            : 'Sin fechas definidas';

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        {esEdicion ? 'Editar campaña' : 'Nueva campaña'}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        {esEdicion
                            ? 'Actualizá la campaña por pasos; los datos se conservan al avanzar o retroceder.'
                            : 'Registro guiado en 4 pasos. Al final confirmás y se crea la campaña.'}
                    </p>
                </div>
            }
        >
            <Head title={esEdicion ? 'Editar campaña — Wayna' : 'Nueva campaña — Wayna'} />

            <div className="relative py-8">
                <div className={adminBackdropTall} aria-hidden />

                <div className="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <AdminBackLink href={route('admin.campanas.index')} />

                    <div className="overflow-hidden rounded-3xl border border-wayna-200/90 bg-white shadow-2xl shadow-wayna-900/[0.08] ring-1 ring-black/[0.03]">
                        <div className="header-wayna-gradient px-6 py-5 sm:px-8">
                            <h3 className="text-lg font-bold text-white sm:text-xl">
                                {esEdicion ? 'Actualizar campaña' : 'Alta de campaña'}
                            </h3>
                            <p className="mt-1 text-sm text-orange-50/95">
                                Paso {paso} de {PASOS_CAMPANA.length}
                            </p>
                        </div>

                        <CampanaFormStepper pasoActual={paso} onSeleccionar={irAPaso} />

                        <form onSubmit={submit}>
                            <AdminFormStepActions
                                sticky
                                cancelHref={route('admin.campanas.index')}
                                paso={paso}
                                totalPasos={PASOS_CAMPANA.length}
                                processing={processing}
                                onAnterior={irAnterior}
                                onSiguiente={irSiguiente}
                                guardarLabel="Guardar"
                            />
                            <div className="min-h-[260px] bg-gradient-to-b from-white to-wayna-50/40 p-6 sm:p-8">
                                {paso === 1 && (
                                    <div className={adminSectionCard}>
                                        <p className={adminLabelUpper}>Vinculación</p>
                                        <p className="mt-1 text-sm text-stone-600">
                                            Elegí el emprendedor al que pertenece esta campaña de
                                            apoyo.
                                        </p>
                                        <div className="mt-4 space-y-5">
                                            <div>
                                                <label
                                                    htmlFor="emprendedor_id"
                                                    className={adminLabelField}
                                                >
                                                    Emprendedor
                                                </label>
                                                <select
                                                    id="emprendedor_id"
                                                    value={data.emprendedor_id}
                                                    onChange={(e) =>
                                                        setData('emprendedor_id', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                >
                                                    <option value="">
                                                        Seleccionar emprendedor…
                                                    </option>
                                                    {emprendedores.map((opt) => (
                                                        <option key={opt.id} value={opt.id}>
                                                            {opt.label}
                                                        </option>
                                                    ))}
                                                </select>
                                                {error('emprendedor_id') && (
                                                    <p className="mt-2 text-sm font-medium text-red-600">
                                                        {error('emprendedor_id')}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="rounded-xl border border-wayna-200/80 bg-gradient-to-r from-wayna-50 to-surface-muted/60 px-4 py-3 text-sm leading-relaxed text-wayna-950">
                                                <span className="font-bold text-wayna-800">
                                                    Regla Wayna:
                                                </span>{' '}
                                                solo puede haber{' '}
                                                <span className="font-semibold">
                                                    una campaña activa
                                                </span>{' '}
                                                por emprendedor. Si ya existe una, finalizala o
                                                desactivala antes de activar otra.
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {paso === 2 && (
                                    <div className={adminSectionCard}>
                                        <p className={adminLabelUpper}>Título y meta</p>
                                        <p className="mt-1 text-sm text-stone-600">
                                            Lo que verán los turistas en el perfil y en el punto
                                            de donación.
                                        </p>
                                        <div className="mt-4 space-y-5">
                                            <div>
                                                <label htmlFor="titulo" className={adminLabelField}>
                                                    Título público
                                                </label>
                                                <input
                                                    id="titulo"
                                                    type="text"
                                                    value={data.titulo}
                                                    onChange={(e) =>
                                                        setData('titulo', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                    placeholder="Ej. Apoyo a artesanías de la Chiquitanía"
                                                />
                                                {error('titulo') && (
                                                    <p className="mt-2 text-sm font-medium text-red-600">
                                                        {error('titulo')}
                                                    </p>
                                                )}
                                            </div>

                                            <div>
                                                <label
                                                    htmlFor="meta_apoyo"
                                                    className={adminLabelField}
                                                >
                                                    Meta de apoyo (Bs)
                                                </label>
                                                <div className="relative mt-2">
                                                    <span className="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-wayna-600">
                                                        Bs
                                                    </span>
                                                    <input
                                                        id="meta_apoyo"
                                                        type="number"
                                                        min="0.01"
                                                        step="0.01"
                                                        value={data.meta_apoyo}
                                                        onChange={(e) =>
                                                            setData('meta_apoyo', e.target.value)
                                                        }
                                                        className={`${adminInputClass} pl-11`}
                                                        placeholder="0.00"
                                                    />
                                                </div>
                                                {error('meta_apoyo') && (
                                                    <p className="mt-2 text-sm font-medium text-red-600">
                                                        {error('meta_apoyo')}
                                                    </p>
                                                )}
                                            </div>

                                            {esEdicion && campana && (
                                                <div className="flex flex-col gap-2 rounded-xl border border-wayna-200 bg-wayna-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                                    <div>
                                                        <p className="text-xs font-bold uppercase tracking-wide text-wayna-800">
                                                            Recaudado validado
                                                        </p>
                                                        <p className="mt-1 text-xs text-stone-600">
                                                            Se actualiza solo con donaciones
                                                            validadas.
                                                        </p>
                                                    </div>
                                                    <p className="font-mono text-xl font-bold tabular-nums text-wayna-900">
                                                        Bs.{' '}
                                                        {formatearBs(campana.monto_recaudado)}
                                                    </p>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {paso === 3 && (
                                    <div className={adminSectionCard}>
                                        <p className={adminLabelUpper}>Fechas y estado</p>
                                        <p className="mt-1 text-sm text-stone-600">
                                            Las fechas son opcionales. El estado define si la
                                            campaña es visible para turistas.
                                        </p>
                                        <div className="mt-4 grid gap-5 sm:grid-cols-2">
                                            <div>
                                                <label
                                                    htmlFor="fecha_inicio"
                                                    className={adminLabelField}
                                                >
                                                    Inicio (opcional)
                                                </label>
                                                <input
                                                    id="fecha_inicio"
                                                    type="date"
                                                    value={data.fecha_inicio}
                                                    onChange={(e) =>
                                                        setData('fecha_inicio', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                />
                                                {error('fecha_inicio') && (
                                                    <p className="mt-2 text-sm font-medium text-red-600">
                                                        {error('fecha_inicio')}
                                                    </p>
                                                )}
                                            </div>
                                            <div>
                                                <label
                                                    htmlFor="fecha_fin"
                                                    className={adminLabelField}
                                                >
                                                    Fin (opcional)
                                                </label>
                                                <input
                                                    id="fecha_fin"
                                                    type="date"
                                                    value={data.fecha_fin}
                                                    onChange={(e) =>
                                                        setData('fecha_fin', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                />
                                                {error('fecha_fin') && (
                                                    <p className="mt-2 text-sm font-medium text-red-600">
                                                        {error('fecha_fin')}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                        <div className="mt-5">
                                            <label htmlFor="estado" className={adminLabelField}>
                                                Visibilidad para turistas
                                            </label>
                                            <select
                                                id="estado"
                                                value={data.estado}
                                                onChange={(e) => setData('estado', e.target.value)}
                                                className={adminInputClass}
                                            >
                                                <option value="activa">
                                                    Activa (visible en perfil público)
                                                </option>
                                                <option value="inactiva">Inactiva</option>
                                                <option value="finalizada">Finalizada</option>
                                            </select>
                                            {error('estado') && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {error('estado')}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {paso === 4 && (
                                    <div className="space-y-4">
                                        <div className={adminSectionCard}>
                                            <p className={adminLabelUpper}>
                                                Revisá antes de guardar
                                            </p>
                                            <p className="mt-1 text-sm text-stone-600">
                                                Confirmá que los datos son correctos. Podés volver
                                                atrás para corregir sin perder lo ingresado.
                                            </p>
                                            <dl className="mt-5 divide-y divide-wayna-100 rounded-xl border border-wayna-100 bg-white">
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Emprendedor
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold text-wayna-950">
                                                        {labelEmprendedor}
                                                    </dd>
                                                </div>
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Título
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold text-wayna-950">
                                                        {data.titulo}
                                                    </dd>
                                                </div>
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Meta de apoyo
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold text-wayna-950">
                                                        Bs. {formatearBs(data.meta_apoyo)}
                                                    </dd>
                                                </div>
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Fechas
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm text-stone-700">
                                                        {fechasResumen}
                                                    </dd>
                                                </div>
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Estado
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold text-wayna-950">
                                                        {etiquetaEstado(data.estado)}
                                                    </dd>
                                                </div>
                                                {esEdicion && campana && (
                                                    <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                        <dt className="text-xs font-bold uppercase text-wayna-700">
                                                            Recaudado
                                                        </dt>
                                                        <dd className="sm:col-span-2 text-sm font-semibold text-wayna-950">
                                                            Bs.{' '}
                                                            {formatearBs(campana.monto_recaudado)}{' '}
                                                            <span className="font-normal text-stone-500">
                                                                (solo lectura)
                                                            </span>
                                                        </dd>
                                                    </div>
                                                )}
                                            </dl>
                                        </div>
                                        {data.estado === 'activa' && (
                                            <p className="rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-950">
                                                Al guardar como <strong>activa</strong>, el sistema
                                                verificará que no exista otra campaña activa para
                                                este emprendedor.
                                            </p>
                                        )}
                                    </div>
                                )}
                            </div>

                            <AdminFormStepActions
                                cancelHref={route('admin.campanas.index')}
                                paso={paso}
                                totalPasos={PASOS_CAMPANA.length}
                                processing={processing}
                                onAnterior={irAnterior}
                                onSiguiente={irSiguiente}
                                guardarLabel="Guardar"
                            />
                        </form>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
