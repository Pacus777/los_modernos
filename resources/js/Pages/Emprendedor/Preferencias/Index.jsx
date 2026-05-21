import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import {
    adminFormFooterPrimaryBtn,
    adminListCardOuter,
    adminPrimaryGradientBtn,
} from '@/Components/Admin/adminUi';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

function OpcionPreferencia({ checked, onChange, titulo, descripcion, error }) {
    return (
        <label className="flex cursor-pointer items-start gap-4 rounded-2xl border-2 border-stone-200 bg-white p-5 shadow-sm transition hover:border-wayna-300">
            <input
                type="checkbox"
                className="mt-1 h-5 w-5 shrink-0 rounded border-stone-400 text-wayna-600 focus:ring-wayna-500"
                checked={checked}
                onChange={onChange}
            />
            <span className="min-w-0 flex-1">
                <span className="block text-base font-bold text-stone-900">{titulo}</span>
                <span className="mt-2 block text-sm leading-relaxed text-stone-700">{descripcion}</span>
                {error && <span className="mt-2 block text-sm text-red-600">{error}</span>}
            </span>
        </label>
    );
}

/**
 * E-10 — Preferencias de correo y avisos en el panel.
 */
export default function PreferenciasIndex({
    notificar_donaciones_email = true,
    notificar_donaciones_panel = true,
    email_cuenta = '',
}) {
    const { flash } = usePage().props;

    const { data, setData, put, processing, errors } = useForm({
        notificar_donaciones_email: Boolean(notificar_donaciones_email),
        notificar_donaciones_panel: Boolean(notificar_donaciones_panel),
    });

    const enviar = (e) => {
        e.preventDefault();
        put(route('emprendedor.preferencias.update'));
    };

    return (
        <EmprendedorLayout
            header={
                <div>
                    <p className="text-xs font-bold uppercase tracking-wider text-wayna-600">
                        Configuración
                    </p>
                    <h1 className="text-xl font-black text-stone-900 sm:text-2xl">
                        Cómo quiero que me avisen
                    </h1>
                </div>
            }
        >
            <Head title="Preferencias — WAYNA" />
            <AdminFlashSuccess flash={flash} />

            <p className="mb-6 max-w-2xl text-base leading-relaxed text-stone-800">
                Cuando un aporte queda <strong className="font-bold text-stone-900">validado</strong>{' '}
                (y suma a tu recaudación), WAYNA puede avisarte por correo y/o en la campana de este
                panel. Los aportes pendientes no generan avisos hasta validarse.
            </p>

            <form onSubmit={enviar} className="space-y-8">
                <section className={adminListCardOuter}>
                    <h2 className="text-lg font-bold text-stone-900">Correo electrónico</h2>
                    <p className="mt-2 text-base text-stone-700">
                        Se envía a:{' '}
                        <span className="break-all font-semibold text-wayna-900">{email_cuenta}</span>
                    </p>

                    <div className="mt-5">
                        <OpcionPreferencia
                            checked={data.notificar_donaciones_email}
                            onChange={(e) =>
                                setData('notificar_donaciones_email', e.target.checked)
                            }
                            titulo="Recibir correo por cada aporte validado"
                            descripcion="Incluye monto, campaña y enlace a tu historial de donaciones."
                            error={errors.notificar_donaciones_email}
                        />
                    </div>
                </section>

                <section className={adminListCardOuter}>
                    <h2 className="text-lg font-bold text-stone-900">Avisos en el panel</h2>
                    <p className="mt-2 text-base text-stone-700">
                        Aparecen en la campana del menú y en{' '}
                        <Link
                            href={route('emprendedor.notificaciones.index')}
                            className="font-semibold text-wayna-800 underline decoration-wayna-300 underline-offset-2"
                        >
                            Mis avisos
                        </Link>
                        .
                    </p>

                    <div className="mt-5">
                        <OpcionPreferencia
                            checked={data.notificar_donaciones_panel}
                            onChange={(e) =>
                                setData('notificar_donaciones_panel', e.target.checked)
                            }
                            titulo="Mostrar avisos en el panel cuando reciba un aporte validado"
                            descripcion="No necesitás tener el correo abierto: al entrar a WAYNA verás el contador y el detalle."
                            error={errors.notificar_donaciones_panel}
                        />
                    </div>
                </section>

                <button
                    type="submit"
                    disabled={processing}
                    className={`${adminPrimaryGradientBtn} ${adminFormFooterPrimaryBtn}`}
                >
                    {processing ? 'Guardando…' : 'Guardar preferencias'}
                </button>
            </form>
        </EmprendedorLayout>
    );
}
