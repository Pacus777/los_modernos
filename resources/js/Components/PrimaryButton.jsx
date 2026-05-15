export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            className={`btn-wayna-primary !rounded-xl !text-xs !uppercase !tracking-widest ${
                disabled ? 'opacity-25' : ''
            } ${className}`}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
