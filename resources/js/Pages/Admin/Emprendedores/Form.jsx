import AdminBackLink from '@/Components/Admin/AdminBackLink';
import AdminFormStepActions from '@/Components/Admin/AdminFormStepActions';
import EmprendedorFormStepper, { PASOS_EMPRENDEDOR } from '@/Components/Admin/EmprendedorFormStepper';
import {
    adminBackdropTall,
    adminFileInputClass,
    adminInputClass,
    adminLabelField,
    adminLabelUpper,
    adminSectionCard,
    adminTextareaClass,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

function formatearBs(valor) {
    const n = Number(valor);
    if (Number.isNaN(n)) {
        return '0,00';
    }
    return n.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Formulario crear / editar emprendedor por pasos (T-A7).
 */
export default function Form({ modo, emprendedor }) {
    const esEdicion = modo === 'editar';
    const [paso, setPaso] = useState(1);
    const [erroresPaso, setErroresPaso] = useState({});

    const { data, setData, post, processing, errors, reset } = useForm({
        nombre: emprendedor?.nombre || '',
        apellidos: emprendedor?.apellidos || '',
        descripcion: emprendedor?.descripcion || '',
        estado: emprendedor?.estado || 'activo',
        meta_monto: emprendedor?.meta_monto ?? '',
        fotografia: null,
    });

    const fotografiaActual = emprendedor?.fotografia
        ? `/storage/${emprendedor.fotografia}`
        : null;

    const vistaPreviaNueva = useMemo(() => {
        if (data.fotografia instanceof File) {
            return URL.createObjectURL(data.fotografia);
        }
        return null;
    }, [data.fotografia]);

    useEffect(() => {
        return () => {
            if (vistaPreviaNueva) {
                URL.revokeObjectURL(vistaPreviaNueva);
            }
        };
    }, [vistaPreviaNueva]);

    useEffect(() => {
        if (errors.nombre || errors.apellidos) {
            setPaso(1);
        } else if (errors.descripcion || errors.fotografia) {
            setPaso(2);
        } else if (errors.estado || errors.meta_monto) {
            setPaso(3);
        }
    }, [errors]);

    const validarPaso = (numeroPaso) => {
        const locales = {};

        if (numeroPaso === 1) {
            if (!data.nombre.trim()) {
                locales.nombre = 'El nombre es obligatorio.';
            }
            if (!data.apellidos.trim()) {
                locales.apellidos = 'Los apellidos son obligatorios.';
            }
        }

        if (numeroPaso === 3) {
            if (data.meta_monto === '' || data.meta_monto === null) {
                locales.meta_monto = 'La meta económica es obligatoria.';
            } else if (Number(data.meta_monto) < 0) {
                locales.meta_monto = 'La meta no puede ser negativa.';
            }
        }

        setErroresPaso(locales);
        return Object.keys(locales).length === 0;
    };

    const irSiguiente = () => {
        if (!validarPaso(paso)) {
            return;
        }
        setPaso((p) => Math.min(PASOS_EMPRENDEDOR.length, p + 1));
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

        if (!validarPaso(1) || !validarPaso(3)) {
            if (!data.nombre.trim() || !data.apellidos.trim()) {
                setPaso(1);
            } else {
                setPaso(3);
            }
            return;
        }

        if (esEdicion) {
            router.post(
                route('admin.emprendedores.update', emprendedor.id),
                { ...data, _method: 'put' },
                { forceFormData: true, preserveScroll: true },
            );
            return;
        }

        post(route('admin.emprendedores.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => reset('fotografia'),
        });
    };

    const error = (campo) => erroresPaso[campo] || errors[campo];
    const fotoMostrar = vistaPreviaNueva || fotografiaActual;

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        {esEdicion ? 'Editar emprendedor' : 'Nuevo emprendedor'}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        {esEdicion
                            ? 'Completá el registro por pasos; los datos se conservan al avanzar o retroceder.'
                            : 'Registro guiado en 4 pasos. Al final confirmás y se genera el QR de perfil.'}
                    </p>
                </div>
            }
        >
            <Head title={esEdicion ? 'Editar emprendedor — Wayna' : 'Nuevo emprendedor — Wayna'} />

            <div className="relative py-8">
                <div className={adminBackdropTall} aria-hidden />

                <div className="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <AdminBackLink href={route('admin.emprendedores.index')} />

                    <div className="overflow-hidden rounded-3xl border border-wayna-200/90 bg-white shadow-2xl shadow-wayna-900/[0.08] ring-1 ring-black/[0.03]">
                        <div className="header-wayna-gradient px-6 py-5 sm:px-8">
                            <h3 className="text-lg font-bold text-white sm:text-xl">
                                {esEdicion ? 'Actualizar emprendedor' : 'Alta de emprendedor'}
                            </h3>
                            <p className="mt-1 text-sm text-orange-50/95">
                                Paso {paso} de {PASOS_EMPRENDEDOR.length}
                            </p>
                        </div>

                        <EmprendedorFormStepper pasoActual={paso} onSeleccionar={irAPaso} />

                        <form onSubmit={submit} encType="multipart/form-data">
                            <AdminFormStepActions
                                sticky
                                cancelHref={route('admin.emprendedores.index')}
                                paso={paso}
                                totalPasos={PASOS_EMPRENDEDOR.length}
                                processing={processing}
                                onAnterior={irAnterior}
                                onSiguiente={irSiguiente}
                                guardarLabel="Guardar"
                            />
                            <div className="min-h-[280px] bg-gradient-to-b from-white to-wayna-50/40 p-6 sm:p-8">
                                {paso === 1 && (
                                    <div className={adminSectionCard}>
                                        <p className={adminLabelUpper}>Datos básicos</p>
                                        <p className="mt-1 text-sm text-stone-600">
                                            Nombre y apellidos visibles en el panel y en el perfil
                                            público.
                                        </p>
                                        <div className="mt-4 grid gap-5 sm:grid-cols-2">
                                            <div>
                                                <label htmlFor="nombre" className={adminLabelField}>
                                                    Nombre
                                                </label>
                                                <input
                                                    id="nombre"
                                                    type="text"
                                                    value={data.nombre}
                                                    onChange={(e) =>
                                                        setData('nombre', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                    placeholder="Ej. Camila"
                                                />
                                                {error('nombre') && (
                                                    <p className="mt-2 text-sm font-medium text-red-600">
                                                        {error('nombre')}
                                                    </p>
                                                )}
                                            </div>
                                            <div>
                                                <label
                                                    htmlFor="apellidos"
                                                    className={adminLabelField}
                                                >
                                                    Apellidos
                                                </label>
                                                <input
                                                    id="apellidos"
                                                    type="text"
                                                    value={data.apellidos}
                                                    onChange={(e) =>
                                                        setData('apellidos', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                    placeholder="Ej. Sánchez López"
                                                />
                                                {error('apellidos') && (
                                                    <p className="mt-2 text-sm font-medium text-red-600">
                                                        {error('apellidos')}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {paso === 2 && (
                                    <div className={adminSectionCard}>
                                        <p className={adminLabelUpper}>Historia y fotografía</p>
                                        <p className="mt-1 text-sm text-stone-600">
                                            Contá la historia del emprendimiento y subí una foto
                                            para el perfil turista.
                                        </p>
                                        <div className="mt-4">
                                            <label
                                                htmlFor="descripcion"
                                                className={adminLabelField}
                                            >
                                                Descripción / historia
                                            </label>
                                            <textarea
                                                id="descripcion"
                                                rows={5}
                                                value={data.descripcion}
                                                onChange={(e) =>
                                                    setData('descripcion', e.target.value)
                                                }
                                                className={adminTextareaClass}
                                                placeholder="Historia o actividad que verá el turista en Wayna."
                                            />
                                            {error('descripcion') && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {error('descripcion')}
                                                </p>
                                            )}
                                        </div>
                                        <div className="mt-6">
                                            {fotoMostrar && (
                                                <div className="mb-4">
                                                    <p className="mb-2 text-xs font-bold uppercase tracking-wide text-wayna-800">
                                                        {vistaPreviaNueva
                                                            ? 'Vista previa'
                                                            : 'Foto actual'}
                                                    </p>
                                                    <img
                                                        src={fotoMostrar}
                                                        alt=""
                                                        className="h-36 w-36 rounded-2xl object-cover ring-2 ring-wayna-100 shadow-md"
                                                    />
                                                </div>
                                            )}
                                            <label
                                                htmlFor="fotografia"
                                                className={adminLabelField}
                                            >
                                                {esEdicion
                                                    ? 'Cambiar fotografía (opcional)'
                                                    : 'Fotografía (opcional)'}
                                            </label>
                                            <input
                                                id="fotografia"
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                onChange={(e) =>
                                                    setData(
                                                        'fotografia',
                                                        e.target.files?.[0] ?? null,
                                                    )
                                                }
                                                className={adminFileInputClass}
                                            />
                                            <p className="mt-2 text-xs text-stone-500">
                                                JPG, PNG o WEBP. Máximo 2 MB.
                                            </p>
                                            {error('fotografia') && (
                                                <p className="mt-2 text-sm font-medium text-red-600">
                                                    {error('fotografia')}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                )}

                                {paso === 3 && (
                                    <div className={adminSectionCard}>
                                        <p className={adminLabelUpper}>Meta y estado</p>
                                        <p className="mt-1 text-sm text-stone-600">
                                            Meta referencial de apoyo y visibilidad en el
                                            sistema.
                                        </p>
                                        <div className="mt-4 grid gap-5 sm:grid-cols-2">
                                            <div>
                                                <label htmlFor="estado" className={adminLabelField}>
                                                    Estado
                                                </label>
                                                <select
                                                    id="estado"
                                                    value={data.estado}
                                                    onChange={(e) =>
                                                        setData('estado', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                >
                                                    <option value="activo">
                                                        Activo (visible)
                                                    </option>
                                                    <option value="inactivo">Inactivo</option>
                                                </select>
                                                {error('estado') && (
                                                    <p className="mt-2 text-sm font-medium text-red-600">
                                                        {error('estado')}
                                                    </p>
                                                )}
                                            </div>
                                            <div>
                                                <label
                                                    htmlFor="meta_monto"
                                                    className={adminLabelField}
                                                >
                                                    Meta económica referencial (Bs)
                                                </label>
                                                <div className="relative mt-2">
                                                    <span className="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-wayna-600">
                                                        Bs
                                                    </span>
                                                    <input
                                                        id="meta_monto"
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        value={data.meta_monto}
                                                        onChange={(e) =>
                                                            setData('meta_monto', e.target.value)
                                                        }
                                                        className={`${adminInputClass} pl-11`}
                                                        placeholder="0.00"
                                                    />
                                                </div>
                                                {error('meta_monto') && (
                                                    <p className="mt-2 text-sm font-medium text-red-600">
                                                        {error('meta_monto')}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                        {esEdicion && emprendedor?.qr_url && (
                                            <div className="mt-5 rounded-2xl border border-wayna-200 bg-wayna-50/60 px-4 py-3 text-sm text-stone-600">
                                                El código QR de perfil ya existe y se conserva al
                                                guardar.
                                            </div>
                                        )}
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
                                                        Nombre completo
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold text-wayna-950">
                                                        {data.nombre} {data.apellidos}
                                                    </dd>
                                                </div>
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Historia
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm text-stone-700">
                                                        {data.descripcion?.trim() ||
                                                            '— Sin descripción —'}
                                                    </dd>
                                                </div>
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Fotografía
                                                    </dt>
                                                    <dd className="sm:col-span-2 flex items-center gap-3">
                                                        {fotoMostrar ? (
                                                            <img
                                                                src={fotoMostrar}
                                                                alt=""
                                                                className="h-16 w-16 rounded-xl object-cover ring-1 ring-wayna-200"
                                                            />
                                                        ) : null}
                                                        <span className="text-sm text-stone-600">
                                                            {data.fotografia
                                                                ? 'Nueva imagen seleccionada'
                                                                : fotografiaActual
                                                                  ? 'Se mantiene la actual'
                                                                  : 'Sin foto'}
                                                        </span>
                                                    </dd>
                                                </div>
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Estado
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold capitalize text-wayna-950">
                                                        {data.estado}
                                                    </dd>
                                                </div>
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Meta referencial
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold text-wayna-950">
                                                        Bs. {formatearBs(data.meta_monto)}
                                                    </dd>
                                                </div>
                                            </dl>
                                        </div>
                                        {!esEdicion && (
                                            <p className="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900">
                                                Al guardar se creará el emprendedor y se generará
                                                automáticamente su código QR de perfil.
                                            </p>
                                        )}
                                    </div>
                                )}
                            </div>

                            <AdminFormStepActions
                                cancelHref={route('admin.emprendedores.index')}
                                paso={paso}
                                totalPasos={PASOS_EMPRENDEDOR.length}
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
