import { Link } from '@inertiajs/react';

/**
 * Enlace “volver” con estilo Wayna admin.
 */
export default function AdminBackLink({ href, children = 'Volver al listado' }) {
    return (
        <Link
            href={href}
            className="group mb-6 inline-flex items-center gap-2 rounded-full border border-wayna-200 bg-white/90 px-4 py-2 text-sm font-semibold text-wayna-800 shadow-sm backdrop-blur-sm transition hover:border-wayna-300 hover:bg-wayna-50"
        >
            <span className="transition-transform group-hover:-translate-x-0.5" aria-hidden>
                ←
            </span>
            {children}
        </Link>
    );
}
