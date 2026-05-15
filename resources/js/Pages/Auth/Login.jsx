import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

// 1. Se importa useTranslation para poder usar las traducciones de i18next.
import { useTranslation } from 'react-i18next';

export default function Login({ status, canResetPassword }) {
    // 2. t() es la función que busca el texto según el idioma activo.
    // Ejemplo: t('auth.welcome')
    // Si el idioma activo es ES mostrará "Bienvenido".
    // Si el idioma activo es EN mostrará "Welcome".
    const { t } = useTranslation();

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            {/* 3. Incluso el título de la pestaña puede traducirse con t(). */}
            <Head title={t('auth.loginTitle')} />

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form
                onSubmit={submit}
                className="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-8 shadow-2xl"
            >
                <div className="mb-6 text-center">
                    {/* 4. Todo texto fijo se reemplaza por una clave de traducción. */}
                    <h1 className="text-3xl font-bold text-gray-800">
                        {t('auth.welcome')}
                    </h1>

                    <p className="mt-2 text-gray-500">
                        {t('auth.subtitle')}
                    </p>
                </div>

                <div>
                    {/* 5. Los labels también pueden usar traducción. */}
                    <InputLabel htmlFor="email" value={t('auth.email')} />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value={t('auth.password')} />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full rounded-lg border-gray-300 focus:border-wayna-500 focus:ring-wayna-500"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4 block">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData('remember', e.target.checked)
                            }
                        />

                        <span className="ms-2 text-sm text-gray-600">
                            {t('auth.remember')}
                        </span>
                    </label>
                </div>

                <div className="mt-4 flex items-center justify-end">
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-wayna-500 focus:ring-offset-2"
                        >
                            {t('auth.forgotPassword')}
                        </Link>
                    )}

                    <PrimaryButton
                        className="ms-4 rounded-lg bg-wayna-500 px-6 py-2 transition hover:bg-wayna-700"
                        disabled={processing}
                    >
                        {t('auth.loginButton')}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}