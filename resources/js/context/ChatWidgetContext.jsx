import { enviarPreguntaChat } from '@/utils/chatApi';
import { setWaynaEnterSkip } from '@/utils/waynaEnterSkip';
import { usePage } from '@inertiajs/react';
import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';

const ChatWidgetContext = createContext(null);

export function ChatWidgetProvider({ children, contextEmprendedorId = null }) {
    const { locale } = usePage().props;

    const [abierto, setAbierto] = useState(false);
    const [historial, setHistorial] = useState([]);
    const [pregunta, setPregunta] = useState('');
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState({});

    const idiomaActivo = locale || 'es';

    const abrir = useCallback(() => setAbierto(true), []);
    const cerrar = useCallback(() => setAbierto(false), []);
    const alternar = useCallback(() => setAbierto((prev) => !prev), []);

    useEffect(() => {
        setErrors((prev) => (prev.pregunta ? {} : prev));
    }, [pregunta]);

    const enviarPregunta = useCallback(
        async (e) => {
            e.preventDefault();

            const texto = pregunta.trim();

            if (!texto || processing) {
                return;
            }

            setProcessing(true);
            setErrors({});
            setWaynaEnterSkip();

            try {
                const rag = await enviarPreguntaChat({
                    pregunta: texto,
                    idioma: idiomaActivo,
                    context_emprendedor_id: contextEmprendedorId,
                });

                if (rag) {
                    setHistorial((prev) => [...prev, rag]);
                }

                setPregunta('');
                setAbierto(true);
            } catch (error) {
                setErrors({
                    pregunta: error?.message ?? 'No se pudo enviar la pregunta.',
                });
            } finally {
                setProcessing(false);
            }
        },
        [pregunta, processing, idiomaActivo, contextEmprendedorId],
    );

    const value = useMemo(
        () => ({
            abierto,
            abrir,
            cerrar,
            alternar,
            historial,
            pregunta,
            setPregunta,
            processing,
            errors,
            enviarPregunta,
            contextEmprendedorId,
        }),
        [
            abierto,
            abrir,
            cerrar,
            alternar,
            historial,
            pregunta,
            processing,
            errors,
            enviarPregunta,
            contextEmprendedorId,
        ],
    );

    return (
        <ChatWidgetContext.Provider value={value}>{children}</ChatWidgetContext.Provider>
    );
}

export function useChatWidget() {
    const ctx = useContext(ChatWidgetContext);

    if (!ctx) {
        throw new Error('useChatWidget debe usarse dentro de ChatWidgetProvider');
    }

    return ctx;
}
