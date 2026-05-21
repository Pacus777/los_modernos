import PostReacciones from '@/Components/Turista/PostReacciones';
import { useTranslation } from 'react-i18next';

export default function PerfilFeedPosts({ posts = [] }) {
    const { t } = useTranslation();

    if (!posts.length) {
        return null;
    }

    return (
        <section className="mt-6">
            <header className="mb-3 px-1">
                <h2 className="text-lg font-black text-wayna-950">
                    {t('tourist.reactions.feedTitle')}
                </h2>
                <p className="mt-0.5 text-sm text-stone-600">
                    {t('tourist.reactions.feedSubtitle')}
                </p>
            </header>

            <ul className="space-y-4">
                {posts.map((post) => (
                    <li
                        key={post.id}
                        className="overflow-hidden rounded-2xl border border-stone-200/80 bg-white p-4 shadow-sm"
                    >
                        {post.contenido ? (
                            <p className="whitespace-pre-wrap text-sm leading-relaxed text-stone-800">
                                {post.contenido}
                            </p>
                        ) : null}

                        {post.media_url ? (
                            <img
                                src={post.media_url}
                                alt=""
                                className="mt-3 max-h-80 w-full rounded-xl object-cover"
                            />
                        ) : null}

                        {post.enlace_externo ? (
                            <div className="mt-3">
                                <a
                                    href={post.enlace_externo}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-1.5 text-xs font-semibold text-wayna-600 hover:text-wayna-800 hover:underline"
                                >
                                    🔗 {post.enlace_externo}
                                </a>
                            </div>
                        ) : null}

                        <PostReacciones
                            postId={post.id}
                            totalesIniciales={post.totales}
                            miReaccionInicial={post.mi_reaccion}
                        />
                    </li>
                ))}
            </ul>
        </section>
    );
}
