export default function SecondaryButton({
    type = 'button',
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            type={type}
            className={`btn-wayna-secondary !rounded-xl !text-xs !uppercase !tracking-widest ${
                disabled ? 'opacity-25' : ''
            } ${className}`}
            disabled={disabled}
        >
            {children}
        </button>
    );
}
