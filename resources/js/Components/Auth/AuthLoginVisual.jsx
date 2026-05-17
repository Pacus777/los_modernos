import { useTranslation } from 'react-i18next';

const LOGIN_IMAGE = '/images/imagen_landing_wayna.jpg';

export default function AuthLoginVisual() {
    const { t } = useTranslation();

    return (
        <section className="auth-login-visual relative min-h-[220px] flex-1 overflow-hidden lg:min-h-0">
            <img
                src={LOGIN_IMAGE}
                alt=""
                className="auth-login-visual__photo absolute inset-0 h-full w-full object-cover"
            />

            <div className="auth-login-visual__wave auth-login-visual__wave--1" aria-hidden />
            <div className="auth-login-visual__wave auth-login-visual__wave--2" aria-hidden />
            <div className="auth-login-visual__wave auth-login-visual__wave--3" aria-hidden />

            <div
                className="absolute inset-0 bg-gradient-to-br from-wayna-950/75 via-wayna-800/55 to-wayna-600/40"
                aria-hidden
            />

            <div className="auth-login-visual-pattern absolute inset-0 opacity-[0.12]" aria-hidden />

            <div className="relative z-10 flex h-full items-end p-8 sm:items-center sm:justify-center sm:p-10">
                <p className="max-w-sm text-lg font-bold leading-snug text-white sm:text-center sm:text-xl">
                    {t('auth.visual.title')}
                </p>
            </div>
        </section>
    );
}
