import AuthLoginLayout from '@/Components/Auth/AuthLoginLayout';
import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PasswordInput from '@/Components/PasswordInput';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function Login({ status, canResetPassword }) {
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
        <AuthLoginLayout navHref="/">
            <Head title={t('auth.loginTitle')} />

            {status && (
                <div className="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {status}
                </div>
            )}

            <div className="mb-6">
                <h1 className="text-2xl font-black text-wayna-950 sm:text-3xl">{t('auth.welcome')}</h1>
                <p className="mt-2 text-sm text-stone-600">{t('auth.subtitle')}</p>
            </div>

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <InputLabel htmlFor="email" value={t('auth.email')} className="sr-only" />
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        placeholder={t('auth.email')}
                        className="auth-login-field mt-0 block w-full"
                        autoComplete="username"
                        isFocused
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password" value={t('auth.password')} className="sr-only" />
                    <PasswordInput
                        id="password"
                        name="password"
                        value={data.password}
                        placeholder={t('auth.password')}
                        inputClassName="auth-login-field mt-0 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <label className="flex cursor-pointer items-center gap-2">
                    <Checkbox
                        name="remember"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                    />
                    <span className="text-sm text-stone-600">{t('auth.remember')}</span>
                </label>

                <div className="flex flex-wrap items-center justify-between gap-3 pt-1 text-sm">
                    <Link
                        href="/"
                        className="font-semibold text-wayna-600 hover:text-wayna-700"
                    >
                        ← {t('auth.backToHome')}
                    </Link>
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="font-semibold text-wayna-600 hover:underline"
                        >
                            {t('auth.forgotPassword')}
                        </Link>
                    )}
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="btn-wayna-primary-gradient auth-login-submit w-full !rounded-full !py-3.5 !text-sm !tracking-wide disabled:opacity-60"
                >
                    {processing ? t('auth.loggingIn') : t('auth.loginButton')}
                </button>
            </form>
        </AuthLoginLayout>
    );
}
