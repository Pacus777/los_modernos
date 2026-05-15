/**
 * Zona de arrastre / clic para subir archivos (admin WAYNA).
 */
export default function AdminMediaDropzone({
    id,
    accept,
    multiple = false,
    disabled = false,
    onFiles,
    title,
    hint,
    badge,
    icon = 'image',
    compact = false,
}) {
    const iconSvg =
        icon === 'video' ? (
            <svg className="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden>
                <path strokeLinecap="round" strokeLinejoin="round" d="m15 10 4.553-2.276A1 1 0 0 1 21 8.618v6.764a1 1 0 0 1-1.447.894L15 14M5 18h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2Z" />
            </svg>
        ) : (
            <svg className="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden>
                <path strokeLinecap="round" strokeLinejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
            </svg>
        );

    return (
        <label
            htmlFor={id}
            className={`admin-media-dropzone group relative flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed text-center transition ${
                disabled
                    ? 'cursor-not-allowed border-stone-200 bg-stone-50/80 opacity-60'
                    : 'border-wayna-200/90 bg-gradient-to-br from-wayna-50/80 via-white to-surface-muted/60 hover:border-wayna-400 hover:shadow-md hover:shadow-wayna-500/10'
            } ${compact ? 'px-4 py-5' : 'px-6 py-8'}`}
        >
            <input
                id={id}
                type="file"
                accept={accept}
                multiple={multiple}
                disabled={disabled}
                className="sr-only"
                onChange={(e) => {
                    onFiles?.(e.target.files);
                    e.target.value = '';
                }}
            />

            <span
                className={`mb-3 flex items-center justify-center rounded-2xl bg-wayna-100/90 text-wayna-600 ring-1 ring-wayna-200/80 transition group-hover:scale-105 group-hover:bg-wayna-500 group-hover:text-white group-hover:ring-wayna-400 ${
                    compact ? 'h-11 w-11' : 'h-14 w-14'
                }`}
            >
                {iconSvg}
            </span>

            <span className="text-sm font-bold text-wayna-900">{title}</span>
            {hint && (
                <span className="mt-1 max-w-xs text-xs leading-relaxed text-stone-500">
                    {hint}
                </span>
            )}
            {badge && (
                <span className="mt-3 inline-flex rounded-full bg-wayna-500/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-wayna-800 ring-1 ring-wayna-200/80">
                    {badge}
                </span>
            )}
        </label>
    );
}
