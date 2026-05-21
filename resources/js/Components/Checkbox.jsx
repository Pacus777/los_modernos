export default function Checkbox({ className = '', ...props }) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'h-4 w-4 rounded-lg border-wayna-300 text-wayna-600 shadow-sm focus:ring-2 focus:ring-wayna-500/35 ' +
                className
            }
        />
    );
}
