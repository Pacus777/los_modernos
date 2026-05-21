import AuthLoginLayout from '@/Components/Auth/AuthLoginLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import LogoutButton from '@/Components/LogoutButton';
import PasswordInput from '@/Components/PasswordInput';
import { adminPrimaryGradientBtn } from '@/Components/Admin/adminUi';
import { Head, useForm } from '@inertiajs/react';

/**
 * E-04 — Cambio obligatorio de contraseña temporal (emprendedor).
 */
export default function CambiarPassword({ usuario }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
        password_confirmation: '',
    });

    const enviar = (e) => {
        e.preventDefault();
        post(route('emprendedor.password.force.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLoginLayout navHref={route('login')}>
            <Head title="Nueva contraseña — WAYNA" />

            <div className="mb-6">
                <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-wayna-600">
                    Seguridad de tu cuenta
                </p>
                <h1 className="mt-2 text-2xl font-black text-wayna-950 sm:text-3xl">
                    Creá tu contraseña
                </h1>
                <p className="mt-2 text-sm text-stone-600">
                    Hola, <strong className="text-wayna-950">{usuario.name}</strong>. La contraseña
                    del correo era temporal. Elegí una nueva para entrar a tu panel (
                    <span className="text-wayna-800">{usuario.email}</span>).
                </p>
            </div>

            <form onSubmit={enviar} className="space-y-5">
                <div>
                    <InputLabel htmlFor="password" value="Nueva contraseña" />
                    <PasswordInput
                        id="password"
                        name="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        inputClassName="auth-login-field mt-2 block w-full"
                        autoComplete="new-password"
                        isFocused
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password_confirmation" value="Confirmar contraseña" />
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        inputClassName="auth-login-field mt-2 block w-full"
                        autoComplete="new-password"
                    />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className={`w-full ${adminPrimaryGradientBtn}`}
                >
                    {processing ? 'Guardando…' : 'Guardar y continuar al panel'}
                </button>
            </form>

            <p className="mt-6 text-center">
                <LogoutButton className="text-sm font-semibold text-stone-500 underline decoration-stone-300 underline-offset-2 hover:text-wayna-700">
                    Cerrar sesión
                </LogoutButton>
            </p>
        </AuthLoginLayout>
    );
}
