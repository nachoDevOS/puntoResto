import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

const blockedDevToolsKeys = new Set(['F12']);
const blockedDevToolsShortcuts = new Set(['i', 'j', 'c', 'u']);
const shouldBlockDevTools = import.meta.env.VITE_BLOCK_DEVTOOLS === 'true';

if (shouldBlockDevTools) {
    document.addEventListener('contextmenu', (event) => {
        event.preventDefault();
    });

    document.addEventListener('keydown', (event) => {
        const key = event.key.toLowerCase();
        const isDevToolsShortcut = event.ctrlKey && event.shiftKey && blockedDevToolsShortcuts.has(key);
        const isViewSourceShortcut = event.ctrlKey && key === 'u';

        if (blockedDevToolsKeys.has(event.key) || isDevToolsShortcut || isViewSourceShortcut) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
}

createInertiaApp({
    title: (title) => (title ? `${title} - PuntoResto` : 'PuntoResto'),
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.vue', { eager: true });

        return pages[`./pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
