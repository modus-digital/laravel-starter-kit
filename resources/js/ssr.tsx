import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} - ${appName}` : appName),
        resolve: (name) => {
            const pages = import.meta.glob(['./pages/core/**/*.tsx', './pages/modules/**/*.tsx']);

            // Try core first
            const coreResult = resolvePageComponent(`./pages/core/${name}.tsx`, pages);
            if (coreResult) {
                return coreResult;
            }

            // Then try modules
            const moduleResult = resolvePageComponent(`./pages/modules/${name}.tsx`, pages);
            if (moduleResult) {
                return moduleResult;
            }

            // Fallback: search for the page in the glob results
            const pagePath = Object.keys(pages).find((path) => {
                const normalizedPath = path.replace(/^\.\/pages\/(core|modules)\//, '').replace(/\.tsx$/, '');
                return normalizedPath === name;
            });

            if (pagePath && pages[pagePath]) {
                return pages[pagePath]();
            }

            return Promise.reject(new Error(`Page not found: ${name}`));
        },
        setup: ({ App, props }) => {
            return <App {...props} />;
        },
    }),
);
