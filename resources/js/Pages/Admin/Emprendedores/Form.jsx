import AdminBackLink from '@/Components/Admin/AdminBackLink';
import AdminFormField from '@/Components/Admin/AdminFormField';
import AdminFormStepActions from '@/Components/Admin/AdminFormStepActions';
import AdminEmprendedorMediosFields from '@/Components/Admin/AdminEmprendedorMediosFields';
import AdminEmprendedorRedesFields from '@/Components/Admin/AdminEmprendedorRedesFields';
import AdminEmprendedorVistaPreviaMedios from '@/Components/Admin/AdminEmprendedorVistaPreviaMedios';
import AdminFotografiaPerfilField from '@/Components/Admin/AdminFotografiaPerfilField';
import AdminResumenEmprendedor from '@/Components/Admin/AdminResumenEmprendedor';
import EmprendedorFormStepper, { PASOS_EMPRENDEDOR } from '@/Components/Admin/EmprendedorFormStepper';
import {
    adminBackdropTall,
    adminFormGrid2,
    adminFormStack,
    adminInputClass,
    adminInputMoneyWrap,
    adminLabelUpper,
    adminSectionCard,
    adminTextareaClass,
} from '@/Components/Admin/adminUi';
import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import AdminLayout from '@/Layouts/AdminLayout';
import { etiquetaDepartamento } from '@/utils/departamento';
import { etiquetaTipoEmprendimiento } from '@/utils/tipoEmprendimiento';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

