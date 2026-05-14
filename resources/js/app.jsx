import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { initI18n } from './i18n';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        const locale = props.initialPage.props.locale || 'es';

        initI18n(locale);

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
