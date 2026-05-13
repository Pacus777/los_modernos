import { Link } from '@inertiajs/react';

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Link
            {...props}
            className={
                'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none ' +
                (active
                    ? 'border-wayna-500 text-wayna-800 focus:border-wayna-600'
                    : 'border-transparent text-gray-500 hover:border-wayna-200 hover:text-wayna-800 focus:border-wayna-200 focus:text-wayna-800') +
                className
            }
        >
            {children}
        </Link>
    );
}
