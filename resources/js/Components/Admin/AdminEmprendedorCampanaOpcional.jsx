import { adminFormFooterPrimaryBtn, adminFormFooterSecondaryBtn } from '@/Components/Admin/adminUi';
import { Link } from '@inertiajs/react';

/**
 * Paso opcional tras el alta: crear campaña ahora o más tarde.
 */
export default function AdminEmprendedorCampanaOpcional({
    emprendedorId,
    nombreCompleto,
    campanaActiva = null,
}) {
    const urlCrearCampana = route('admin.campanas.create', {
        emprendedor_id: emprendedorId,
    });

    if (campanaActiva) {
        return (
            <section className="rounded-3xl border border-emerald-200 bg-emerald-50/70 p-6 shadow-sm ring-1 ring-emerald-100">
                <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-800">
                    Meta de apoyo
                </p>
                <h3 className="mt-1 text-lg font-bold text-emerald-950">Ya tiene una campaña activa</h3>
                <p className="mt-2 text-sm text-emerald-900/90">
                    <strong>{campanaActiva.titulo}</strong> — meta Bs{' '}
                    {Number(campanaActiva.meta_apoyo).toLocaleString('es-BO')} (visible en el perfil
                    público).
                </p>
                <Link
                    href={route('admin.campanas.edit', campanaActiva.id)}
                    className={`mt-4 inline-flex ${adminFormFooterSecondaryBtn}`}
                >
                    Ver campaña en administración
                </Link>
            </section>
        );
    }

    return (
        <section className="rounded-3xl border border-wayna-200 bg-white p-6 shadow-sm ring-1 ring-black/[0.03]">
            <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-wayna-600">
                Meta de apoyo (campaña)
            </p>
            <h3 className="mt-1 text-lg font-bold text-wayna-950">
                ¿Querés activar la meta de {nombreCompleto} ahora?
            </h3>
            <p className="mt-2 text-sm leading-relaxed text-stone-600">
                El monto que ven los turistas y la barra de progreso viven en una{' '}
                <strong>campaña</strong>, no en el perfil del emprendedor. Podés crearla acá, desde el
                módulo <strong>Campañas</strong>, o dejar que el emprendedor la configure en su panel
                (Mi meta de apoyo).
            </p>

            <div className="mt-5 grid gap-3 sm:grid-cols-1">
                <Link href={urlCrearCampana} className={`text-center ${adminFormFooterPrimaryBtn}`}>
                    Crear campaña ahora
                </Link>
                <p className="rounded-xl border border-wayna-100 bg-wayna-50/50 px-4 py-3 text-xs text-stone-600">
                    <strong>Más tarde:</strong> en el menú <strong>Campañas</strong> elegís a{' '}
                    {nombreCompleto} y definís título, meta en Bs. y fechas.
                </p>
                <p className="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-xs text-stone-600">
                    <strong>Desde el emprendedor:</strong> si le das acceso abajo, podrá crear su meta en{' '}
                    <em>Mi meta de apoyo</em> sin pasar por admin.
                </p>
            </div>
        </section>
    );
}