/** Muestra solo los 8 dígitos locales (sin +591) en el formulario admin. */
function whatsappCelularLocal(valor) {
    const digitos = String(valor ?? '').replace(/\D/g, '');
    if (digitos.startsWith('591') && digitos.length >= 11) {
        return digitos.slice(3, 11);
    }
    if (digitos.length === 8) {
        return digitos;
    }
    return digitos.slice(0, 8);
}

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
export default function Form({
    modo,
    emprendedor,
    tiposEmprendimiento = [],
    departamentos = [],
}) {
    const { flash } = usePage().props;
    const esEdicion = modo === 'editar';
    const [paso, setPaso] = useState(1);
    const [erroresPaso, setErroresPaso] = useState({});
    const { data, setData, post, processing, errors, reset } = useForm({
        nombre: emprendedor?.nombre || '',
        apellidos: emprendedor?.apellidos || '',
        descripcion: emprendedor?.descripcion || '',
        tipo_emprendimiento:
            emprendedor?.tipo_emprendimiento?.value ??
            emprendedor?.tipo_emprendimiento ??
            '',
        departamento:
            emprendedor?.departamento?.value ?? emprendedor?.departamento ?? '',
        estado: emprendedor?.estado || 'activo',
        fotografia: null,
        foto_empresa: null,
        galeria: [],
        galeria_nuevas: [],
        galeria_conservar: Array.isArray(emprendedor?.galeria) ? [...emprendedor.galeria] : [],
        video: null,
        video_enlace:
            emprendedor?.video_url?.startsWith('http://') ||
            emprendedor?.video_url?.startsWith('https://')
                ? emprendedor.video_url
                : '',
        quitar_video: false,
        whatsapp: whatsappCelularLocal(emprendedor?.whatsapp),
        instagram: emprendedor?.instagram || '',
        facebook: emprendedor?.facebook || '',
        tiktok: emprendedor?.tiktok || '',
        sitio_web: emprendedor?.sitio_web || '',
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
        } else if (
            errors.descripcion ||
            errors.fotografia ||
            errors.foto_empresa ||
            errors.galeria ||
            errors.galeria_nuevas ||
            errors.video ||
            errors.video_enlace ||
            errors.tipo_emprendimiento ||
            errors.departamento
        ) {
            setPaso(2);
        } else if (errors.estado) {
            setPaso(1);
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
            if (!data.estado) {
                locales.estado = 'Debés elegir un estado.';
            }
        }

        if (numeroPaso === 2) {
            if (!data.tipo_emprendimiento) {
                locales.tipo_emprendimiento = 'Debés elegir el tipo de emprendimiento.';
            }
            if (!data.departamento) {
                locales.departamento = 'Debés elegir el departamento.';
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

        if (!validarPaso(1) || !validarPaso(2)) {
            if (
                !data.nombre.trim() ||
                !data.apellidos.trim() ||
                !data.estado
            ) {
                setPaso(1);
            } else {
                setPaso(2);
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
    const nombreCompleto = `${data.nombre} ${data.apellidos}`.trim();

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
                            : 'Registro en 3 pasos. Después definís la campaña (meta) y el acceso al panel.'}
                    </p>
                </div>
            }
        >
            <Head title={esEdicion ? 'Editar emprendedor — Wayna' : 'Nuevo emprendedor — Wayna'} />

            <div className="relative py-8">
                <div className={adminBackdropTall} aria-hidden />

                <div className="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <AdminBackLink href={route('admin.emprendedores.index')} />
                    <AdminFlashSuccess message={flash?.success} />
                    {flash?.error ? (
                        <div
                            className="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900"
                            role="alert"
                        >
                            {flash.error}
                        </div>
                    ) : null}

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
                                guardarLabel={esEdicion ? 'Guardar cambios' : 'Continuar al acceso'}
                                guardandoLabel={esEdicion ? 'Guardando…' : 'Guardando perfil…'}
                            />
                            <div className="min-h-[280px] bg-gradient-to-b from-white to-wayna-50/40 p-6 sm:p-8">
                                {paso >= 2 && nombreCompleto ? (
                                    <AdminResumenEmprendedor
                                        titulo="Emprendedor en registro"
                                        nombre={nombreCompleto}
                                        tipoEtiqueta={etiquetaTipoEmprendimiento(
                                            data.tipo_emprendimiento,
                                        )}
                                        departamentoEtiqueta={etiquetaDepartamento(
                                            data.departamento,
                                        )}
                                        descripcion={
                                            paso >= 2 ? data.descripcion?.trim() || null : null
                                        }
                                        onCambiar={() => {
                                            setErroresPaso({});
                                            setPaso(1);
                                        }}
                                        textoCambiar="Cambiar datos básicos"
                                    />
                                ) : null}
                                {paso === 1 && (
                                    <div className={adminSectionCard}>
                                        <p className={adminLabelUpper}>Datos básicos</p>
                                        <p className="mt-1 text-sm text-stone-600">
                                            Completá los campos marcados con{' '}
                                            <span className="font-bold text-wayna-600">*</span>.
                                        </p>
                                        <div className={`mt-4 ${adminFormGrid2}`}>
                                            <AdminFormField
                                                id="nombre"
                                                label="Nombre"
                                                required
                                                error={error('nombre')}
                                            >
                                                <input
                                                    id="nombre"
                                                    type="text"
                                                    required
                                                    value={data.nombre}
                                                    onChange={(e) =>
                                                        setData('nombre', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                    placeholder="Ej. Camila"
                                                />
                                            </AdminFormField>
                                            <AdminFormField
                                                id="apellidos"
                                                label="Apellidos"
                                                required
                                                error={error('apellidos')}
                                            >
                                                <input
                                                    id="apellidos"
                                                    type="text"
                                                    required
                                                    value={data.apellidos}
                                                    onChange={(e) =>
                                                        setData('apellidos', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                    placeholder="Ej. Sánchez López"
                                                />
                                            </AdminFormField>
                                            <AdminFormField
                                                id="estado"
                                                label="Estado en WAYNA"
                                                required
                                                error={error('estado')}
                                            >
                                                <select
                                                    id="estado"
                                                    required
                                                    value={data.estado}
                                                    onChange={(e) =>
                                                        setData('estado', e.target.value)
                                                    }
                                                    className={adminInputClass}
                                                >
                                                    <option value="activo">
                                                        Activo (visible para turistas)
                                                    </option>
                                                    <option value="inactivo">Inactivo</option>
                                                </select>
                                            </AdminFormField>
                                        </div>
                                        <p className="mt-4 rounded-xl border border-wayna-100 bg-wayna-50/60 px-4 py-3 text-xs text-stone-600">
                                            La <strong>meta de recaudación</strong> se configura en una{' '}
                                            <strong>campaña</strong> después del alta (o desde el panel del
                                            emprendedor).
                                        </p>
                                    </div>
                                )}

                                {paso === 2 && (
                                    <div className={adminSectionCard}>
                                        <p className={adminLabelUpper}>Perfil público</p>
                                        <p className="mt-1 text-sm text-stone-600">
                                            Completá los campos marcados con{' '}
                                            <span className="font-bold text-wayna-600">*</span>.
                                            La foto es opcional.
                                        </p>
                                        <div className={`mt-4 ${adminFormStack}`}>
                                            <div className={adminFormGrid2}>
                                                <AdminFormField
                                                    id="tipo_emprendimiento"
                                                    label="Tipo de emprendimiento o empresa"
                                                    required
                                                    hint="Clasificación para filtros y estadísticas."
                                                    error={error('tipo_emprendimiento')}
                                                >
                                                    <select
                                                        id="tipo_emprendimiento"
                                                        required
                                                        value={data.tipo_emprendimiento}
                                                        onChange={(e) =>
                                                            setData(
                                                                'tipo_emprendimiento',
                                                                e.target.value,
                                                            )
                                                        }
                                                        className={adminInputClass}
                                                    >
                                                        <option value="">
                                                            Seleccionar tipo…
                                                        </option>
                                                        {tiposEmprendimiento.map((opt) => (
                                                            <option
                                                                key={opt.value}
                                                                value={opt.value}
                                                            >
                                                                {opt.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </AdminFormField>
                                                <AdminFormField
                                                    id="departamento"
                                                    label="Departamento"
                                                    required
                                                    hint="Región para impacto nacional y reportes."
                                                    error={error('departamento')}
                                                >
                                                    <select
                                                        id="departamento"
                                                        required
                                                        value={data.departamento}
                                                        onChange={(e) =>
                                                            setData('departamento', e.target.value)
                                                        }
                                                        className={adminInputClass}
                                                    >
                                                        <option value="">
                                                            Seleccionar departamento…
                                                        </option>
                                                        {departamentos.map((opt) => (
                                                            <option
                                                                key={opt.value}
                                                                value={opt.value}
                                                            >
                                                                {opt.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </AdminFormField>
                                            </div>
                                            <AdminFormField
                                                id="descripcion"
                                                label="Descripción del emprendimiento"
                                                hint="Opcional. Qué produce o vende; lo verán los turistas en Wayna."
                                                error={error('descripcion')}
                                            >
                                                <textarea
                                                    id="descripcion"
                                                    rows={5}
                                                    value={data.descripcion}
                                                    onChange={(e) =>
                                                        setData('descripcion', e.target.value)
                                                    }
                                                    className={adminTextareaClass}
                                                    placeholder="Ej. Artesanías en madera y tejidos de la Chiquitanía."
                                                />
                                            </AdminFormField>
                                            <AdminFotografiaPerfilField
                                                fotografia={data.fotografia}
                                                setFotografia={(file) =>
                                                    setData('fotografia', file)
                                                }
                                                urlActual={fotografiaActual}
                                                error={error('fotografia')}
                                                esEdicion={esEdicion}
                                                nombreCompleto={nombreCompleto}
                                            />

                                            <AdminEmprendedorMediosFields
                                                data={data}
                                                setData={setData}
                                                emprendedor={emprendedor}
                                                error={error}
                                                esEdicion={esEdicion}
                                            />

                                            <AdminEmprendedorRedesFields
                                                data={data}
                                                setData={setData}
                                                error={error}
                                            />

                                            <AdminEmprendedorVistaPreviaMedios
                                                data={data}
                                                emprendedor={emprendedor}
                                                fotoPerfilUrl={fotoMostrar}
                                            />
                                        </div>
                                    </div>
                                )}

                                {esEdicion && paso === 2 ? (
                                    <div className="mb-4 space-y-3">
                                        {emprendedor?.qr_url ? (
                                            <div className="rounded-2xl border border-wayna-200 bg-wayna-50/60 px-4 py-3 text-sm text-stone-600">
                                                El código QR de perfil ya existe y se conserva al
                                                guardar.
                                            </div>
                                        ) : (
                                            <div className="rounded-2xl border border-amber-200 bg-amber-50/90 px-4 py-4 text-sm text-amber-900/90">
                                                <p className="font-semibold text-amber-950">
                                                    Código QR pendiente
                                                </p>
                                                <p className="mt-1">
                                                    En el listado, usá <strong>Ver QR</strong> para
                                                    generar el código.
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                ) : null}

                                {paso === 3 && (
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
                                                        Tipo
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold text-wayna-950">
                                                        {etiquetaTipoEmprendimiento(
                                                            data.tipo_emprendimiento,
                                                        ) || '—'}
                                                    </dd>
                                                </div>
                                                <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                                                    <dt className="text-xs font-bold uppercase text-wayna-700">
                                                        Departamento
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold text-wayna-950">
                                                        {etiquetaDepartamento(data.departamento) ||
                                                            '—'}
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
                                                        Estado
                                                    </dt>
                                                    <dd className="sm:col-span-2 text-sm font-semibold capitalize text-wayna-950">
                                                        {data.estado}
                                                    </dd>
                                                </div>
                                            </dl>
                                            <p className="mt-4 rounded-xl border border-wayna-100 bg-wayna-50/60 px-4 py-3 text-sm text-stone-600">
                                                Tras guardar podrás crear la{' '}
                                                <strong>campaña con la meta en Bs.</strong> antes de
                                                configurar el acceso del emprendedor.
                                            </p>
                                        </div>

                                        <AdminEmprendedorVistaPreviaMedios
                                            data={data}
                                            emprendedor={emprendedor}
                                            fotoPerfilUrl={fotoMostrar}
                                        />

                                        {!esEdicion && (
                                            <p className="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900">
                                                Al continuar se guardará el perfil y el QR. Luego elegís si
                                                creás la campaña (meta) y configurás el acceso al panel.
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
                                guardarLabel={esEdicion ? 'Guardar cambios' : 'Continuar al acceso'}
                                guardandoLabel={esEdicion ? 'Guardando…' : 'Guardando perfil…'}
                            />
                        </form>
                    </div>

                </div>
            </div>

        </AdminLayout>
    );
}

