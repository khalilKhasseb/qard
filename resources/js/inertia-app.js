import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import naive from 'naive-ui';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

// Sync HTML dir and lang attributes with Inertia page props
const syncDocumentDirection = (page) => {
    const direction = page.props?.currentDirection || 'ltr';
    const language = page.props?.currentLanguage || 'en';

    document.documentElement.setAttribute('dir', direction);
    document.documentElement.setAttribute('lang', language);
};

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // Sync direction on initial page load
        syncDocumentDirection(props.initialPage);

        // Listen for Inertia navigation events
        router.on('navigate', (event) => {
            syncDocumentDirection(event.detail.page);
        });

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(naive)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
