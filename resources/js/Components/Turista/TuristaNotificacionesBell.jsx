import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

function IconBell({ className = 'h-5 w-5' }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            />
        </svg>
    );
}

export default function TuristaNotificacionesBell() {
    const { t } = useTranslation();
    const resumen = usePage().props.turistaNotificaciones;
    const [abierto, setAbierto] = useState(false);
    const panelRef = useRef(null);

    const noLeidas = resumen?.no_leidas ?? 0;
    const items = resumen?.items ?? [];

    useEffect(() => {
        const cerrar = (e) => {
            if (panelRef.current && !panelRef.current.contains(e.target)) {
                setAbierto(false);
            }
        };
        if (abierto) {
            document.addEventListener('mousedown', cerrar);
        }
        return () => document.removeEventListener('mousedown', cerrar);
    }, [abierto]);

    const marcarLeida = (id) => {
        router.patch(route('turista.notificaciones.leer', id), {}, { preserveScroll: true });
    };

    if (!resumen) {
        return null;
    }

    return (
        <div className="relative shrink-0" ref={panelRef}>
            <button
                type="button"
                aria-label={`Notificaciones turista${noLeidas > 0 ? `, ${noLeidas} sin leer` : ''}`}
                aria-expanded={abierto}
                onClick={() => setAbierto((o) => !o)}
                className="relative flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-xl border border-white/40 bg-white/20 text-white shadow-sm transition hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/40"
            >
                <IconBell className="h-5 w-5 sm:h-6 sm:w-6" />
                {noLeidas > 0 && (
                    <span className="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-extrabold text-white ring-2 ring-wayna-500">
                        {noLeidas > 9 ? '9+' : noLeidas}
                    </span>
                )}
            </button>

            {abierto && (
                <div className="absolute right-0 z-50 mt-2 w-[min(calc(100vw-2rem),22rem)] overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-2xl animate-fade-in text-stone-900">
                    <div className="flex items-center justify-between border-b border-stone-100 bg-stone-50 px-4 py-3">
                        <p className="text-sm font-black text-stone-900">Tus Novedades</p>
                        {noLeidas > 0 && (
                            <button
                                type="button"
                                className="text-xs font-bold text-wayna-700 hover:text-wayna-900"
                                onClick={() => {
                                    router.post(route('turista.notificaciones.marcar-todas'), {}, {
                                        preserveScroll: true,
                                        onSuccess: () => setAbierto(false),
                                    });
                                }}
                            >
                                Marcar todas
                            </button>
                        )}
                    </div>

                    <ul className="max-h-80 overflow-y-auto divide-y divide-stone-100">
                        {items.length === 0 ? (
                            <li className="px-4 py-8 text-center text-sm text-stone-500 leading-relaxed">
                                No tienes novedades por el momento.
                            </li>
                        ) : (
                            items.map((n) => (
                                <li
                                    key={n.id}
                                    className={`px-4 py-3 transition hover:bg-stone-50/80 ${
                                        !n.leida ? 'bg-orange-50/40' : ''
                                    }`}
                                >
                                    <div className="flex items-start justify-between gap-2">
                                        <Link
                                            href={n.url || '#'}
                                            onClick={() => {
                                                setAbierto(false);
                                                if (!n.leida) {
                                                    marcarLeida(n.id);
                                                }
                                            }}
                                            className="text-left"
                                        >
                                            <p className="text-sm font-bold text-wayna-900 hover:underline">{n.titulo}</p>
                                            <p className="mt-0.5 text-sm leading-snug text-stone-700">{n.mensaje}</p>
                                        </Link>
                                    </div>
                                    <div className="mt-2.5 flex items-center justify-between gap-2 border-t border-stone-100/40 pt-1.5">
                                        <span className="text-[11px] font-medium text-stone-500">{n.created_at_humano}</span>
                                        {!n.leida && (
                                            <button
                                                type="button"
                                                className="text-xs font-bold text-wayna-700 hover:text-wayna-900 hover:underline"
                                                onClick={() => marcarLeida(n.id)}
                                            >
                                                Marcar como leída
                                            </button>
                                        )}
                                    </div>
                                </li>
                            ))
                        )}
                    </ul>

                    <div className="border-t border-stone-100 bg-stone-50 px-4 py-3 text-center">
                        <Link
                            href={route('turista.notificaciones.index')}
                            className="text-sm font-bold text-wayna-800 hover:text-wayna-950 transition hover:underline"
                            onClick={() => setAbierto(false)}
                        >
                            Ver todo el historial
                        </Link>
                    </div>
                </div>
            )}
        </div>
    );
}
