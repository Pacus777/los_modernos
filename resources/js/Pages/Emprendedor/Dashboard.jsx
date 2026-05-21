import LogoutButton from '@/Components/LogoutButton';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ emprendedor, usuario }) {
    return (
        <EmprendedorLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Panel emprendedor
                    </p>
                    <h2 className="mt-1 text-xl font-bold text-wayna-950 sm:text-2xl">
                        Hola, {usuario.name}
                    </h2>
                </div>
            }
        >
            <Head title="Mi panel — WAYNA" />

            <div className="mx-auto max-w-2xl space-y-6">
                {emprendedor ? (
                    <>
                        <div className="overflow-hidden rounded-3xl border border-wayna-200 bg-white shadow-lg ring-1 ring-black/[0.03]">
                            <div className="header-wayna-gradient px-6 py-5">
                                <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-orange-100/95">
                                    Tu perfil en WAYNA
                                </p>
                                <p className="mt-2 text-lg font-bold text-white">
                                    {emprendedor.nombre_completo}
                                </p>
                                <p className="mt-1 text-sm text-orange-50/90">
                                    Estado:{' '}
                                    <span className="font-semibold text-white">
                                        {emprendedor.estado}
                                    </span>
                                </p>
                            </div>
                            <div className="space-y-4 p-6">
                                <p className="text-sm text-stone-600">
                                    Meta de apoyo registrada:{' '}
                                    <strong className="text-wayna-950">
                                        Bs {emprendedor.meta_monto.toLocaleString('es-BO')}
                                    </strong>
                                </p>
                                <Link
                                    href={emprendedor.perfil_publico_url}
                                    className="inline-flex w-full items-center justify-center rounded-2xl bg-wayna-600 px-5 py-3 text-sm font-bold text-white shadow-md transition hover:bg-wayna-700 sm:w-auto"
                                >
                                    Ver mi perfil público
                                </Link>
                            </div>
                        </div>

                        <p className="rounded-2xl border border-amber-200/90 bg-amber-50/90 px-4 py-3 text-sm text-amber-950">
                            Próximamente vas a poder editar tu perfil, ver donaciones y publicar desde
                            acá. Por ahora usá el enlace de arriba para revisar cómo te ven los
                            visitantes.
                        </p>
                    </>
                ) : (
                    <div
                        className="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-900"
                        role="alert"
                    >
                        Tu cuenta no está vinculada a un emprendedor. Contactá al administrador de
                        WAYNA.
                    </div>
                )}

                <div className="flex flex-col gap-3 border-t border-wayna-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-xs text-stone-500">
                        Sesión iniciada como{' '}
                        <span className="font-semibold text-stone-700">{usuario.email}</span>
                    </p>
                    <LogoutButton className="inline-flex w-full items-center justify-center rounded-2xl border border-wayna-200 bg-white px-5 py-3 text-sm font-bold text-wayna-800 shadow-sm transition hover:border-wayna-300 hover:bg-wayna-50 sm:w-auto">
                        Cerrar sesión
                    </LogoutButton>
                </div>
            </div>
        </EmprendedorLayout>
    );
}
