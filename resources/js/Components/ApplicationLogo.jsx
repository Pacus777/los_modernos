import { Link } from '@inertiajs/react';
import { useState } from 'react';

export const WAYNA_LOGO_SRC = '/images/logo-naranja.png';
export const WAYNA_LOGO_WHITE_SRC = '/images/logo-blanco.png';

const SIZE_CLASS = {
    xs: 'h-7 w-auto max-w-full',
    sm: 'h-9 w-auto max-w-full',
    md: 'h-11 w-auto max-w-full',
    lg: 'h-14 w-auto max-w-full',
    xl: 'h-[4.5rem] w-auto max-w-full',
    hero: 'h-20 w-auto max-w-full sm:h-24',
    nav: 'h-11 w-auto max-w-full sm:h-12',
    'nav-lg': 'h-12 w-auto max-w-full sm:h-[3.75rem]',
};

/**
 * @param {'xs'|'sm'|'md'|'lg'|'xl'|'hero'|'nav'|'nav-lg'} size
 * @param {'light'|'on-brand'|'plain'} tone
 */
export default function ApplicationLogo({
    className = '',
    size = 'md',
    tone = 'light',
    ...props
}) {
    const [useFallback, setUseFallback] = useState(false);
    const sizeClass = SIZE_CLASS[size] ?? SIZE_CLASS.md;
    const src = tone === 'on-brand' ? WAYNA_LOGO_WHITE_SRC : WAYNA_LOGO_SRC;

    const image = !useFallback ? (
        <img
            src={src}
            alt="Wayna"
            className={`block object-contain ${sizeClass} ${className}`}
            onError={() => setUseFallback(true)}
            {...props}
        />
    ) : (
        <span
            className={`inline-flex items-center justify-center rounded-xl bg-wayna-500 px-3 py-1 text-sm font-bold text-white ${sizeClass}`}
        >
            wayna
        </span>
    );

    if (tone === 'on-brand') {
        return (
            <span className="logo-wayna-on-brand inline-flex shrink-0 items-center justify-center">
                {image}
            </span>
        );
    }

    if (tone === 'light') {
        return (
            <span className="inline-flex shrink-0 items-center justify-center rounded-2xl bg-surface-card p-2 shadow-md ring-1 ring-wayna-200/60">
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
            className={`inline-flex shrink-0 items-center transition hover:opacity-90 active:scale-[0.98] ${className}`}
        >
            <ApplicationLogo size={size} tone={tone} />
        </Link>
    );
}
