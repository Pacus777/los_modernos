import {
    adminFormFooterPrimaryBtn,
    adminFormFooterSecondaryBtn,
    adminInputClass,
    adminLabelField,
    adminPrimaryGradientBtn,
} from '@/Components/Admin/adminUi';
import { useConfirmDialog } from '@/hooks/useConfirmDialog';
import { Link, router, useForm } from '@inertiajs/react';

function IconoCandado({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden>
            <path
                stroke="currentColor"
                strokeWidth="1.75"
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
            />
        </svg>
    );
}

function IconoCorreo({ className }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" aria-hidden>
            <path
                stroke="currentColor"
                strokeWidth="1.75"
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
            />
        </svg>
    );
}

/**
 * E-03 — Panel de cuenta de acceso (diseño alineado al admin WAYNA).
 */
export default function AdminEmprendedorCuentaPanel({
    emprendedorId,
    emprendedor = null,
    cuenta = {},
    nombreSugerido = '',
    modo = 'edicion',
    fotoSrc = null,
    soloContenido = false,
    desdeListado = false,
}) {
    const { requestConfirm, ConfirmDialogPortal } = useConfirmDialog();
    const tieneCuenta = Boolean(cuenta?.tiene_cuenta);
    const esFinalizar = modo === 'finalizar';

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        name: nombreSugerido || '',
        finalizar_registro: esFinalizar,
        desde_listado: desdeListado,
    });

    const opcionesPost = {
        preserveScroll: true,
        onSuccess: () => reset('email'),
    };

    const crearCuenta = (e) => {
        e.preventDefault();
        post(route('admin.emprendedores.cuenta.store', emprendedorId), opcionesPost);
    };

    const reenviarCredenciales = () => {
        requestConfirm({
            title: 'Reenviar credenciales',
            message:
                'Se generará una nueva contraseña temporal y se enviará por correo. La anterior dejará de funcionar.',
            confirmLabel: 'Sí, reenviar',
            variant: 'danger',
            onConfirm: ({ close }) => {
                close();
                router.post(
                    route('admin.emprendedores.cuenta.reenviar', emprendedorId),
                    {
                        finalizar_registro: esFinalizar,
                        desde_listado: desdeListado,
                    },
                    opcionesPost,
                );
            },
        });
    };

    const nombreMostrar =
        emprendedor?.nombre_completo ||
        `${emprendedor?.nombre ?? ''} ${emprendedor?.apellidos ?? ''}`.trim() ||
        nombreSugerido;

    const contenido = (
            <div
                className={
                    soloContenido
                        ? 'space-y-6 p-4 sm:p-6'
                        : 'space-y-6 bg-gradient-to-b from-wayna-50/40 via-white to-surface-muted/30 p-6 sm:p-8'
                }
            >
                {nombreMostrar ? (
                    <p className="text-sm text-stone-600">
                        Emprendedor:{' '}
                        <span className="font-bold text-wayna-950">{nombreMostrar}</span>
                    </p>
                ) : null}

                <div className="flex gap-3 rounded-2xl border border-amber-200/90 bg-amber-50/90 px-4 py-3 text-sm text-amber-950">
                    <IconoCorreo className="mt-0.5 h-5 w-5 shrink-0 text-amber-700" />
                    <p>
                        Escribí el <strong className="text-amber-950">correo del emprendedor</strong>: el que use para
                        revisar mensajes. Ahí le llegará el mail con su usuario y contraseña. Si no lo encuentra, que
                        revise spam.
                    </p>
                </div>

                {tieneCuenta ? (
                    <div className="space-y-5">
                        <div className="rounded-2xl border border-emerald-200 bg-emerald-50/80 px-5 py-4">
                            <p className="text-sm font-bold text-emerald-900">Cuenta vinculada</p>
                            <p className="mt-1 text-sm text-emerald-800/90">
                                Las credenciales fueron enviadas a{' '}
                                <span className="font-semibold">{cuenta.email}</span>.
                                {esFinalizar
                                    ? ' Podés finalizar el registro o reenviar una nueva contraseña.'
                                    : null}
                            </p>
                        </div>

                        <dl className="grid gap-3 sm:grid-cols-2">
                            <div className="rounded-2xl border border-wayna-100 bg-white p-4 shadow-sm">
                                <dt className="text-[10px] font-bold uppercase tracking-wider text-wayna-600">
                                    Nombre en el sistema
                                </dt>
                                <dd className="mt-1 text-sm font-semibold text-wayna-950">
                                    {cuenta.name || '—'}
                                </dd>
                            </div>
                            <div className="rounded-2xl border border-wayna-100 bg-white p-4 shadow-sm">
                                <dt className="text-[10px] font-bold uppercase tracking-wider text-wayna-600">
                                    Correo de acceso
                                </dt>
                                <dd className="mt-1 break-all text-sm font-semibold text-wayna-950">
                                    {cuenta.email}
                                </dd>
                            </div>
                        </dl>

                        <button
                            type="button"
                            onClick={reenviarCredenciales}
                            className={adminFormFooterSecondaryBtn}
                        >
                            Regenerar contraseña y reenviar
                        </button>
                    </div>
                ) : (
                    <form onSubmit={crearCuenta} className="space-y-5">
                        <div className="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label htmlFor={`cuenta-name-${emprendedorId}`} className={adminLabelField}>
                                    Nombre en el sistema
                                </label>
                                <input
                                    id={`cuenta-name-${emprendedorId}`}
                                    type="text"
                                    className={`${adminInputClass} mt-2`}
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder={nombreSugerido || 'Nombre del emprendedor'}
                                />
                                {errors.name ? (
                                    <p className="mt-1.5 text-sm font-medium text-red-600">{errors.name}</p>
                                ) : null}
                            </div>

                            <div>
                                <label htmlFor={`cuenta-email-${emprendedorId}`} className={adminLabelField}>
                                    Correo de acceso{' '}
                                    <span className="font-bold text-wayna-600">*</span>
                                </label>
                                <input
                                    id={`cuenta-email-${emprendedorId}`}
                                    type="email"
                                    required
                                    autoComplete="off"
                                    className={`${adminInputClass} mt-2`}
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="nombre.apellido@empresa.com"
                                />
                                {errors.email ? (
                                    <p className="mt-1.5 text-sm font-medium text-red-600">{errors.email}</p>
                                ) : null}
                            </div>
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className={`w-full sm:w-auto ${adminPrimaryGradientBtn}`}
                        >
                            {processing ? 'Enviando credenciales…' : 'Crear cuenta y enviar credenciales'}
                        </button>
                    </form>
                )}
            </div>
    );

    return (
        <>
            {soloContenido ? (
                contenido
            ) : (
                <div className="overflow-hidden rounded-3xl border border-wayna-200/90 bg-white shadow-xl shadow-wayna-900/[0.07] ring-1 ring-black/[0.03]">
                    <div className="header-wayna-gradient px-6 py-5 sm:px-8">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex items-start gap-4">
                                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-white ring-2 ring-white/30">
                                    <IconoCandado className="h-6 w-6" />
                                </div>
                                <div>
                                    <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-100/95">
                                        {esFinalizar ? 'Paso final del registro' : 'Cuenta de acceso'}
                                    </p>
                                    <h3 className="mt-1 text-lg font-bold text-white sm:text-xl">
                                        Credenciales para el panel
                                    </h3>
                                    <p className="mt-1 max-w-lg text-sm text-orange-50/90">
                                        Creá su acceso al panel. Le llegará un correo con usuario y contraseña
                                        para ingresar.
                                    </p>
                                </div>
                            </div>
                            {fotoSrc ? (
                                <img
                                    src={fotoSrc}
                                    alt=""
                                    className="h-16 w-16 shrink-0 rounded-2xl object-cover ring-4 ring-white/25 shadow-lg"
                                />
                            ) : null}
                        </div>
                    </div>
                    {contenido}
                </div>
            )}
            <ConfirmDialogPortal />
        </>
    );
}
