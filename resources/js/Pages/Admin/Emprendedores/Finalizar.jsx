import AdminBackLink from '@/Components/Admin/AdminBackLink';
import AdminEmprendedorCampanaOpcional from '@/Components/Admin/AdminEmprendedorCampanaOpcional';
import AdminEmprendedorCuentaPanel from '@/Components/Admin/AdminEmprendedorCuentaPanel';
import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import {
    adminBackdropTall,
    adminFormFooterPrimaryBtn,
    adminFormFooterSecondaryBtn,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, usePage } from '@inertiajs/react';

function IconoCheck({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden>
            <path
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
        </svg>
    );
}

/**
 * E-03 — Último paso tras crear emprendedor: credenciales antes de cerrar el registro.
 */
export default function Finalizar({ emprendedor, cuenta, campanaActiva = null }) {
    const { flash } = usePage().props;
    const tieneCuenta = Boolean(cuenta?.tiene_cuenta);
    const nombreCompleto = `${emprendedor.nombre} ${emprendedor.apellidos}`.trim();
    const fotoSrc = emprendedor.fotografia ? `/storage/${emprendedor.fotografia}` : null;

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin · Registro
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        Finalizar registro
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        El perfil ya está guardado. Configurá el acceso al sistema y cerrá el proceso cuando
                        estés listo.
                    </p>
                </div>
            }
        >
            <Head title="Finalizar registro — Wayna" />

            <div className="relative py-8">
                <div className={adminBackdropTall} aria-hidden />

                <div className="relative mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <AdminBackLink href={route('admin.emprendedores.index')}>
                        Volver al listado
                    </AdminBackLink>
                    <AdminFlashSuccess message={flash?.success} />
                    {flash?.error ? (
                        <div
                            className="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900"
                            role="alert"
                        >
                            {flash.error}
                        </div>
                    ) : null}

                    <div className="mb-6 overflow-hidden rounded-3xl border border-wayna-200/80 bg-white shadow-lg ring-1 ring-black/[0.03]">
                        <div className="flex flex-col gap-4 border-b border-wayna-100 bg-wayna-50/60 px-6 py-5 sm:flex-row sm:items-center sm:px-8">
                            {fotoSrc ? (
                                <img
                                    src={fotoSrc}
                                    alt=""
                                    className="h-14 w-14 rounded-2xl object-cover ring-2 ring-wayna-200 shadow-sm"
                                />
                            ) : (
                                <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-wayna-100 text-lg font-black text-wayna-700">
                                    {emprendedor.nombre?.charAt(0)}
                                </div>
                            )}
                            <div className="min-w-0 flex-1">
                                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-wayna-600">
                                    Perfil registrado
                                </p>
                                <p className="truncate text-lg font-bold text-wayna-950">{nombreCompleto}</p>
                                <p className="mt-1 text-sm text-stone-600">
                                    Código QR y datos listos. Falta el acceso al panel (opcional ahora).
                                </p>
                            </div>
                            <span className="inline-flex items-center gap-1.5 self-start rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-900 ring-1 ring-emerald-200 sm:self-center">
                                <IconoCheck className="h-4 w-4" />
                                Guardado
                            </span>
                        </div>

                        <nav aria-label="Progreso del registro" className="px-6 py-4 sm:px-8">
                            <ol className="flex items-center gap-2 text-xs font-bold text-stone-500">
                                {['Datos', 'Perfil', 'Confirmación', 'Campaña', 'Acceso'].map((etiqueta, i) => {
                                    const paso = i + 1;
                                    const activo = paso === (campanaActiva ? 5 : 4);
                                    const listo = paso < (campanaActiva ? 5 : 4);

                                    return (
                                        <li key={etiqueta} className="flex items-center gap-2">
                                            {i > 0 ? (
                                                <span
                                                    className={`h-0.5 w-4 rounded-full sm:w-6 ${listo ? 'bg-wayna-400' : 'bg-wayna-100'}`}
                                                    aria-hidden
                                                />
                                            ) : null}
                                            <span
                                                className={`flex h-7 w-7 items-center justify-center rounded-full text-[10px] ${
                                                    activo
                                                        ? 'bg-wayna-600 text-white ring-4 ring-wayna-200'
                                                        : listo
                                                          ? 'bg-wayna-500 text-white'
                                                          : 'bg-white text-stone-400 ring-2 ring-wayna-100'
                                                }`}
                                            >
                                                {listo ? '✓' : paso}
                                            </span>
                                            <span
                                                className={`hidden sm:inline ${activo ? 'text-wayna-800' : ''}`}
                                            >
                                                {etiqueta}
                                            </span>
                                        </li>
                                    );
                                })}
                            </ol>
                        </nav>
                    </div>

                    <AdminEmprendedorCampanaOpcional
                        emprendedorId={emprendedor.id}
                        nombreCompleto={nombreCompleto}
                        campanaActiva={campanaActiva}
                    />

                    <div className="mt-6">
                        <p className="mb-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-wayna-700">
                            Acceso al panel del emprendedor
                        </p>
                    <AdminEmprendedorCuentaPanel
                        emprendedorId={emprendedor.id}
                        emprendedor={{
                            nombre: emprendedor.nombre,
                            apellidos: emprendedor.apellidos,
                            nombre_completo: nombreCompleto,
                        }}
                        cuenta={cuenta}
                        nombreSugerido={nombreCompleto}
                        modo="finalizar"
                        fotoSrc={fotoSrc}
                    />
                    </div>

                    <div className="mt-8 flex flex-col-reverse gap-3 rounded-2xl border border-wayna-100 bg-white/90 p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
                        <Link
                            href={route('admin.emprendedores.completar-registro', emprendedor.id)}
                            method="post"
                            as="button"
                            className={adminFormFooterSecondaryBtn}
                        >
                            {tieneCuenta ? 'Finalizar sin cambios' : 'Omitir acceso por ahora'}
                        </Link>

                        <Link
                            href={route('admin.emprendedores.completar-registro', emprendedor.id)}
                            method="post"
                            as="button"
                            className={`${adminFormFooterPrimaryBtn} ${!tieneCuenta ? 'opacity-90' : ''}`}
                        >
                            {tieneCuenta ? 'Finalizar registro' : 'Cerrar registro'}
                        </Link>
                    </div>

                    {!tieneCuenta ? (
                        <p className="mt-3 text-center text-xs text-stone-500">
                            Podés crear la cuenta arriba o cerrar ahora y configurarla después desde{' '}
                            <strong>Editar emprendedor</strong>.
                        </p>
                    ) : null}
                </div>
            </div>
        </AdminLayout>
    );
}
