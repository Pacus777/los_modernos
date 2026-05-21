import AuthLoginLayout from '@/Components/Auth/AuthLoginLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { Head, useForm } from '@inertiajs/react';

export default function TwoFactorChallenge({ email }) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('two-factor.verify'));
    };

    return (
        <AuthLoginLayout navHref="/">
            <Head title="Verificación 2FA" />

            <div className="mb-6">
                <h1 className="text-2xl font-black text-wayna-950 sm:text-3xl">
                    Verificación en dos pasos
                </h1>
                <p className="mt-2 text-sm text-stone-600">
                    Ingresá el código de 6 dígitos de Google Authenticator para{' '}
                    <span className="font-semibold text-wayna-800">{email}</span>.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <InputLabel htmlFor="code" value="Código de autenticación" />
                    <TextInput
                        id="code"
                        name="code"
                        inputMode="numeric"
                        autoComplete="one-time-code"
                        autoFocus
                        maxLength={8}
                        value={data.code}
                        className="mt-1 tracking-widest"
                        onChange={(e) => setData('code', e.target.value)}
                        placeholder="000000"
                    />
                    <InputError message={errors.code} className="mt-2" />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="btn-wayna-primary w-full justify-center py-3 text-sm font-bold uppercase tracking-wide"
                >
                    {processing ? 'Verificando…' : 'Verificar'}
                </button>
            </form>
        </AuthLoginLayout>
    );
}
