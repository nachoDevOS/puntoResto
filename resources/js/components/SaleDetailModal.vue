<script setup>
const props = defineProps({
    sale: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const paymentLabels = { efectivo: '💵 Efectivo', qr: '📱 QR/Transf.', mixto: '💵+📱 Mixto' };

const formatDate = (value) =>
    new Date(value).toLocaleDateString('es-BO', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

const formatTime = (value) =>
    new Date(value).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });

const money = (value) => `Bs. ${Number(value).toFixed(2)}`;

const change = (sale) => Math.max(0, Number(sale.cash_amount) + Number(sale.qr_amount) - Number(sale.total));
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-200"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sale"
                class="fixed inset-0 z-[105] flex items-center justify-center bg-black/40 p-4"
                @click.self="emit('close')"
            >
                <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl">
                    <!-- Header -->
                    <div class="flex items-center justify-between bg-slate-800 px-5 py-4 text-white">
                        <h2 class="text-lg font-bold">🧾 Venta #{{ sale.ticket_number ?? sale.id }}</h2>
                        <button
                            class="rounded-lg p-1.5 text-slate-300 transition hover:bg-slate-700 hover:text-white"
                            title="Cerrar"
                            @click="emit('close')"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="space-y-5 p-5">
                        <!-- Fecha, tipo, vendedor -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs font-semibold text-slate-400 uppercase">📅 Fecha</p>
                                <p class="mt-0.5 text-sm font-medium text-slate-800 capitalize">{{ formatDate(sale.created_at) }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs font-semibold text-slate-400 uppercase">🕐 Hora</p>
                                <p class="mt-0.5 text-sm font-medium text-slate-800">{{ formatTime(sale.created_at) }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs font-semibold text-slate-400 uppercase">Tipo de venta</p>
                                <p class="mt-0.5 text-sm font-medium text-slate-800">
                                    <template v-if="sale.type === 'mesa'">
                                        🍴 Para Mesa{{ sale.table_number ? ` — N° ${sale.table_number}` : '' }}
                                    </template>
                                    <template v-else>🛍️ Para Llevar</template>
                                </p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs font-semibold text-slate-400 uppercase">👤 Vendedor</p>
                                <p class="mt-0.5 text-sm font-medium text-slate-800">{{ sale.user?.name ?? '—' }}</p>
                            </div>
                        </div>

                        <p
                            v-if="sale.deleted_at"
                            class="rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600"
                        >
                            🗑️ Venta eliminada el {{ formatDate(sale.deleted_at) }} a las {{ formatTime(sale.deleted_at) }}
                        </p>

                        <!-- Detalle de productos -->
                        <div>
                            <p class="mb-2 text-xs font-semibold text-slate-400 uppercase">Detalle de venta</p>
                            <div class="overflow-hidden rounded-xl border border-slate-100">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 text-xs text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Producto</th>
                                            <th class="px-3 py-2 text-right">Precio</th>
                                            <th class="px-3 py-2 text-right">Cant.</th>
                                            <th class="px-3 py-2 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr v-for="detail in sale.details" :key="detail.id">
                                            <td class="px-3 py-2 text-slate-700">{{ detail.product_name }}</td>
                                            <td class="px-3 py-2 text-right text-slate-500">{{ money(detail.price) }}</td>
                                            <td class="px-3 py-2 text-right text-slate-500">{{ detail.quantity }}</td>
                                            <td class="px-3 py-2 text-right font-medium text-slate-700">{{ money(detail.subtotal) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Detalle de pago -->
                        <div>
                            <p class="mb-2 text-xs font-semibold text-slate-400 uppercase">Detalle de pago</p>
                            <div class="space-y-1.5 rounded-xl border border-slate-100 p-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Método</span>
                                    <span class="font-medium text-slate-800">{{ paymentLabels[sale.payment_method] }}</span>
                                </div>
                                <div v-if="Number(sale.cash_amount) > 0" class="flex justify-between">
                                    <span class="text-slate-500">Efectivo recibido</span>
                                    <span class="font-medium text-slate-800">{{ money(sale.cash_amount) }}</span>
                                </div>
                                <div v-if="Number(sale.qr_amount) > 0" class="flex justify-between">
                                    <span class="text-slate-500">QR / Transferencia</span>
                                    <span class="font-medium text-slate-800">{{ money(sale.qr_amount) }}</span>
                                </div>
                                <div v-if="change(sale) > 0" class="flex justify-between text-amber-600">
                                    <span>Cambio entregado</span>
                                    <span class="font-medium">{{ money(change(sale)) }}</span>
                                </div>
                                <div class="flex justify-between border-t border-slate-100 pt-2 text-base">
                                    <span class="font-semibold text-slate-700">Total</span>
                                    <span class="font-bold text-emerald-600">{{ money(sale.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
