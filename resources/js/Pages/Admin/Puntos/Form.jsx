import AdminBackLink from '@/Components/Admin/AdminBackLink';
import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import AdminFormField from '@/Components/Admin/AdminFormField';
import {
    adminBackdropTall,
    adminFormStack,
    adminInputClass,
    adminLabelUpper,
    adminSectionCard,
    adminSelectClass,
    adminTextareaClass,
    adminFormFooterPrimaryBtn,
    adminFormFooterSecondaryBtn,
} from '@/Components/Admin/adminUi';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

function normalizarIdsSeleccionados(valor) {
    if (!valor) {
        return [];
    }
    const lista = Array.isArray(valor) ? valor : Object.values(valor);
    return lista.map((id) => Number(id)).filter((id) => !Number.isNaN(id));
}

/**
 * Formulario crear / editar punto físico con asociación de emprendedores — T-A28.
 */
export default function Form({
    modo,
    punto = null,
    emprendedores = [],
    emprendedoresSeleccionados = [],
}) {
    const { flash } = usePage().props;
    const esEdicion = modo === 'editar';
    const [busqueda, setBusqueda] = useState('');

    const idsIniciales = useMemo(
        () => normalizarIdsSeleccionados(emprendedoresSeleccionados),
        [emprendedoresSeleccionados],
    );

    const { data, setData, post, put, processing, errors } = useForm({
        nombre: punto?.nombre ?? '',
        descripcion: punto?.descripcion ?? '',
        ubicacion: punto?.ubicacion ?? '',
        estado: punto?.estado ?? 'activo',
        emprendedores: idsIniciales,
    });

    const emprendedoresFiltrados = useMemo(() => {
        const termino = busqueda.trim().toLowerCase();
        if (!termino) {
            return emprendedores;
        }
        return emprendedores.filter((e) =>
            (e.nombre_completo || '').toLowerCase().includes(termino),
        );
    }, [busqueda, emprendedores]);

    const seleccionadosCount = data.emprendedores?.length ?? 0;

    const estaSeleccionado = (id) => data.emprendedores?.includes(Number(id));

    const alternarEmprendedor = (id) => {
        const numId = Number(id);
        const actuales = data.emprendedores ?? [];
        if (actuales.includes(numId)) {
            setData(
                'emprendedores',
                actuales.filter((x) => x !== numId),
            );
        } else {
            setData('emprendedores', [...actuales, numId]);
        }
    };

    const seleccionarTodosVisibles = () => {
        const idsVisibles = emprendedoresFiltrados.map((e) => Number(e.id));
        const actuales = new Set(data.emprendedores ?? []);
        idsVisibles.forEach((id) => actuales.add(id));
        setData('emprendedores', Array.from(actuales));
    };

    const quitarTodosVisibles = () => {
        const idsVisibles = new Set(emprendedoresFiltrados.map((e) => Number(e.id)));
        setData(
            'emprendedores',
            (data.emprendedores ?? []).filter((id) => !idsVisibles.has(id)),
        );
    };

    const enviar = (e) => {
        e.preventDefault();

        if (esEdicion) {
            put(route('admin.puntos.update', punto.id));
            return;
        }

        post(route('admin.puntos.store'));
    };

    return (
        <AdminLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-wayna-600">
                        Wayna admin
                    </p>
                    <h2 className="mt-1 text-2xl font-bold tracking-tight text-wayna-950 sm:text-3xl">
                        {esEdicion ? 'Editar punto físico' : 'Nuevo punto físico'}
                    </h2>
                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-stone-600">
                        {esEdicion
                            ? 'Actualizá los datos y la lista de emprendedores. Al guardar se sincroniza la tabla de asociación.'
                            : 'Definí el punto y asociá emprendedores activos. El enlace público se genera al crear.'}
                    </p>
                </div>
            }
        >
            <Head title={`${esEdicion ? 'Editar' : 'Nuevo'} punto — Wayna`} />

            <div className="relative py-8">
                <div className={adminBackdropTall} aria-hidden />

                <div className="relative mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <AdminBackLink href={route('admin.puntos.index')} />
                    <AdminFlashSuccess message={flash?.success} />

                    <form onSubmit={enviar} className="space-y-6">
                        <section className={adminSectionCard}>
                            <h3 className={adminLabelUpper}>Datos del punto</h3>

                            <div className={`mt-5 ${adminFormStack}`}>
                                <AdminFormField
                                    id="nombre"
                                    label="Nombre del punto"
                                    required
                                    error={errors.nombre}
                                >
                                    <input
                                        id="nombre"
                                        type="text"
                                        value={data.nombre}
                                        onChange={(e) => setData('nombre', e.target.value)}
                                        className={adminInputClass}
                                        maxLength={120}
                                        autoComplete="off"
                                    />
                                </AdminFormField>

                                <AdminFormField
                                    id="ubicacion"
                                    label="Ubicación"
                                    optional
                                    error={errors.ubicacion}
                                    hint="Ej.: Feria Wayna, mesa 3, lobby del hotel."
                                >
                                    <input
                                        id="ubicacion"
                                        type="text"
                                        value={data.ubicacion}
                                        onChange={(e) => setData('ubicacion', e.target.value)}
                                        className={adminInputClass}
                                        maxLength={150}
                                        autoComplete="off"
                                    />
                                </AdminFormField>

                                <AdminFormField
                                    id="descripcion"
                                    label="Descripción"
                                    optional
                                    error={errors.descripcion}
                                >
                                    <textarea
                                        id="descripcion"
                                        value={data.descripcion}
                                        onChange={(e) => setData('descripcion', e.target.value)}
                                        className={adminTextareaClass}
                                        rows={4}
                                    />
                                </AdminFormField>

                                <AdminFormField
                                    id="estado"
                                    label="Estado"
                                    required
                                    error={errors.estado}
                                >
                                    <select
                                        id="estado"
                                        value={data.estado}
                                        onChange={(e) => setData('estado', e.target.value)}
                                        className={adminSelectClass}
                                    >
                                        <option value="activo">Activo (visible en la página pública)</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </AdminFormField>

                                {esEdicion && punto?.slug && (
                                    <div className="rounded-xl border border-wayna-100 bg-wayna-50/60 px-4 py-3 text-sm text-stone-700">
                                        <p className="font-semibold text-wayna-900">Enlace público</p>
                                        <p className="mt-1 font-mono text-xs text-stone-600">
                                            {route('punto.show', punto.slug)}
                                        </p>
                                        <p className="mt-2 text-xs text-stone-500">
                                            El slug no se modifica al guardar.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </section>

                        <section className={adminSectionCard}>
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 className={adminLabelUpper}>Emprendedores asociados</h3>
                                    <p className="mt-2 text-sm text-stone-600">
                                        Solo se listan emprendedores con estado activo. Al guardar, la
                                        asociación se sincroniza con la base de datos.
                                    </p>
                                    <p className="mt-2 text-sm font-semibold text-wayna-800">
                                        Seleccionados: {seleccionadosCount}
                                    </p>
                                </div>
                                {emprendedores.length > 0 && (
                                    <div className="flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            onClick={seleccionarTodosVisibles}
                                            className="rounded-lg border border-wayna-200 bg-white px-3 py-1.5 text-xs font-bold text-wayna-800 hover:bg-wayna-50"
                                        >
                                            Marcar visibles
                                        </button>
                                        <button
                                            type="button"
                                            onClick={quitarTodosVisibles}
                                            className="rounded-lg border border-wayna-200 bg-white px-3 py-1.5 text-xs font-bold text-wayna-800 hover:bg-wayna-50"
                                        >
                                            Quitar visibles
                                        </button>
                                    </div>
                                )}
                            </div>

                            {errors.emprendedores && (
                                <p className="mt-3 text-sm font-medium text-red-600" role="alert">
                                    {errors.emprendedores}
                                </p>
                            )}

                            {emprendedores.length > 0 && (
                                <div className="mt-4">
                                    <label
                                        htmlFor="buscar_emprendedor_punto"
                                        className="sr-only"
                                    >
                                        Buscar emprendedor
                                    </label>
                                    <input
                                        id="buscar_emprendedor_punto"
                                        type="search"
                                        value={busqueda}
                                        onChange={(e) => setBusqueda(e.target.value)}
                                        placeholder="Buscar por nombre…"
                                        className={adminInputClass}
                                    />
                                </div>
                            )}

                            <div className="mt-4 max-h-80 overflow-y-auto rounded-xl border border-wayna-100 bg-white">
                                {emprendedores.length === 0 && (
                                    <p className="px-4 py-8 text-center text-sm text-stone-600">
                                        No hay emprendedores activos. Activá o creá emprendedores antes de
                                        asociarlos a este punto.
                                    </p>
                                )}

                                {emprendedores.length > 0 && emprendedoresFiltrados.length === 0 && (
                                    <p className="px-4 py-8 text-center text-sm text-stone-600">
                                        Ningún emprendedor coincide con la búsqueda.
                                    </p>
                                )}

                                <ul className="divide-y divide-wayna-50">
                                    {emprendedoresFiltrados.map((emp) => {
                                        const marcado = estaSeleccionado(emp.id);
                                        const inputId = `emp-punto-${emp.id}`;

                                        return (
                                            <li key={emp.id}>
                                                <label
                                                    htmlFor={inputId}
                                                    className={`flex cursor-pointer items-center gap-3 px-4 py-3 transition hover:bg-wayna-50/80 ${
                                                        marcado ? 'bg-wayna-50/60' : ''
                                                    }`}
                                                >
                                                    <input
                                                        id={inputId}
                                                        type="checkbox"
                                                        checked={marcado}
                                                        onChange={() => alternarEmprendedor(emp.id)}
                                                        className="h-4 w-4 rounded border-wayna-300 text-wayna-600 focus:ring-wayna-500"
                                                    />
                                                    <span className="text-sm font-medium text-stone-800">
                                                        {emp.nombre_completo}
                                                    </span>
                                                </label>
                                            </li>
                                        );
                                    })}
                                </ul>
                            </div>

                            {errors['emprendedores.0'] && (
                                <p className="mt-2 text-sm font-medium text-red-600" role="alert">
                                    Revisá la selección de emprendedores.
                                </p>
                            )}
                        </section>

                        <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <Link
                                href={route('admin.puntos.index')}
                                className={`text-center ${adminFormFooterSecondaryBtn}`}
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className={adminFormFooterPrimaryBtn}
                            >
                                {processing
                                    ? 'Guardando…'
                                    : esEdicion
                                      ? 'Guardar cambios'
                                      : 'Crear punto'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
