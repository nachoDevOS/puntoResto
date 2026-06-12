<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ToastContainer from '../components/ToastContainer.vue';
import { useToast } from '../composables/useToast';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const toast = useToast();

const toastPosition = computed(() => (page.component === 'Pos/Index' ? 'top-left' : 'bottom-center'));

let removeSuccessListener;
let removeErrorListener;

onMounted(() => {
    removeSuccessListener = router.on('success', (event) => {
        const flash = event.detail.page.props.flash ?? {};

        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
    });

    removeErrorListener = router.on('error', () => {
        toast.warning('Revisá los campos marcados.');
    });

    const flash = page.props.flash ?? {};

    if (flash.success) toast.success(flash.success);
    if (flash.error) toast.error(flash.error);
});

onUnmounted(() => {
    removeSuccessListener?.();
    removeErrorListener?.();
});

const links = [
    { href: '/pos', label: 'Vender', icon: '🛒' },
    { href: '/sales', label: 'Ventas', icon: '🧾' },
    { href: '/products', label: 'Productos', icon: '🍗' },
    { href: '/categories', label: 'Categorías', icon: '🏷️' },
    { href: '/reports/sales', label: 'Reportes', icon: '📊' },
];

const isActive = (href) => page.url.startsWith(href);

const logout = () => router.post('/logout');
</script>

<template>
    <div class="flex min-h-screen bg-gray-100">
        <aside class="flex w-56 shrink-0 flex-col bg-slate-800 text-slate-200">
            <div class="flex items-center gap-2 px-4 py-5 text-lg font-bold text-white">
                🍽️ PuntoResto
            </div>
            <nav class="flex-1 space-y-1 px-2">
                <Link
                    v-for="link in links"
                    :key="link.href"
                    :href="link.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition hover:bg-slate-700"
                    :class="{ 'bg-slate-700 text-white': isActive(link.href) }"
                >
                    <span>{{ link.icon }}</span>
                    {{ link.label }}
                </Link>
            </nav>
            <div class="border-t border-slate-700 p-3">
                <div class="mb-3 rounded-lg bg-slate-900/60 px-3 py-2.5">
                    <a
                        href="https://www.soluciondigital.dev/"
                        target="_blank"
                        rel="noopener"
                        class="block text-xs font-bold text-emerald-400 transition hover:text-emerald-300"
                    >
                        ⚡ Solución Digital
                    </a>
                    <div class="mt-1.5 space-y-0.5">
                        <a
                            href="https://wa.me/59167285914"
                            target="_blank"
                            rel="noopener"
                            class="block text-[11px] text-slate-400 transition hover:text-emerald-400"
                        >
                            📱 67285914
                        </a>
                        <a
                            href="https://wa.me/59177149775"
                            target="_blank"
                            rel="noopener"
                            class="block text-[11px] text-slate-400 transition hover:text-emerald-400"
                        >
                            📱 77149775
                        </a>
                    </div>
                </div>
                <p class="truncate px-2 pb-2 text-xs text-slate-400">{{ user?.name }}</p>
                <button
                    class="w-full rounded-lg bg-slate-700 px-3 py-2 text-sm font-medium transition hover:bg-slate-600"
                    @click="logout"
                >
                    🚪 Cerrar sesión
                </button>
            </div>
        </aside>

        <main class="flex-1 overflow-x-hidden">
            <slot />
        </main>

        <ToastContainer :position="toastPosition" />
    </div>
</template>
