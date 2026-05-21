import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

/**
 * Seguir emprendedor por email sin cuenta (S4-06).
 */
export default function SeguirEmprendedorCard({ emprendedorId, seguimiento = {} }) {
    const { t } = useTranslation();
    const { flash } = usePage().props;
    const estado = flash?.seguimiento_status || seguimiento?.estado || 'ninguno';
    const emailMostrado = seguimiento?.email ?? '';

    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const enviar = (e) => {
        e.preventDefault();
        post(route('turista.seguir.store', emprendedorId), {
            preserveScroll: true,
        });
    };

    return (
        <section className="mt-6 rounded-2xl border border-wayna-200 bg-gradient-to-br from-wayna-50/80 to-white p-5 shadow-sm sm:p-6">
            <header className="mb-4">
                <h2 className="text-lg font-black text-wayna-950">
                    {t('tourist.follow.title')}
                </h2>
                <p className="mt-1 text-sm text-stone-600">{t('tourist.follow.subtitle')}</p>
            </header>

            {estado === 'confirmado' ? (
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    <p className="font-semibold">{t('tourist.follow.confirmedTitle')}</p>
                    <p className="mt-1">
                        {t('tourist.follow.confirmedBody', { email: emailMostrado })}
                    </p>
                </div>
            ) : null}

            {estado === 'pendiente' ? (
                <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <p className="font-semibold">{t('tourist.follow.pendingTitle')}</p>
                    <p className="mt-1">
                        {t('tourist.follow.pendingBody', { email: emailMostrado })}
                    </p>
                </div>
            ) : null}

            {estado !== 'confirmado' ? (
                <form onSubmit={enviar} className="mt-4 space-y-3">
                    <div>
                        <InputLabel htmlFor="seguir_email" value={t('tourist.follow.emailLabel')} />
                        <TextInput
                            id="seguir_email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 w-full"
                            placeholder="tu@correo.com"
                            autoComplete="email"
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError message={errors.email} className="mt-1" />
                    </div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="btn-wayna-secondary w-full justify-center sm:w-auto"
                    >
                        {processing ? t('tourist.follow.sending') : t('tourist.follow.submit')}
                    </button>
                </form>
            ) : null}
        </section>
    );
}
