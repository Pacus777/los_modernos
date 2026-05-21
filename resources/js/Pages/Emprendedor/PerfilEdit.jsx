import AdminEmprendedorMediosFields from '@/Components/Admin/AdminEmprendedorMediosFields';
import AdminEmprendedorRedesFields from '@/Components/Admin/AdminEmprendedorRedesFields';
import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import AdminFormField from '@/Components/Admin/AdminFormField';
import AdminFotografiaPerfilField from '@/Components/Admin/AdminFotografiaPerfilField';
import {
    adminFormGrid2,
    adminFormStack,
    adminInputClass,
    adminPrimaryGradientBtn,
    adminSectionCard,
    adminTextareaClass,
} from '@/Components/Admin/adminUi';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

import OnboardingChecklist from '@/Components/Emprendedor/OnboardingChecklist';

/** Muestra solo los 8 dígitos locales (sin +591). */
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

/**
 * E-06 — Edición del perfil público por el emprendedor.
 */
export default function PerfilEdit({
    emprendedor,
    tiposEmprendimiento = [],
    departamentos = [],
    perfil_publico_url,
}) {
    const { flash } = usePage().props;

    const { data, setData, processing, errors } = useForm({
        nombre: emprendedor?.nombre || '',
        apellidos: emprendedor?.apellidos || '',
        descripcion: emprendedor?.descripcion || '',
        tipo_emprendimiento:
            emprendedor?.tipo_emprendimiento?.value ??
            emprendedor?.tipo_emprendimiento ??
            '',
        departamento:
            emprendedor?.departamento?.value ?? emprendedor?.departamento ?? '',
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

    const error = (campo) => errors[campo];
    const nombreCompleto = `${data.nombre} ${data.apellidos}`.trim();

    const enviar = (e) => {
        e.preventDefault();
        router.post(route('emprendedor.perfil.update'), { ...data, _method: 'put' }, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return (
        <EmprendedorLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Perfil público
                    </p>
                    <h2 className="mt-1 text-xl font-bold text-wayna-950 sm:text-2xl">
                        Editá cómo te ven los visitantes
                    </h2>
                </div>
            }
        >
            <Head title="Editar perfil — WAYNA" />

            <div className="mx-auto max-w-3xl space-y-6">
                <AdminFlashSuccess message={flash?.success} />

                {flash?.error ? (
                    <div className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                        {flash.error}
                    </div>
                ) : null}

                <OnboardingChecklist />

                <p className="rounded-2xl border border-wayna-100 bg-wayna-50/50 px-4 py-3 text-sm text-stone-600">
                    Estos datos aparecen en tu{' '}
                    <a
                        href={perfil_publico_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="font-semibold text-wayna-700 underline underline-offset-2 hover:text-wayna-900"
                    >
                        perfil público
                    </a>
                    . El estado de tu cuenta y las metas de campaña las gestiona el equipo WAYNA.
                </p>

                <form onSubmit={enviar} className="space-y-6">
                    <section className={adminSectionCard}>
                        <h3 className="text-sm font-bold text-wayna-950">Datos principales</h3>
                        <div className={`${adminFormStack} mt-4`}>
                            <div className={adminFormGrid2}>
                                <AdminFormField
                                    id="nombre"
                                    label="Nombre"
                                    required
                                    error={error('nombre')}
                                >
                                    <input
                                        id="nombre"
                                        type="text"
                                        value={data.nombre}
                                        onChange={(e) => setData('nombre', e.target.value)}
                                        className={adminInputClass}
                                        autoComplete="given-name"
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
                                        value={data.apellidos}
                                        onChange={(e) => setData('apellidos', e.target.value)}
                                        className={adminInputClass}
                                        autoComplete="family-name"
                                    />
                                </AdminFormField>
                            </div>

                            <div className={adminFormGrid2}>
                                <AdminFormField
                                    id="tipo_emprendimiento"
                                    label="Tipo de emprendimiento"
                                    required
                                    error={error('tipo_emprendimiento')}
                                >
                                    <select
                                        id="tipo_emprendimiento"
                                        value={data.tipo_emprendimiento}
                                        onChange={(e) =>
                                            setData('tipo_emprendimiento', e.target.value)
                                        }
                                        className={adminInputClass}
                                    >
                                        <option value="">Elegí una opción</option>
                                        {tiposEmprendimiento.map((opt) => (
                                            <option key={opt.value} value={opt.value}>
                                                {opt.label}
                                            </option>
                                        ))}
                                    </select>
                                </AdminFormField>
                                <AdminFormField
                                    id="departamento"
                                    label="Departamento"
                                    required
                                    error={error('departamento')}
                                >
                                    <select
                                        id="departamento"
                                        value={data.departamento}
                                        onChange={(e) => setData('departamento', e.target.value)}
                                        className={adminInputClass}
                                    >
                                        <option value="">Elegí un departamento</option>
                                        {departamentos.map((opt) => (
                                            <option key={opt.value} value={opt.value}>
                                                {opt.label}
                                            </option>
                                        ))}
                                    </select>
                                </AdminFormField>
                            </div>

                            <AdminFormField
                                id="descripcion"
                                label="Descripción del emprendimiento"
                                hint="Opcional. Contá qué producís o vendés para los turistas."
                                error={error('descripcion')}
                            >
                                <textarea
                                    id="descripcion"
                                    rows={5}
                                    value={data.descripcion}
                                    onChange={(e) => setData('descripcion', e.target.value)}
                                    className={adminTextareaClass}
                                />
                            </AdminFormField>
                        </div>
                    </section>

                    <AdminFotografiaPerfilField
                        fotografia={data.fotografia}
                        setFotografia={(file) => setData('fotografia', file)}
                        urlActual={fotografiaActual}
                        error={error('fotografia')}
                        esEdicion
                        nombreCompleto={nombreCompleto}
                    />

                    <section className={adminSectionCard}>
                        <AdminEmprendedorMediosFields
                            data={data}
                            setData={setData}
                            emprendedor={emprendedor}
                            error={error}
                            esEdicion
                        />
                    </section>

                    <section className={adminSectionCard}>
                        <AdminEmprendedorRedesFields data={data} setData={setData} error={error} />
                    </section>

                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href={route('emprendedor.dashboard')}
                            className="inline-flex items-center justify-center rounded-2xl border border-wayna-200 bg-white px-5 py-3 text-sm font-bold text-wayna-800 transition hover:bg-wayna-50"
                        >
                            Volver al panel
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className={`w-full sm:w-auto ${adminPrimaryGradientBtn}`}
                        >
                            {processing ? 'Guardando…' : 'Guardar perfil público'}
                        </button>
                    </div>
                </form>
            </div>
        </EmprendedorLayout>
    );
}
