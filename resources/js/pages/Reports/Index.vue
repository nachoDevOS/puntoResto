<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';
import SaleDetailModal from '../../components/SaleDetailModal.vue';

const props = defineProps({
    sales: Object,
    filters: Object,
    summary: Object,
});

const from = ref(props.filters.from);
const to = ref(props.filters.to);
const selectedSale = ref(null);

const applyFilters = () => {
    router.get('/reports/sales', { from: from.value, to: to.value }, { preserveState: true });
};

const setToday = () => {
    const today = new Date().toISOString().slice(0, 10);
    from.value = today;
    to.value = today;
    applyFilters();
};

const formatDateTime = (value) =>
    new Date(value).toLocaleString('es-BO', { dateStyle: 'short', timeStyle: 'short' });

const paymentLabels = { efectivo: 'Efectivo', qr: 'QR/Transf.', mixto: 'Mixto' };
</script>

<template>
    <Head title="Reporte de ventas" />

    <AppLayout>
        <div class="mx-auto max-w-5xl p-6">
            <h1 class="mb-6 text-2xl font-bold text-slate-800">Reporte de ventas</h1>

            <!-- Filtros -->
            <div class="mb-6 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500" for="from">Desde</label>
                    <input
                        id="from"
                        v-model="from"
                        type="date"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500" for="to">Hasta</label>
                    <input
                        id="to"
                        v-model="to"
                        type="date"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                    />
                </div>
                <button
                    class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-600"
                    @click="applyFilters"
                >
                    🔍 Filtrar
                </button>
                <button
                    class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-200"
                    @click="setToday"
                >
                    📅 Hoy
                </button>
            </div>

            <!-- Resumen -->
            <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-xl bg-white p-4 shadow">
                    <p class="text-xs font-medium text-slate-500 uppercase">Ventas</p>
                    <p class="text-2xl font-bold text-slate-800">{{ summary.count }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow">
                    <p class="text-xs font-medium text-slate-500 uppercase">Total</p>
                    <p class="text-2xl font-bold text-emerald-600">Bs. {{ Number(summary.total).toFixed(2) }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow">
                    <p class="text-xs font-medium text-slate-500 uppercase">Efectivo</p>
                    <p class="text-2xl font-bold text-slate-800">Bs. {{ Number(summary.cash).toFixed(2) }}</p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow">
                    <p class="text-xs font-medium text-slate-500 uppercase">QR/Transf.</p>
                    <p class="text-2xl font-bold text-slate-800">Bs. {{ Number(summary.qr).toFixed(2) }}</p>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-hidden rounded-xl bg-white shadow">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Pago</th>
                            <th class="px-4 py-3">Vendedor</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ sale.ticket_number ?? sale.id }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ formatDateTime(sale.created_at) }}</td>
                            <td class="px-4 py-3">
                                <span v-if="sale.type === 'mesa'" class="text-slate-700">
                                    🍴 Mesa{{ sale.table_number ? ` ${sale.table_number}` : '' }}
                                </span>
                                <span v-else class="text-slate-700">🛍️ Llevar</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ paymentLabels[sale.payment_method] }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ sale.user?.name }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-800">Bs. {{ Number(sale.total).toFixed(2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    class="rounded-lg bg-blue-50 p-2 text-blue-600 transition hover:bg-blue-100"
                                    title="Ver detalle"
                                    @click="selectedSale = sale"
                                >
                                    👁️
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!sales.data.length">
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">Sin ventas en este rango.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="sales.last_page > 1" class="mt-4 flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Mostrando {{ sales.from }}–{{ sales.to }} de {{ sales.total }} ventas
                </p>
                <nav class="flex gap-1">
                    <template v-for="(link, index) in sales.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                            :class="link.active ? 'bg-emerald-500 text-white' : 'bg-white text-slate-600 shadow-sm hover:bg-slate-50'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="rounded-lg px-3 py-1.5 text-sm text-slate-300"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>

        <SaleDetailModal :sale="selectedSale" @close="selectedSale = null" />
    </AppLayout>
</template>
