import TextInput from '@/Components/TextInput';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

function EyeIcon({ open }) {
    if (open) {
        return (
            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden>
                <path
                    d="M3 3l18 18M10.58 10.58A2 2 0 0012 15a2 2 0 001.42-.58M9.88 5.09A10.94 10.94 0 0112 5c5.5 0 9.5 4.5 10 7-.37 1.24-1.12 2.55-2.18 3.68M6.11 6.11C4.18 7.56 2.82 9.33 2 11c.5 2.5 4.5 7 10 7 1.78 0 3.44-.47 4.89-1.27"
                    stroke="currentColor"
                    strokeWidth="1.75"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                />
            </svg>
        );
    }

    return (
        <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden>
            <path
                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"
                stroke="currentColor"
                strokeWidth="1.75"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            <circle cx="12" cy="12" r="3" stroke="currentColor" strokeWidth="1.75" />
        </svg>
    );
}

export default function PasswordInput({ className = '', inputClassName = '', ...props }) {
    const { t } = useTranslation();
    const [visible, setVisible] = useState(false);

    return (
        <div className={`relative ${className}`.trim()}>
            <TextInput
                {...props}
                type={visible ? 'text' : 'password'}
                className={`${inputClassName} pe-12`.trim()}
            />
            <button
                type="button"
                onClick={() => setVisible((v) => !v)}
                className="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-stone-500 transition hover:bg-wayna-50 hover:text-wayna-700 focus:outline-none focus:ring-2 focus:ring-wayna-500/40"
                aria-label={visible ? t('auth.hidePassword') : t('auth.showPassword')}
                aria-pressed={visible}
            >
                <EyeIcon open={visible} />
            </button>
        </div>
    );
}
