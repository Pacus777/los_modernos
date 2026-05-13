export default function Checkbox({ className = '', ...props }) {
    return (
        <input
            {...props}
            type="checkbox"
            className={
                'rounded border-gray-300 text-wayna-600 shadow-sm focus:ring-wayna-500 ' +
                className
            }
        />
    );
}
