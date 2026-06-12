<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';
import SaleDetailModal from '../../components/SaleDetailModal.vue';

const props = defineProps({
    sales: Object,
    filters: Object,
});

const selectedSale = ref(null);
const status = ref(props.filters.status);

const changeStatus = () => {
    router.get('/sales', { status: status.value }, { preserveState: true });
};

const destroy = (sale) => {
    if (confirm(`¿Eliminar la venta #${sale.ticket_number ?? sale.id} de Bs. ${Number(sale.total).toFixed(2)}?`)) {
        useForm({}).delete(`/sales/${sale.id}`, { preserveScroll: true });
    }
};

const formatDateTime = (value) =>
    new Date(value).toLocaleString('es-BO', { dateStyle: 'short', timeStyle: 'short' });

const paymentLabels = { efectivo: 'Efectivo', qr: 'QR/Transf.', mixto: 'Mixto' };
</script>

<template>
    <Head title="Ventas" />

    <AppLayout>
        <div class="mx-auto max-w-5xl p-6">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-bold text-slate-800">Ventas</h1>

                <div class="flex items-center gap-3">
                    <select
                        v-model="status"
                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 focus:border-emerald-500 focus:outline-none"
                        @change="changeStatus"
                    >
                        <option value="activas">Ventas activas</option>
                        <option value="eliminadas">Ventas eliminadas</option>
                    </select>
                    <p class="text-sm text-slate-500">{{ sales.total }} registros</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Pago</th>
                            <th class="px-4 py-3">Vendedor</th>
                            <th v-if="status === 'eliminadas'" class="px-4 py-3">Eliminada</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="sale in sales.data"
                            :key="sale.id"
                            class="hover:bg-slate-50"
                            :class="{ 'opacity-70': sale.deleted_at }"
                        >
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
                            <td v-if="status === 'eliminadas'" class="px-4 py-3 text-red-500">
                                {{ sale.deleted_at ? formatDateTime(sale.deleted_at) : '' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-800">Bs. {{ Number(sale.total).toFixed(2) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button
                                    class="rounded-lg bg-blue-50 p-2 text-blue-600 transition hover:bg-blue-100"
                                    title="Ver detalle"
                                    @click="selectedSale = sale"
                                >
                                    👁️
                                </button>
                                <button
                                    v-if="!sale.deleted_at"
                                    class="ml-2 rounded-lg bg-red-50 p-2 text-red-600 transition hover:bg-red-100"
                                    title="Eliminar venta"
                                    @click="destroy(sale)"
                                >
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!sales.data.length">
                            <td :colspan="status === 'eliminadas' ? 8 : 7" class="px-4 py-8 text-center text-slate-400">
                                {{ status === 'eliminadas' ? 'Sin ventas eliminadas.' : 'Sin ventas registradas.' }}
                            </td>
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
