<script setup>
import { nextTick, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';
import SaleDetailModal from '../../components/SaleDetailModal.vue';

const props = defineProps({
    sales: Array,
    filters: Object,
    summary: Object,
});

const date = ref(props.filters.from);
const selectedSale = ref(null);

const applyFilters = () => {
    router.get('/reports/sales', { from: date.value, to: date.value }, { preserveState: true });
};

const setToday = () => {
    date.value = new Date().toISOString().slice(0, 10);
    applyFilters();
};

const formatDateTime = (value) =>
    new Date(value).toLocaleString('es-BO', { dateStyle: 'short', timeStyle: 'short' });

const paymentLabels = { efectivo: 'Efectivo', qr: 'QR/Transf.', mixto: 'Mixto' };

const printData = ref(null);
const printing = ref(false);

const printReport = async () => {
    printing.value = true;

    try {
        const response = await fetch(`/reports/sales/print?date=${date.value}`, {
            headers: { Accept: 'application/json' },
        });

        printData.value = await response.json();

        await nextTick();
        window.print();
    } finally {
        printing.value = false;
    }
};
</script>

<template>
    <Head title="Reporte de ventas" />

    <AppLayout>
        <div class="mx-auto max-w-5xl p-6 print:hidden">
            <h1 class="mb-6 text-2xl font-bold text-slate-800">Reporte de ventas</h1>

            <!-- Filtros -->
            <div class="mb-6 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500" for="date">Fecha</label>
                    <input
                        id="date"
                        v-model="date"
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
                <button
                    :disabled="printing"
                    class="ml-auto rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 disabled:opacity-50"
                    @click="printReport"
                >
                    🖨️ Imprimir
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
                        <tr v-for="sale in sales" :key="sale.id" class="hover:bg-slate-50">
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
                        <tr v-if="!sales.length">
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">Sin ventas en este día.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Versión imprimible -->
        <div v-if="printData" class="hidden p-8 text-black print:block">
            <h1 class="text-xl font-bold">Reporte de ventas</h1>
            <p class="mt-1 text-sm">
                Día: {{ printData.date }} · Impreso el {{ formatDateTime(new Date()) }}
            </p>

            <table class="mt-4 w-full border-collapse text-sm">
                <tbody>
                    <tr>
                        <td class="border border-black px-3 py-1.5 font-semibold">Ventas</td>
                        <td class="border border-black px-3 py-1.5 text-right">{{ printData.summary.count }}</td>
                        <td class="border border-black px-3 py-1.5 font-semibold">Ingreso Efectivo</td>
                        <td class="border border-black px-3 py-1.5 text-right">Bs. {{ Number(printData.summary.cash).toFixed(2) }}</td>
                        <td class="border border-black px-3 py-1.5 font-semibold">Ingreso QR</td>
                        <td class="border border-black px-3 py-1.5 text-right">Bs. {{ Number(printData.summary.qr).toFixed(2) }}</td>
                        <td class="border border-black px-3 py-1.5 font-semibold">Total ventas</td>
                        <td class="border border-black px-3 py-1.5 text-right">Bs. {{ Number(printData.summary.total).toFixed(2) }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="mt-4 w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th class="border border-black px-3 py-1.5 text-left">#</th>
                        <th class="border border-black px-3 py-1.5 text-left">Fecha</th>
                        <th class="border border-black px-3 py-1.5 text-left">Tipo</th>
                        <th class="border border-black px-3 py-1.5 text-left">Pago</th>
                        <th class="border border-black px-3 py-1.5 text-left">Vendedor</th>
                        <th class="border border-black px-3 py-1.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in printData.sales" :key="`print-${sale.id}`">
                        <td class="border border-black px-3 py-1.5">{{ sale.ticket_number ?? sale.id }}</td>
                        <td class="border border-black px-3 py-1.5">{{ formatDateTime(sale.created_at) }}</td>
                        <td class="border border-black px-3 py-1.5">
                            {{ sale.type === 'mesa' ? `Mesa${sale.table_number ? ` ${sale.table_number}` : ''}` : 'Llevar' }}
                        </td>
                        <td class="border border-black px-3 py-1.5">{{ paymentLabels[sale.payment_method] }}</td>
                        <td class="border border-black px-3 py-1.5">{{ sale.user?.name }}</td>
                        <td class="border border-black px-3 py-1.5 text-right">Bs. {{ Number(sale.total).toFixed(2) }}</td>
                    </tr>
                    <tr v-if="!printData.sales.length">
                        <td colspan="6" class="border border-black px-3 py-4 text-center">Sin ventas en este día.</td>
                    </tr>
                </tbody>
            </table>

            <p class="mt-6 text-xs">PuntoResto · Desarrollado por Solucion Digital</p>
        </div>

        <SaleDetailModal :sale="selectedSale" @close="selectedSale = null" />
    </AppLayout>
</template>
