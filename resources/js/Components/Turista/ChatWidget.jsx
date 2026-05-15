import ApplicationLogo from '@/Components/ApplicationLogo';
import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

/**
 * ChatWidget
 *
 * Widget flotante para el turista.
 *
 * Funciona con:
 * - ChatController@store
 * - RagService
 * - flash.rag compartido desde HandleInertiaRequests
 *
 * No usa fetch ni axios manual.
 * Usa useForm de Inertia.
 */
export default function ChatWidget() {
    const { t } = useTranslation();
    const { flash, locale } = usePage().props;

    const [abierto, setAbierto] = useState(false);

    const idiomaActivo = locale || 'es';

    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
    } = useForm({
        pregunta: '',
        idioma: idiomaActivo,
    });

    /**
     * Envía la pregunta al ChatController.
     *
     * preserveState mantiene el widget abierto.
     * preserveScroll evita que la página vuelva arriba al responder.
     */
    const enviarPregunta = (e) => {
        e.preventDefault();

        if (!data.pregunta.trim()) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Enviar pregunta al ChatController
        |--------------------------------------------------------------------------
        |
        | No usamos transform().post() porque en esta versión de Inertia
        | transform no está devolviendo un objeto encadenable.
        |
        | El idioma ya está dentro del estado inicial del formulario.
        |
        */

        post(route('chat.store'), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                reset('pregunta');
                setAbierto(true);
            },
        });
    };

    const respuesta = flash?.rag;

    return (
        <div className="fixed bottom-[max(1rem,env(safe-area-inset-bottom))] right-4 z-50 w-[calc(100%-2rem)] max-w-sm sm:bottom-6 sm:right-6">
            {abierto && (
                <div className="mb-3 overflow-hidden rounded-3xl border border-wayna-100 bg-white shadow-2xl">
                    <div className="border-b border-wayna-100 bg-wayna-50 px-5 py-4">
                        <div className="flex items-start justify-between gap-3">
                            <div className="flex items-start gap-3">
                                <ApplicationLogo size="xs" tone="light" />
                                <div>
                                <p className="text-xs font-bold uppercase tracking-[0.2em] text-wayna-600">
                                    {t('chat.etiqueta', 'Asistente Wayna')}
                                </p>
                                <h3 className="mt-1 text-base font-black text-wayna-950">
                                    {t('chat.titulo', '¿Necesitas ayuda?')}
                                </h3>
                                </div>
                            </div>

                            <button
                                type="button"
                                onClick={() => setAbierto(false)}
                                className="rounded-full px-2 py-1 text-sm font-bold text-stone-500 hover:bg-white hover:text-wayna-900"
                                aria-label={t('chat.cerrar', 'Cerrar chat')}
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    <div className="max-h-[min(55dvh,24rem)] space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-5">
                        <div className="rounded-2xl bg-wayna-50 px-4 py-3 text-sm leading-relaxed text-stone-700">
                            {t(
                                'chat.mensajeInicial',
                                'Puedes preguntarme cómo donar, cómo funciona el pago en efectivo, qué significa pendiente o cómo se actualiza la barra de progreso.'
                            )}
                        </div>

                        {respuesta && (
                            <div className="space-y-2">
                                {respuesta.question && (
                                    <div className="ml-auto max-w-[90%] rounded-2xl bg-wayna-700 px-4 py-3 text-sm leading-relaxed text-white">
                                        {respuesta.question}
                                    </div>
                                )}

                                <div className="mr-auto max-w-[92%] rounded-2xl border border-wayna-100 bg-white px-4 py-3 text-sm leading-relaxed text-stone-700 shadow-sm">
                                    {respuesta.answer}
                                </div>
                            </div>
                        )}

                        {!respuesta && (
                            <div className="rounded-2xl border border-dashed border-wayna-200 px-4 py-3 text-sm leading-relaxed text-stone-500">
                                {t(
                                    'chat.sinRespuestaAun',
                                    'Escribe una pregunta para recibir una respuesta rápida.'
                                )}
                            </div>
                        )}
                    </div>

                    <form onSubmit={enviarPregunta} className="border-t border-wayna-100 p-4">
                        <label htmlFor="pregunta-chat" className="sr-only">
                            {t('chat.pregunta', 'Pregunta')}
                        </label>

                        <textarea
                            id="pregunta-chat"
                            rows="2"
                            value={data.pregunta}
                            onChange={(e) => setData('pregunta', e.target.value)}
                            placeholder={t(
                                'chat.placeholder',
                                'Ejemplo: ¿Cómo puedo donar?'
                            )}
                            className="block w-full resize-none rounded-2xl border-wayna-200 text-sm shadow-sm focus:border-wayna-500 focus:ring-wayna-500"
                            maxLength={300}
                        />

                        {errors.pregunta && (
                            <p className="mt-2 text-xs font-semibold text-red-600">
                                {errors.pregunta}
                            </p>
                        )}

                        <button
                            type="submit"
                            disabled={processing || !data.pregunta.trim()}
                            className="mt-3 w-full rounded-2xl bg-wayna-700 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-wayna-800 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {processing
                                ? t('chat.enviando', 'Enviando...')
                                : t('chat.enviar', 'Enviar pregunta')}
                        </button>
                    </form>
                </div>
            )}

            {!abierto && (
                <button
                    type="button"
                    onClick={() => setAbierto(true)}
                    className="touch-target ml-auto flex min-h-11 items-center gap-2 rounded-full bg-wayna-700 px-5 py-3 text-sm font-black text-white shadow-xl transition hover:bg-wayna-800"
                >
                    <span className="flex h-2.5 w-2.5 rounded-full bg-white" />
                    {t('chat.botonAbrir', 'Ayuda')}
                </button>
            )}
        </div>
    );
}