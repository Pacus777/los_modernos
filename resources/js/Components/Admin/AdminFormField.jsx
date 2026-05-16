import { adminLabelRequiredMark } from '@/Components/Admin/adminUi';

/**
 * Campo de formulario admin: etiqueta arriba, control abajo, error debajo.
 */
export default function AdminFormField({
    id,
    label,
    required = false,
    optional = false,
    hint = null,
    error = null,
    children,
}) {
    return (
        <div className="flex w-full min-w-0 flex-col gap-1.5">
            <label htmlFor={id} className="block text-sm font-semibold text-stone-800">
                {label}
                {required ? (
                    <span className={adminLabelRequiredMark} aria-hidden="true">
                        *
                    </span>
                ) : null}
                {optional && !required ? (
                    <span className="ml-1.5 text-xs font-normal text-stone-500">
                        (opcional)
                    </span>
                ) : null}
            </label>
            {hint ? (
                <p className="text-xs leading-relaxed text-stone-500">{hint}</p>
            ) : null}
            <div className="w-full min-w-0">{children}</div>
            {error ? (
                <p className="text-sm font-medium text-red-600" role="alert">
                    {error}
                </p>
            ) : null}
        </div>
    );
}
