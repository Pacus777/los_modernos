import GuestLayout from '@/Layouts/GuestLayout';
import BarraProgreso from '@/Components/Turista/BarraProgreso';
import AdminPaginator from '@/Components/Admin/AdminPaginator';
import WaynaEnterTransition from '@/Components/Wayna/WaynaEnterTransition';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { adminInputClass } from '@/Components/Admin/adminUi';

export default function Index({
    emprendedores = {},
    filtros = {},
    catalogos = {},
}) {
    const { t } = useTranslation();

    const [q, setQ] = useState(filtros.q ?? '');
    const [departamento, setDepartamento] = useState(filtros.departamento ?? '');
    const [tipoEmprendimiento, setTipoEmprendimiento] = useState(filtros.tipo_emprendimiento ?? '');
    const [orden, setOrden] = useState(filtros.orden ?? 'recientes');

    const aplicarFiltros = (parcial = {}) => {
        const payload = {
            q: parcial.q !== undefined ? parcial.q : q,
            departamento: parcial.departamento !== undefined ? parcial.departamento : departamento,
            tipo_emprendimiento: parcial.tipo_emprendimiento !== undefined ? parcial.tipo_emprendimiento : tipoEmprendimiento,
            orden: parcial.orden !== undefined ? parcial.orden : orden,
        };

        router.get(route('turista.emprendedores.index'), payload, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        aplicarFiltros();
    };

    const handleClear = () => {
        setQ('');
        setDepartamento('');
        setTipoEmprendimiento('');
        setOrden('recientes');

        router.get(route('turista.emprendedores.index'), {}, {
            preserveState: false,
            preserveScroll: true,
        });
    };

    const records = emprendedores.data ?? [];

    return (
        <WaynaEnterTransition variant="navigate">
            <GuestLayout variant="full" contentClassName="!max-w-7xl">
                <Head title="Emprendedores WAYNA - Explorar" />

                <div className="mx-auto max-w-7xl px-2 sm:px-4 py-4">
                    {/* Header */}
                    <header className="max-w-3xl mb-8">
                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-wayna-600">
                            WAYNA Red Social
                        </p>
                        <h1 className="mt-2 text-3xl font-black text-wayna-950 sm:text-4xl">
                            Emprendedores WAYNA
                        </h1>
                        <p className="mt-2 text-stone-600">
                            Descubre emprendimientos turísticos locales y apoya directamente sus metas activas.
                        </p>
                    </header>

                    {/* Filtros */}
                    <form
                        onSubmit={handleSubmit}
                        className="mb-8 rounded-3xl border border-wayna-100 bg-white p-4 shadow-lg shadow-wayna-900/5 sm:p-6"
                    >
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                            <div className="lg:col-span-3">
                                <label htmlFor="q" className="text-xs font-bold uppercase text-wayna-700">
                                    Buscar
                                </label>
                                <input
                                    id="q"
                                    type="search"
                                    value={q}
                                    onChange={(e) => setQ(e.target.value)}
                                    placeholder="Nombre, apellidos o descripción..."
                                    className={`${adminInputClass} mt-1 text-sm`}
                                />
                            </div>

                            <div className="lg:col-span-2">
                                <label htmlFor="tipo" className="text-xs font-bold uppercase text-wayna-700">
                                    Tipo
                                </label>
                                <select
                                    id="tipo"
                                    value={tipoEmprendimiento}
                                    onChange={(e) => {
                                        setTipoEmprendimiento(e.target.value);
                                        aplicarFiltros({ tipo_emprendimiento: e.target.value });
                                    }}
                                    className={`${adminInputClass} mt-1 text-sm`}
                                >
                                    <option value="">Todos los tipos</option>
                                    {(catalogos.tiposEmprendimiento ?? []).map((opt) => (
                                        <option key={opt.value} value={opt.value}>
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="lg:col-span-2">
                                <label htmlFor="depto" className="text-xs font-bold uppercase text-wayna-700">
                                    Departamento
                                </label>
                                <select
                                    id="depto"
                                    value={departamento}
                                    onChange={(e) => {
                                        setDepartamento(e.target.value);
                                        aplicarFiltros({ departamento: e.target.value });
                                    }}
                                    className={`${adminInputClass} mt-1 text-sm`}
                                >
                                    <option value="">Todos los deptos</option>
                                    {(catalogos.departamentos ?? []).map((opt) => (
                                        <option key={opt.value} value={opt.value}>
                                            {opt.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="lg:col-span-2">
                                <label htmlFor="orden" className="text-xs font-bold uppercase text-wayna-700">
                                    Ordenar por
                                </label>
                                <select
                                    id="orden"
                                    value={orden}
                                    onChange={(e) => {
                                        setOrden(e.target.value);
                                        aplicarFiltros({ orden: e.target.value });
                                    }}
                                    className={`${adminInputClass} mt-1 text-sm`}
                                >
                                    <option value="recientes">Más recientes</option>
                                    <option value="mas_publicaciones">Más publicaciones</option>
                                    <option value="mas_apoyados">Más apoyados (donaciones)</option>
                                    <option value="cerca_meta">Cerca de cumplir meta</option>
                                </select>
                            </div>

                            <div className="flex gap-2 sm:col-span-2 lg:col-span-3">
                                <button type="submit" className="btn-wayna-primary flex-1 touch-target !px-4 text-center justify-center">
                                    Filtrar
                                </button>
                                <button
                                    type="button"
                                    onClick={handleClear}
                                    className="inline-flex flex-1 items-center justify-center rounded-2xl border border-stone-200 bg-stone-50 px-4 py-2.5 text-xs font-bold text-stone-700 hover:bg-stone-100 transition duration-150"
                                >
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </form>

                    {/* Results count info */}
                    <div className="mb-6 flex justify-between items-center text-sm text-stone-600 font-semibold">
                        <span>
                            {records.length === 1
                                ? 'Se encontró 1 emprendedor'
                                : `Se encontraron ${emprendedores.total ?? records.length} emprendedores`}
                        </span>
                    </div>

                    {/* Empty State */}
                    {records.length === 0 ? (
                        <div className="rounded-3xl border border-dashed border-wayna-200 bg-wayna-50/50 px-6 py-16 text-center">
                            <p className="text-lg font-bold text-wayna-900">No encontramos emprendedores</p>
                            <p className="mt-2 text-sm text-stone-600">
                                Intenta limpiar los filtros o realizar una búsqueda con palabras distintas.
                            </p>
                            <button
                                type="button"
                                onClick={handleClear}
                                className="mt-4 inline-flex items-center justify-center rounded-2xl bg-wayna-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-wayna-700 transition"
                            >
                                Limpiar filtros
                            </button>
                        </div>
                    ) : (
                        /* Grid of Entrepreneurs */
                        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                            {records.map((emprendedor) => {
                                const tieneMeta = emprendedor.campana_activa || Number(emprendedor.progreso?.meta ?? 0) > 0;
                                return (
                                    <article
                                        key={emprendedor.id}
                                        className="group flex flex-col overflow-hidden rounded-3xl border border-wayna-100 bg-white shadow-md shadow-wayna-900/5 transition duration-300 hover:-translate-y-1 hover:shadow-xl"
                                    >
                                        {/* Cover Photo */}
                                        <div className="relative aspect-[4/3] bg-gradient-to-br from-wayna-100 to-wayna-50">
                                            {emprendedor.foto_portada ? (
                                                <img
                                                    src={emprendedor.foto_portada}
                                                    alt={emprendedor.nombre_completo}
                                                    className="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                                    loading="lazy"
                                                />
                                            ) : (
                                                <div className="flex h-full w-full items-center justify-center text-6xl font-black text-wayna-400">
                                                    {emprendedor.nombre?.charAt(0) || 'W'}
                                                </div>
                                            )}

                                            {/* Badges Overlay */}
                                            <div className="absolute right-3 top-3 flex flex-wrap gap-1.5">
                                                <span className="rounded-full bg-black/55 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wide text-white backdrop-blur-sm">
                                                    Posts: {emprendedor.publicaciones_count}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Card Body */}
                                        <div className="flex flex-1 flex-col p-6">
                                            <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-wayna-600">
                                                Emprendedor WAYNA
                                            </p>

                                            <h2 className="mt-1.5 text-xl font-black leading-tight text-wayna-950">
                                                {emprendedor.nombre_completo}
                                            </h2>

                                            {/* Tags */}
                                            <div className="mt-2.5 flex flex-wrap gap-1.5">
                                                {emprendedor.tipo_emprendimiento_etiqueta ? (
                                                    <span className="inline-flex rounded-full bg-wayna-100 px-3 py-1 text-xs font-bold text-wayna-800">
                                                        {emprendedor.tipo_emprendimiento_etiqueta}
                                                    </span>
                                                ) : null}
                                                {emprendedor.departamento_etiqueta ? (
                                                    <span className="inline-flex rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                                        {emprendedor.departamento_etiqueta}
                                                    </span>
                                                ) : null}
                                            </div>

                                            {/* Description */}
                                            <p className="mt-4 line-clamp-3 flex-1 text-sm leading-relaxed text-stone-600">
                                                {emprendedor.descripcion || 'Sin descripción disponible.'}
                                            </p>

                                            {/* Meta Progress */}
                                            <div className="mt-5 pt-4 border-t border-stone-100">
                                                {emprendedor.campana_activa ? (
                                                    <BarraProgreso
                                                        porcentaje={emprendedor.progreso.porcentaje}
                                                        montoRecaudado={emprendedor.progreso.monto_recaudado}
                                                        meta={emprendedor.progreso.meta}
                                                        titulo={emprendedor.campana_activa.titulo}
                                                    />
                                                ) : tieneMeta ? (
                                                    <div className="rounded-xl border border-amber-200 bg-amber-50/70 p-3">
                                                        <p className="text-xs font-bold text-amber-900">
                                                            Meta de referencia:{' '}
                                                            <span className="font-extrabold text-amber-950">
                                                                Bs. {Number(emprendedor.progreso.meta).toFixed(2)}
                                                            </span>
                                                        </p>
                                                    </div>
                                                ) : (
                                                    <div className="rounded-xl border border-stone-200 bg-stone-50 p-3 text-center">
                                                        <p className="text-xs font-bold text-stone-500 uppercase tracking-wider">
                                                            Sin meta activa
                                                        </p>
                                                    </div>
                                                )}
                                            </div>

                                            {/* Actions */}
                                            <div className="mt-5 flex">
                                                <Link
                                                    href={emprendedor.perfil_url}
                                                    className="btn-wayna-primary flex-1 !py-3 text-center text-sm font-bold justify-center"
                                                >
                                                    Ver perfil
                                                </Link>
                                            </div>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                    )}

                    {/* Pagination */}
                    {emprendedores.last_page > 1 && (
                        <div className="mt-12 rounded-3xl overflow-hidden shadow-sm border border-wayna-100 bg-white">
                            <AdminPaginator paginator={emprendedores} etiqueta="emprendedores" />
                        </div>
                    )}
                </div>
            </GuestLayout>
        </WaynaEnterTransition>
    );
}
