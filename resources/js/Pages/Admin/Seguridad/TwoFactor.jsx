import AdminBackLink from '@/Components/Admin/AdminBackLink';
import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PasswordInput from '@/Components/PasswordInput';
import TextInput from '@/Components/TextInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';

export default function TwoFactor({ enabled, confirmedAt, setup }) {
    const flash = usePage().props.flash;

    const activarForm = useForm({ code: '' });
    const desactivarForm = useForm({ code: '', password: '' });

    const preparar = () => {
        router.post(route('admin.two-factor.preparar'));
    };

    const activar = (e) => {
        e.preventDefault();
        activarForm.post(route('admin.two-factor.activar'));
    };

    const desactivar = (e) => {
        e.preventDefault();
        desactivarForm.delete(route('admin.two-factor.desactivar'));
    };

    return (
        <AdminLayout
            header={
                <div>
                    <AdminBackLink href={route('admin.dashboard')} label="Panel" />
                    <h1 className="mt-2 text-2xl font-bold text-wayna-950">
                        Autenticación en dos pasos
                    </h1>
                    <p className="mt-1 text-sm text-stone-600">
                        Protegé el panel admin con Google Authenticator.
                    </p>
                </div>
            }
        >
            <Head title="2FA Admin" />

            <div className="mx-auto max-w-2xl space-y-6 p-4 sm:p-6 lg:p-8">
                <AdminFlashSuccess />

                {flash?.error && (
                    <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        {flash.error}
                    </div>
                )}

                {enabled ? (
                    <section className="rounded-2xl border border-wayna-100 bg-white p-6 shadow-sm">
                        <p className="text-sm font-semibold text-emerald-800">
                            2FA activo
                            {confirmedAt && (
                                <span className="font-normal text-stone-600">
                                    {' '}
                                    — desde {new Date(confirmedAt).toLocaleString()}
                                </span>
                            )}
                        </p>

                        <form onSubmit={desactivar} className="mt-6 space-y-4">
                            <p className="text-sm text-stone-600">
                                Para desactivar, confirmá tu contraseña y un código actual de la app.
                            </p>
                            <div>
                                <InputLabel htmlFor="disable_code" value="Código 2FA" />
                                <TextInput
                                    id="disable_code"
                                    value={desactivarForm.data.code}
                                    className="mt-1"
                                    onChange={(e) => desactivarForm.setData('code', e.target.value)}
                                />
                                <InputError message={desactivarForm.errors.code} className="mt-1" />
                            </div>
                            <div>
                                <InputLabel htmlFor="disable_password" value="Contraseña actual" />
                                <PasswordInput
                                    id="disable_password"
                                    value={desactivarForm.data.password}
                                    className="mt-1"
                                    onChange={(e) => desactivarForm.setData('password', e.target.value)}
                                />
                                <InputError message={desactivarForm.errors.password} className="mt-1" />
                            </div>
                            <button
                                type="submit"
                                disabled={desactivarForm.processing}
                                className="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-800 hover:bg-red-100"
                            >
                                Desactivar 2FA
                            </button>
                        </form>
                    </section>
                ) : (
                    <section className="rounded-2xl border border-wayna-100 bg-white p-6 shadow-sm">
                        <p className="text-sm text-stone-600">
                            Escaneá el código QR con Google Authenticator (o app compatible TOTP)
                            y confirmá con un código de 6 dígitos.
                        </p>

                        {!setup?.qrDataUri ? (
                            <button
                                type="button"
                                onClick={preparar}
                                className="btn-wayna-primary mt-4"
                            >
                                Generar código QR
                            </button>
                        ) : (
                            <div className="mt-6 space-y-4">
                                <img
                                    src={setup.qrDataUri}
                                    alt="QR Google Authenticator"
                                    className="mx-auto h-52 w-52 rounded-xl border border-stone-200 bg-white p-2"
                                />
                                {setup.secret && (
                                    <p className="break-all text-center font-mono text-xs text-stone-600">
                                        Clave manual: {setup.secret}
                                    </p>
                                )}

                                <form onSubmit={activar} className="space-y-3">
                                    <div>
                                        <InputLabel htmlFor="activate_code" value="Código de verificación" />
                                        <TextInput
                                            id="activate_code"
                                            value={activarForm.data.code}
                                            className="mt-1"
                                            autoFocus
                                            onChange={(e) => activarForm.setData('code', e.target.value)}
                                        />
                                        <InputError message={activarForm.errors.code} className="mt-1" />
                                    </div>
                                    <button
                                        type="submit"
                                        disabled={activarForm.processing}
                                        className="btn-wayna-primary"
                                    >
                                        Activar 2FA
                                    </button>
                                </form>
                            </div>
                        )}
                    </section>
                )}
            </div>
        </AdminLayout>
    );
}
