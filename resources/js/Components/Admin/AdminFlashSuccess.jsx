/**
 * Mensaje flash de éxito unificado (panel admin WAYNA).
 */
export default function AdminFlashSuccess({ message }) {
    if (!message) {
        return null;
    }

    return (
        <div
            role="status"
            className="mb-6 flex items-start gap-3 rounded-2xl border border-wayna-200 bg-gradient-to-r from-wayna-50 to-white px-4 py-3 text-sm text-wayna-950 shadow-sm shadow-wayna-900/5"
        >
            <span className="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wayna-500 text-xs font-bold text-white">
                ✓
            </span>
            <span className="pt-0.5 leading-relaxed">{message}</span>
        </div>
    );
}
