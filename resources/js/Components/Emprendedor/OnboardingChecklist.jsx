import { Link, usePage } from '@inertiajs/react';
import React, { useState } from 'react';

/**
 * E-12: Componente interactivo de checklist de onboarding para emprendedores.
 */
export default function OnboardingChecklist() {
    const { emprendedorOnboarding } = usePage().props;
    const [mostrarDetalles, setMostrarDetalles] = useState(true);

    if (!emprendedorOnboarding) {
        return null;
    }

    const { completo, porcentaje, items } = emprendedorOnboarding;

    return (
        <article data-testid="emprendedor-onboarding-checklist" className="overflow-hidden rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-50/70 via-white to-white p-5 shadow-md shadow-amber-500/[0.03] ring-1 ring-amber-100/50">
            <header className="flex flex-wrap items-start justify-between gap-4">
                <div className="flex-1 min-w-0">
                    <span className="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 tracking-wide">
                        {completo ? '¡Perfil Completo!' : 'Completá tu perfil público'}
                    </span>
                    <h3 className="mt-2 text-base font-black text-wayna-950">
                        {completo 
                            ? '¡Excelente trabajo! Tu perfil está listo y visible para el público.' 
                            : 'Faltan detalles para activar todas las funciones de tu panel.'}
                    </h3>
                    <p className="mt-1 text-xs text-stone-600">
                        {completo 
                            ? 'Has completado todos los pasos obligatorios. Los visitantes ahora pueden conocer tu historia.'
                            : 'Para desbloquear el acceso al dashboard completo y gestionar tus publicaciones, completa los requisitos indicados abajo.'}
                    </p>
                </div>

                <button
                    type="button"
                    onClick={() => setMostrarDetalles(!mostrarDetalles)}
                    className="text-xs font-bold text-amber-700 underline underline-offset-2 hover:text-amber-900 shrink-0"
                >
                    {mostrarDetalles ? 'Ocultar checklist' : 'Ver checklist'}
                </button>
            </header>

            {/* Barra de progreso */}
            <div className="mt-5">
                <div className="flex items-center justify-between text-xs font-bold text-stone-700">
                    <span>Progreso de Onboarding</span>
                    <span className="tabular-nums">{porcentaje}%</span>
                </div>
                <div className="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-stone-200">
                    <div
                        className="h-full rounded-full bg-amber-500 transition-all duration-550 ease-out"
                        style={{ width: `${porcentaje}%` }}
                    />
                </div>
            </div>

            {/* Lista de tareas */}
            {mostrarDetalles && (
                <ul className="mt-6 divide-y divide-stone-100 border-t border-stone-100">
                    {items.map((item) => (
                        <li key={item.id} data-testid={item.id === 'datos_basicos' ? 'onboarding-item-datos-principales' : `onboarding-item-${item.id}`} className="flex items-start justify-between gap-4 py-4.5">
                            <div className="flex items-start gap-3 min-w-0">
                                {/* Icono de estado */}
                                <div className="mt-0.5 shrink-0">
                                    {item.completo ? (
                                        <svg className="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    ) : (
                                        <div className="h-5 w-5 rounded-full border-2 border-stone-300 bg-white" />
                                    )}
                                </div>

                                <div className="min-w-0">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h4 className={`text-sm font-bold ${item.completo ? 'text-stone-500 line-through' : 'text-stone-900'}`}>
                                            {item.titulo}
                                        </h4>
                                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase ${
                                            item.bloqueante 
                                                ? 'bg-red-50 text-red-700 border border-red-100' 
                                                : 'bg-stone-100 text-stone-600'
                                        }`}>
                                            {item.bloqueante ? 'Obligatorio' : 'Recomendado'}
                                        </span>
                                    </div>
                                    <p className="mt-1 text-xs text-stone-500 leading-relaxed max-w-xl">
                                        {item.descripcion}
                                    </p>
                                </div>
                            </div>

                            {/* Botón de acción */}
                            {!item.completo && item.url && (
                                <Link
                                    href={item.url}
                                    className="shrink-0 inline-flex items-center justify-center rounded-xl border border-amber-200 bg-white px-3.5 py-1.5 text-xs font-bold text-amber-800 transition hover:bg-amber-50"
                                >
                                    Completar
                                </Link>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </article>
    );
}
