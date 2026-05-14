import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/react';
import { initI18n } from './i18n';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

let i18nRouterHookRegistered = false;

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        const locale = props.initialPage?.props?.locale ?? 'es';

        initI18n(locale);

        if (!i18nRouterHookRegistered) {
            i18nRouterHookRegistered = true;
            router.on('success', (event) => {
                const next = event.detail.page?.props?.locale;
                if (next === 'es' || next === 'en') {
                    initI18n(next);
                }
            });
        }

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
