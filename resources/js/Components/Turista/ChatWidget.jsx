import ApplicationLogo from '@/Components/ApplicationLogo';
import { useChatWidget } from '@/context/ChatWidgetContext';
import { useWaynaSectionNav } from '@/context/WaynaSectionNavContext';
import { seccionDesdeHref } from '@/utils/chatApi';
import { setWaynaEnterSkip } from '@/utils/waynaEnterSkip';
import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import { useTranslation } from 'react-i18next';

export { ChatWidgetProvider } from '@/context/ChatWidgetContext';

function ChatWidgetPortal({ children }) {
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

    if (!mounted) {
        return null;
    }

    return createPortal(children, document.body);
}

function ChatAccionLink({ href, label, className }) {
    const { goToSection } = useWaynaSectionNav();

    const manejarClick = (e) => {
        setWaynaEnterSkip();

        const seccionId = seccionDesdeHref(href);

        if (seccionId) {
            e.preventDefault();
            goToSection(seccionId);
        }
    };

    return (
        <Link href={href} preserveScroll onClick={manejarClick} className={className}>
            {label}
        </Link>
    );
}

export default function ChatWidget() {
    const { t } = useTranslation();
    const {
        abierto,
        abrir,
        cerrar,
        historial,
        pregunta,
        setPregunta,
        processing,
        errors,
        enviarPregunta,
    } = useChatWidget();

    const botonAccionClass = (variant) =>
        variant === 'primary'
            ? 'bg-wayna-700 text-white hover:bg-wayna-800'
            : 'border border-wayna-200 bg-white text-wayna-800 hover:bg-wayna-50';

    return (
        <ChatWidgetPortal>
            <div className="pointer-events-none fixed inset-0 z-[55]">
                {abierto && (
                    <div
                        id="wayna-chat-panel"
                        role="dialog"
                        aria-modal="false"
                        aria-label={t('chat.titulo')}
                        className="pointer-events-auto fixed bottom-[calc(4.75rem+env(safe-area-inset-bottom))] right-4 flex max-h-[min(70dvh,28rem)] w-[calc(100%-2rem)] max-w-sm flex-col overflow-hidden rounded-3xl border border-wayna-100 bg-white shadow-2xl sm:bottom-[calc(5.25rem+env(safe-area-inset-bottom))] sm:right-6"
                    >
                        <div className="shrink-0 border-b border-wayna-100 bg-wayna-50 px-5 py-4">
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex items-start gap-3">
                                    <ApplicationLogo size="xs" tone="light" />
                                    <div>
                                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-wayna-600">
                                            {t('chat.etiqueta')}
                                        </p>
                                        <h3 className="mt-1 text-base font-black text-wayna-950">
                                            {t('chat.titulo')}
                                        </h3>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    onClick={cerrar}
                                    className="rounded-full px-2 py-1 text-sm font-bold text-stone-500 hover:bg-white hover:text-wayna-900"
                                    aria-label={t('chat.cerrar')}
                                >
                                    ✕
                                </button>
                            </div>
                        </div>

                        <div className="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-5">
                            <div className="rounded-2xl bg-wayna-50 px-4 py-3 text-sm leading-relaxed text-stone-700">
                                {t('chat.mensajeInicial')}
                            </div>

                            {historial.map((mensaje, index) => (
                                <div
                                    key={`${mensaje.matched_id ?? 'msg'}-${index}`}
                                    className="space-y-2"
                                >
                                    {mensaje.question && (
                                        <div className="ml-auto max-w-[90%] rounded-2xl bg-wayna-700 px-4 py-3 text-sm leading-relaxed text-white">
                                            {mensaje.question}
                                        </div>
                                    )}

                                    <div className="mr-auto max-w-[92%] space-y-2">
                                        <div className="rounded-2xl border border-wayna-100 bg-white px-4 py-3 text-sm leading-relaxed text-stone-700 shadow-sm">
                                            {mensaje.answer}
                                        </div>

                                        {Array.isArray(mensaje.actions) &&
                                            mensaje.actions.length > 0 && (
                                                <div className="flex flex-col gap-2">
                                                    {mensaje.actions.map((accion) => (
                                                        <ChatAccionLink
                                                            key={`${accion.href}-${accion.label}`}
                                                            href={accion.href}
                                                            label={accion.label}
                                                            className={`inline-flex min-h-10 items-center justify-center rounded-xl px-3 py-2 text-center text-xs font-bold transition sm:text-sm ${botonAccionClass(
                                                                accion.variant,
                                                            )}`}
                                                        />
                                                    ))}
                                                </div>
                                            )}
                                    </div>
                                </div>
                            ))}

                            {historial.length === 0 && (
                                <div className="rounded-2xl border border-dashed border-wayna-200 px-4 py-3 text-sm leading-relaxed text-stone-500">
                                    {t('chat.sinRespuestaAun')}
                                </div>
                            )}
                        </div>

                        <form
                            onSubmit={enviarPregunta}
                            className="shrink-0 border-t border-wayna-100 p-4"
                        >
                            <label htmlFor="pregunta-chat" className="sr-only">
                                {t('chat.pregunta')}
                            </label>

                            <textarea
                                id="pregunta-chat"
                                rows="2"
                                value={pregunta}
                                onChange={(e) => setPregunta(e.target.value)}
                                placeholder={t('chat.placeholder')}
                                className="input-wayna min-h-[2.5rem] resize-none text-sm"
                                maxLength={300}
                            />

                            {errors.pregunta && (
                                <p className="mt-2 text-xs font-semibold text-red-600">
                                    {errors.pregunta}
                                </p>
                            )}

                            <button
                                type="submit"
                                disabled={processing || !pregunta.trim()}
                                className="mt-3 w-full rounded-2xl bg-wayna-700 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-wayna-800 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {processing ? t('chat.enviando') : t('chat.enviar')}
                            </button>
                        </form>
                    </div>
                )}

                {!abierto && (
                    <button
                        type="button"
                        onClick={abrir}
                        aria-controls="wayna-chat-panel"
                        aria-expanded={abierto}
                        className="touch-target pointer-events-auto fixed bottom-[max(1rem,env(safe-area-inset-bottom))] right-4 flex min-h-11 items-center gap-2 rounded-full bg-wayna-700 px-5 py-3 text-sm font-black text-white shadow-xl transition hover:bg-wayna-800 sm:bottom-6 sm:right-6"
                    >
                        <span className="flex h-2.5 w-2.5 rounded-full bg-white" aria-hidden />
                        {t('chat.botonAbrir')}
                    </button>
                )}
            </div>
        </ChatWidgetPortal>
    );
}
