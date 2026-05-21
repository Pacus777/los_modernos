import AdminFlashSuccess from '@/Components/Admin/AdminFlashSuccess';
import AdminPaginator from '@/Components/Admin/AdminPaginator';
import {
    adminListCardOuter,
    adminPrimaryGradientBtn,
} from '@/Components/Admin/adminUi';
import EmprendedorLayout from '@/Layouts/EmprendedorLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

function formatearFecha(fecha) {
    if (!fecha) return 'Sin publicar';

    return new Date(fecha).toLocaleString('es-BO', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function badgeEstado(estado) {
    if (estado === 'publicado') {
        return 'bg-emerald-100 text-emerald-800 ring-emerald-200';
    }

    return 'bg-amber-100 text-amber-900 ring-amber-200';
}

function PostCard({ post, onEditar, onEliminar }) {
    return (
        <article className="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <span
                        className={`inline-flex rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wide ring-1 ${badgeEstado(post.estado)}`}
                    >
                        {post.estado === 'publicado' ? 'Publicado' : 'Borrador'}
                    </span>
                    <p className="mt-2 text-xs text-stone-500">
                        {post.estado === 'publicado'
                            ? `Publicado: ${formatearFecha(post.publicado_en)}`
                            : `Creado: ${formatearFecha(post.created_at)}`}
                    </p>
                </div>

                <div className="flex gap-2">
                    <button
                        type="button"
                        onClick={() => onEditar(post)}
                        className="rounded-xl border border-wayna-200 bg-white px-3 py-2 text-xs font-bold text-wayna-800 hover:bg-wayna-50"
                    >
                        Editar
                    </button>
                    <button
                        type="button"
                        onClick={() => onEliminar(post)}
                        className="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-800 hover:bg-red-100"
                    >
                        Eliminar
                    </button>
                </div>
            </div>

            {post.media_url && (
                <img
                    src={post.media_url}
                    alt=""
                    className="mt-4 max-h-72 w-full rounded-2xl object-cover"
                />
            )}

            {post.contenido && (
                <p className="mt-4 whitespace-pre-line text-base leading-relaxed text-stone-800">
                    {post.contenido}
                </p>
            )}

            {post.enlace_externo && (
                <a
                    href={post.enlace_externo}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="mt-4 block break-all rounded-xl border border-wayna-100 bg-wayna-50 px-3 py-2 text-sm font-semibold text-wayna-800 hover:bg-wayna-100"
                >
                    {post.enlace_externo}
                </a>
            )}

            <p className="mt-4 text-xs text-stone-500">
                {post.reacciones_count ?? 0} reacción(es)
            </p>
        </article>
    );
}

export default function PublicacionesIndex({ emprendedor, posts }) {
    const { flash } = usePage().props;
    const filas = posts?.data ?? [];
    const [editando, setEditando] = useState(null);

    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
        transform,
    } = useForm({
        contenido: '',
        imagen: null,
        enlace_externo: '',
        estado: 'publicado',
        quitar_imagen: false,
        _method: 'post',
    });

    const tituloFormulario = editando
        ? `Editar publicación #${editando.id}`
        : 'Nueva publicación';

    const imagenActual = useMemo(() => {
        if (!editando || data.imagen || data.quitar_imagen) {
            return null;
        }

        return editando.media_url ?? null;
    }, [editando, data.imagen, data.quitar_imagen]);

    const limpiarFormulario = () => {
        setEditando(null);
        reset();
        setData({
            contenido: '',
            imagen: null,
            enlace_externo: '',
            estado: 'publicado',
            quitar_imagen: false,
            _method: 'post',
        });
    };

    const seleccionarPost = (postActual) => {
        setEditando(postActual);
        setData({
            contenido: postActual.contenido ?? '',
            imagen: null,
            enlace_externo: postActual.enlace_externo ?? '',
            estado: postActual.estado ?? 'borrador',
            quitar_imagen: false,
            _method: 'put',
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const enviar = (e) => {
        e.preventDefault();

        transform((payload) => ({
            ...payload,
            _method: editando ? 'put' : 'post',
        }));

        post(
            editando
                ? route('emprendedor.publicaciones.update', editando.id)
                : route('emprendedor.publicaciones.store'),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => limpiarFormulario(),
            },
        );
    };

    const eliminar = (postActual) => {
        if (!confirm('¿Eliminar esta publicación? Esta acción no se puede deshacer.')) {
            return;
        }

        router.delete(route('emprendedor.publicaciones.destroy', postActual.id), {
            preserveScroll: true,
        });
    };

    return (
        <EmprendedorLayout
            header={
                <div>
                    <p className="text-xs font-bold uppercase tracking-wider text-wayna-600">
                        Red social WAYNA
                    </p>
                    <h1 className="text-xl font-black text-stone-900 sm:text-2xl">
                        Mis publicaciones
                    </h1>
                    <p className="mt-1 text-sm text-stone-600">
                        Comparte avances, historias, fotos y novedades de tu emprendimiento.
                    </p>
                </div>
            }
            contentClassName="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8"
        >
            <Head title="Mis publicaciones — WAYNA" />

            <AdminFlashSuccess message={flash?.success} />

            {flash?.error && (
                <p className="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                    {flash.error}
                </p>
            )}

            <section className={adminListCardOuter}>
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 className="text-lg font-black text-stone-900">
                            {tituloFormulario}
                        </h2>
                        <p className="mt-1 text-sm text-stone-600">
                            Puedes publicar texto, subir una imagen o agregar un enlace externo.
                        </p>
                    </div>

                    {editando && (
                        <button
                            type="button"
                            onClick={limpiarFormulario}
                            className="rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm font-bold text-stone-700 hover:bg-stone-50"
                        >
                            Cancelar edición
                        </button>
                    )}
                </div>

                <form onSubmit={enviar} className="mt-5 space-y-5">
                    <div>
                        <label
                            htmlFor="contenido"
                            className="block text-sm font-bold text-stone-800"
                        >
                            Editor de contenido
                        </label>
                        <textarea
                            id="contenido"
                            value={data.contenido}
                            onChange={(e) => setData('contenido', e.target.value)}
                            disabled={processing}
                            rows={7}
                            maxLength={3000}
                            className="input-wayna mt-2 w-full resize-y"
                            placeholder="Escribe una historia, avance, logro o novedad de tu emprendimiento..."
                        />
                        <div className="mt-1 flex justify-between gap-2 text-xs text-stone-500">
                            <span>
                                Usa saltos de línea para separar párrafos. Máximo 3000 caracteres.
                            </span>
                            <span>{data.contenido?.length ?? 0}/3000</span>
                        </div>
                        {errors.contenido && (
                            <p className="mt-2 text-sm text-red-600">{errors.contenido}</p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="imagen"
                            className="block text-sm font-bold text-stone-800"
                        >
                            Imagen
                        </label>

                        {imagenActual && (
                            <div className="mt-2 rounded-2xl border border-stone-200 bg-stone-50 p-3">
                                <img
                                    src={imagenActual}
                                    alt=""
                                    className="max-h-56 w-full rounded-xl object-cover"
                                />
                                <label className="mt-3 flex items-center gap-2 text-sm font-semibold text-stone-700">
                                    <input
                                        type="checkbox"
                                        checked={data.quitar_imagen}
                                        onChange={(e) =>
                                            setData('quitar_imagen', e.target.checked)
                                        }
                                    />
                                    Quitar imagen actual
                                </label>
                            </div>
                        )}

                        <input
                            id="imagen"
                            type="file"
                            accept="image/*"
                            onChange={(e) =>
                                setData('imagen', e.target.files?.[0] ?? null)
                            }
                            disabled={processing}
                            className="mt-2 block w-full rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm text-stone-700 file:mr-3 file:rounded-lg file:border-0 file:bg-wayna-100 file:px-3 file:py-2 file:text-sm file:font-bold file:text-wayna-800 hover:file:bg-wayna-200"
                        />

                        <p className="mt-1 text-xs text-stone-500">
                            Formatos de imagen comunes. Tamaño máximo: 4 MB.
                        </p>

                        {errors.imagen && (
                            <p className="mt-2 text-sm text-red-600">{errors.imagen}</p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="enlace_externo"
                            className="block text-sm font-bold text-stone-800"
                        >
                            Enlace externo
                            <span className="ml-1 font-normal text-stone-400">
                                opcional
                            </span>
                        </label>
                        <input
                            id="enlace_externo"
                            type="url"
                            value={data.enlace_externo}
                            onChange={(e) =>
                                setData('enlace_externo', e.target.value)
                            }
                            disabled={processing}
                            className="input-wayna mt-2 w-full"
                            placeholder="https://youtube.com/... o https://tiktok.com/..."
                        />
                        {errors.enlace_externo && (
                            <p className="mt-2 text-sm text-red-600">
                                {errors.enlace_externo}
                            </p>
                        )}
                    </div>

                    <div>
                        <p className="block text-sm font-bold text-stone-800">
                            Estado
                        </p>
                        <div className="mt-2 grid gap-2 sm:grid-cols-2">
                            <label className="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 bg-white px-4 py-3">
                                <input
                                    type="radio"
                                    name="estado"
                                    value="publicado"
                                    checked={data.estado === 'publicado'}
                                    onChange={(e) => setData('estado', e.target.value)}
                                />
                                <span>
                                    <span className="block text-sm font-bold text-stone-900">
                                        Publicar ahora
                                    </span>
                                    <span className="text-xs text-stone-500">
                                        Visible para turistas en el feed.
                                    </span>
                                </span>
                            </label>

                            <label className="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 bg-white px-4 py-3">
                                <input
                                    type="radio"
                                    name="estado"
                                    value="borrador"
                                    checked={data.estado === 'borrador'}
                                    onChange={(e) => setData('estado', e.target.value)}
                                />
                                <span>
                                    <span className="block text-sm font-bold text-stone-900">
                                        Guardar borrador
                                    </span>
                                    <span className="text-xs text-stone-500">
                                        No se muestra públicamente.
                                    </span>
                                </span>
                            </label>
                        </div>
                        {errors.estado && (
                            <p className="mt-2 text-sm text-red-600">{errors.estado}</p>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className={`${adminPrimaryGradientBtn} w-full justify-center sm:w-auto`}
                    >
                        {processing
                            ? 'Guardando…'
                            : editando
                              ? 'Actualizar publicación'
                              : 'Guardar publicación'}
                    </button>
                </form>
            </section>

            <section className="mt-8">
                <div className="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 className="text-lg font-black text-stone-900">
                            Publicaciones creadas
                        </h2>
                        <p className="text-sm text-stone-600">
                            Historial de posts de {emprendedor?.nombre_completo}.
                        </p>
                    </div>
                </div>

                {filas.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-wayna-200 bg-wayna-50/50 px-5 py-8 text-center">
                        <p className="text-sm font-semibold text-wayna-900">
                            Todavía no tienes publicaciones.
                        </p>
                        <p className="mt-2 text-sm text-stone-600">
                            Crea tu primer post para mostrar novedades a los turistas.
                        </p>
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {filas.map((postActual) => (
                            <PostCard
                                key={postActual.id}
                                post={postActual}
                                onEditar={seleccionarPost}
                                onEliminar={eliminar}
                            />
                        ))}
                    </div>
                )}

                {posts?.links?.length > 3 && (
                    <div className="mt-6">
                        <AdminPaginator links={posts.links} />
                    </div>
                )}
            </section>
        </EmprendedorLayout>
    );
}
