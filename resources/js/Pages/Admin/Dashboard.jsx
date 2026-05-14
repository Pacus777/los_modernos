import {
    adminBackdropShort,
    adminListCardHeader,
    adminListCardOuter,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';

function IconoFlecha({ className }) {
    return (
        <svg className={className} viewBox="0 0 20 20" fill="currentColor" aria-hidden>
            <path
                fillRule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clipRule="evenodd"
            />
        </svg>
    );
}

/**
 * Panel principal admin WAYNA — accesos unificados con el resto del diseño.
 */
export default function Dashboard() {
    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        Panel general
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        Elegí un módulo para gestionar emprendedores y campañas de apoyo.
                    </p>
                </div>
            }
        >
            <Head title="Panel — Wayna" />

            <div className="relative py-8">
                <div className={adminBackdropShort} aria-hidden />

                <div className="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className={adminListCardOuter}>
                        <div className={adminListCardHeader}>
                            <h3 className="text-lg font-bold text-wayna-950">Accesos rápidos</h3>
                            <p className="mt-1 text-sm text-stone-600">
                                Misma línea visual que listados y formularios de campañas.
                            </p>
                        </div>

                        <div className="grid gap-4 p-6 sm:grid-cols-2 sm:p-8">
                            <Link
                                href={route('admin.emprendedores.index')}
                                className="group flex flex-col rounded-2xl border border-wayna-200/90 bg-gradient-to-br from-white to-wayna-50/50 p-5 shadow-sm transition hover:border-wayna-300 hover:shadow-md"
                            >
                                <span className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                                    Directorio
                                </span>
                                <span className="mt-2 text-lg font-bold text-wayna-950">Emprendedores</span>
                                <span className="mt-1 flex-1 text-sm text-stone-600">
                                    Altas, fotos, meta referencial y QR de perfil.
                                </span>
                                <span className="mt-4 inline-flex items-center gap-1 text-sm font-bold text-wayna-700">
                                    Ir
                                    <IconoFlecha className="h-4 w-4 transition group-hover:translate-x-0.5" />
                                </span>
                            </Link>

                            <Link
                                href={route('admin.campanas.index')}
                                className="group flex flex-col rounded-2xl border border-wayna-200/90 bg-gradient-to-br from-white to-orange-50/40 p-5 shadow-sm transition hover:border-wayna-300 hover:shadow-md"
                            >
                                <span className="text-xs font-bold uppercase tracking-wide text-wayna-700">
                                    Metas
                                </span>
                                <span className="mt-2 text-lg font-bold text-wayna-950">Campañas</span>
                                <span className="mt-1 flex-1 text-sm text-stone-600">
                                    Vinculación a emprendedor, estado y fechas de la meta pública.
                                </span>
                                <span className="mt-4 inline-flex items-center gap-1 text-sm font-bold text-wayna-700">
                                    Ir
                                    <IconoFlecha className="h-4 w-4 transition group-hover:translate-x-0.5" />
                                </span>
                            </Link>
                        </div>

                        <div className="border-t border-wayna-100 bg-wayna-50/40 px-6 py-4 sm:px-8">
                            <p className="text-center text-sm text-stone-600">
                                ¿Primera vez?{' '}
                                <Link
                                    href={route('admin.emprendedores.create')}
                                    className="font-bold text-wayna-800 underline decoration-wayna-300 decoration-2 underline-offset-2 hover:text-wayna-950"
                                >
                                    Crear un emprendedor
                                </Link>
                                {' · '}
                                <Link
                                    href={route('admin.campanas.create')}
                                    className="font-bold text-wayna-800 underline decoration-wayna-300 decoration-2 underline-offset-2 hover:text-wayna-950"
                                >
                                    Nueva campaña
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
