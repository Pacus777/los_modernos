import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

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

export default function EmprendedorNotificacionesBell() {
    const resumen = usePage().props.emprendedorNotificaciones;
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
        router.patch(route('emprendedor.notificaciones.leer', id), {}, { preserveScroll: true });
    };

    if (!resumen) {
        return null;
    }

    return (
        <div className="relative" ref={panelRef}>
            <button
                type="button"
                aria-label={`Notificaciones${noLeidas > 0 ? `, ${noLeidas} sin leer` : ''}`}
                aria-expanded={abierto}
                onClick={() => setAbierto((o) => !o)}
                className="relative flex h-10 w-10 items-center justify-center rounded-xl border border-wayna-200 bg-white text-wayna-800 shadow-sm transition hover:bg-wayna-50"
            >
                <IconBell />
                {noLeidas > 0 && (
                    <span className="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
                        {noLeidas > 9 ? '9+' : noLeidas}
                    </span>
                )}
            </button>

            {abierto && (
                <div className="absolute right-0 z-50 mt-2 w-[min(100vw-2rem,22rem)] overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl">
                    <div className="flex items-center justify-between border-b border-stone-100 bg-stone-50 px-4 py-3">
                        <p className="text-sm font-bold text-stone-900">Avisos en tu panel</p>
                        {noLeidas > 0 && (
                            <button
                                type="button"
                                className="text-xs font-semibold text-wayna-700 hover:text-wayna-900"
                                onClick={() => {
                                    router.post(route('emprendedor.notificaciones.marcar-todas'), {}, {
                                        preserveScroll: true,
                                        onSuccess: () => setAbierto(false),
                                    });
                                }}
                            >
                                Marcar todas
                            </button>
                        )}
                    </div>

                    <ul className="max-h-80 overflow-y-auto">
                        {items.length === 0 ? (
                            <li className="px-4 py-6 text-center text-sm text-stone-600">
                                No tenés avisos por ahora.
                            </li>
                        ) : (
                            items.map((n) => (
                                <li
                                    key={n.id}
                                    className={`border-b border-stone-50 px-4 py-3 ${
                                        !n.leida ? 'bg-amber-50/60' : ''
                                    }`}
                                >
                                    <p className="text-sm font-bold text-stone-900">{n.titulo}</p>
                                    <p className="mt-0.5 text-sm leading-snug text-stone-700">{n.mensaje}</p>
                                    <div className="mt-2 flex items-center justify-between gap-2">
                                        <span className="text-xs text-stone-500">{n.created_at_humano}</span>
                                        {!n.leida && (
                                            <button
                                                type="button"
                                                className="text-xs font-semibold text-wayna-700 hover:underline"
                                                onClick={() => marcarLeida(n.id)}
                                            >
                                                Marcar leída
                                            </button>
                                        )}
                                    </div>
                                </li>
                            ))
                        )}
                    </ul>

                    <div className="border-t border-stone-100 bg-stone-50 px-4 py-2.5 text-center">
                        <Link
                            href={route('emprendedor.notificaciones.index')}
                            className="text-sm font-semibold text-wayna-800 hover:text-wayna-950"
                            onClick={() => setAbierto(false)}
                        >
                            Ver todos los avisos
                        </Link>
                    </div>
                </div>
            )}
        </div>
    );
}
