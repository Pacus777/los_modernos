import { Link } from '@inertiajs/react';
import { useState } from 'react';

export const WAYNA_LOGO_SRC = '/images/logo-naranja.png';

const SIZE_CLASS = {
    xs: 'h-7 w-auto',
    sm: 'h-9 w-auto',
    md: 'h-11 w-auto',
    lg: 'h-14 w-auto',
    xl: 'h-[4.5rem] w-auto',
    hero: 'h-24 w-auto sm:h-28',
    nav: 'h-12 w-auto sm:h-14',
    'nav-lg': 'h-14 w-auto sm:h-[4.25rem]',
};

/**
 * @param {'xs'|'sm'|'md'|'lg'|'xl'|'hero'|'nav'|'nav-lg'} size
 * @param {'light'|'dark'|'plain'|'on-brand'} tone
 */
export default function ApplicationLogo({
    className = '',
    size = 'md',
    tone = 'light',
    ...props
}) {
    const [useFallback, setUseFallback] = useState(false);
    const sizeClass = SIZE_CLASS[size] ?? SIZE_CLASS.md;

    const image = !useFallback ? (
        <img
            src={WAYNA_LOGO_SRC}
            alt="Wayna"
            className={`object-contain ${sizeClass} ${className}`}
            onError={() => setUseFallback(true)}
            {...props}
        />
    ) : (
        <span
            className={`inline-flex items-center justify-center rounded-xl bg-stone-950 px-3 py-1 text-sm font-bold text-white ${sizeClass} ${className}`}
        >
            wayna
        </span>
    );

    if (tone === 'light') {
        return (
            <span className="inline-flex rounded-2xl bg-stone-950 p-2 shadow-md shadow-stone-900/15 ring-1 ring-black/10">
                {image}
            </span>
        );
    }

    if (tone === 'dark') {
        return (
            <span className="inline-flex rounded-xl bg-stone-950/90 p-1.5 ring-1 ring-white/10">
                {image}
            </span>
        );
    }

    if (tone === 'on-brand') {
        return (
            <span className="inline-flex rounded-2xl bg-stone-950 p-2 shadow-lg shadow-black/25 ring-2 ring-white/20 sm:p-2.5">
                {image}
            </span>
        );
    }

    return image;
}

export function WaynaBrand({
    href = '/',
    size = 'md',
    tone = 'light',
    className = '',
    onClick,
}) {
    return (
        <Link
            href={href}
            onClick={onClick}
            className={`inline-flex shrink-0 transition hover:opacity-90 active:scale-[0.98] ${className}`}
        >
            <ApplicationLogo size={size} tone={tone} />
        </Link>
    );
}
