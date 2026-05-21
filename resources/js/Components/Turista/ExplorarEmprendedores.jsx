import { adminInputClass } from '@/Components/Admin/adminUi';
import EmprendedorTarjetaExplorar from '@/Components/Turista/EmprendedorTarjetaExplorar';
import { opcionesDepartamentoT, opcionesTipoEmprendimientoT } from '@/utils/catalogosI18n';
import { router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

export default function ExplorarEmprendedores({
    emprendedores = [],
    filtros = {},
    catalogos = {},
}) {
    const { t } = useTranslation();
    const [busqueda, setBusqueda] = useState(filtros.q ?? '');

    const tiposEmprendimiento = useMemo(
        () => opcionesTipoEmprendimientoT(t, catalogos.tiposEmprendimiento ?? []),
        [t, catalogos.tiposEmprendimiento],
    );

    const departamentos = useMemo(
        () => opcionesDepartamentoT(t, catalogos.departamentos ?? []),
        [t, catalogos.departamentos],
    );

    const aplicarFiltros = (parcial = {}) => {
        const payload = {
            q: parcial.q ?? filtros.q ?? '',
            tipo_emprendimiento: parcial.tipo_emprendimiento ?? filtros.tipo_emprendimiento ?? '',
            departamento: parcial.departamento ?? filtros.departamento ?? '',
            punto_id: parcial.punto_id ?? filtros.punto_id ?? '',
        };

        router.get('/', payload, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const enviarBusqueda = (e) => {
        e.preventDefault();
        aplicarFiltros({ q: busqueda.trim() });
    };

    return (
        <section id="explorar" className="scroll-mt-20 border-b border-wayna-100/80 bg-surface py-14 sm:py-20">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <header className="max-w-3xl">
                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-wayna-600">
                        {t('explore.kicker')}
                    </p>
                    <h2 className="mt-2 text-3xl font-black text-wayna-950 sm:text-4xl">
                        {t('explore.title')}
                    </h2>
                    <p className="mt-3 text-base leading-relaxed text-stone-600">{t('explore.subtitle')}</p>
                </header>

                <form
                    onSubmit={enviarBusqueda}
                    className="mt-8 rounded-3xl border border-wayna-100 bg-white p-4 shadow-lg shadow-wayna-900/5 sm:p-5"
                >
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
                        <div className="lg:col-span-4">
                            <label htmlFor="explorar-q" className="text-xs font-bold uppercase text-wayna-700">
                                {t('explore.searchLabel')}
                            </label>
                            <input
                                id="explorar-q"
                                type="search"
                                value={busqueda}
                                onChange={(e) => setBusqueda(e.target.value)}
                                placeholder={t('explore.searchPlaceholder')}
                                className={`${adminInputClass} mt-1 text-sm`}
                            />
                        </div>

                        <div className="lg:col-span-2">
                            <label htmlFor="explorar-tipo" className="text-xs font-bold uppercase text-wayna-700">
                                {t('explore.filterType')}
                            </label>
                            <select
                                id="explorar-tipo"
                                value={filtros.tipo_emprendimiento ?? ''}
                                onChange={(e) =>
                                    aplicarFiltros({ tipo_emprendimiento: e.target.value })
                                }
                                className={`${adminInputClass} mt-1 text-sm`}
                            >
                                <option value="">{t('explore.filterAll')}</option>
                                {tiposEmprendimiento.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="lg:col-span-2">
                            <label htmlFor="explorar-depto" className="text-xs font-bold uppercase text-wayna-700">
                                {t('explore.filterDepartment')}
                            </label>
                            <select
                                id="explorar-depto"
                                value={filtros.departamento ?? ''}
                                onChange={(e) => aplicarFiltros({ departamento: e.target.value })}
                                className={`${adminInputClass} mt-1 text-sm`}
                            >
                                <option value="">{t('explore.filterAll')}</option>
                                {departamentos.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="lg:col-span-3">
                            <label htmlFor="explorar-punto" className="text-xs font-bold uppercase text-wayna-700">
                                {t('explore.filterPoint')}
                            </label>
                            <select
                                id="explorar-punto"
                                value={filtros.punto_id ?? ''}
                                onChange={(e) => aplicarFiltros({ punto_id: e.target.value })}
                                className={`${adminInputClass} mt-1 text-sm`}
                            >
                                <option value="">{t('explore.filterAll')}</option>
                                {(catalogos.puntos ?? []).map((punto) => (
                                    <option key={punto.id} value={punto.id}>
                                        {punto.nombre}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="sm:col-span-2 lg:col-span-1">
                            <button type="submit" className="btn-wayna-primary touch-target w-full !px-3">
                                {t('explore.searchButton')}
                            </button>
                        </div>
                    </div>
                </form>

                <p className="mt-6 text-sm font-semibold text-stone-600">
                    {t('explore.resultsCount', { count: emprendedores.length })}
                </p>

                {emprendedores.length === 0 ? (
                    <div className="mt-8 rounded-3xl border border-dashed border-wayna-200 bg-wayna-50/50 px-6 py-12 text-center">
                        <p className="text-base font-bold text-wayna-900">{t('explore.emptyTitle')}</p>
                        <p className="mt-2 text-sm text-stone-600">{t('explore.emptyBody')}</p>
                    </div>
                ) : (
                    <div className="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        {emprendedores.map((emprendedor) => (
                            <EmprendedorTarjetaExplorar key={emprendedor.id} emprendedor={emprendedor} />
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
