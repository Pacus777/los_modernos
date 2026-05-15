import {
    adminPaginationBtnActive,
    adminPaginationBtnIdle,
} from '@/Components/Admin/adminUi';
import { Link } from '@inertiajs/react';

/**
 * Paginación Inertia con links de Laravel (conserva query string vía withQueryString).
 */
export default function AdminPaginator({ paginator, etiqueta = 'registros' }) {
    if (!paginator || paginator.last_page <= 1) {
        return null;
    }

    const desde = paginator.from ?? 0;
    const hasta = paginator.to ?? 0;
    const total = paginator.total ?? 0;

    return (
        <div className="border-t border-wayna-100 bg-wayna-50/30 px-4 py-4 sm:px-6">
            <p className="mb-3 text-sm text-stone-600">
                Mostrando{' '}
                <span className="font-semibold text-wayna-950">{desde}</span>–
                <span className="font-semibold text-wayna-950">{hasta}</span> de{' '}
                <span className="font-semibold text-wayna-950">{total}</span> {etiqueta}
            </p>
            <nav aria-label="Paginación" className="flex flex-wrap items-center gap-2">
                {paginator.links.map((link, index) => (
                    <Link
                        key={`${link.label}-${index}`}
                        href={link.url || '#'}
                        preserveScroll
                        preserveState
                        className={`${link.active ? adminPaginationBtnActive : adminPaginationBtnIdle} ${!link.url ? 'pointer-events-none opacity-40' : ''}`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </nav>
        </div>
    );
}
