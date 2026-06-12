<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';
import { useTicketPrinter } from '../../composables/useTicketPrinter';

const props = defineProps({
    categories: Array,
    products: Array,
});

const { printTicket } = useTicketPrinter();

const selectedCategory = ref(null);
const search = ref('');
const cart = ref([]);

const filteredProducts = computed(() => {
    return props.products.filter((product) => {
        const matchesCategory = selectedCategory.value === null || product.category_id === selectedCategory.value;
        const matchesSearch = product.name.toLowerCase().includes(search.value.toLowerCase());

        return matchesCategory && matchesSearch;
    });
});

const cartCount = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));
const total = computed(() => cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0));

const totalHighlight = ref(false);
let totalHighlightTimer;

watch(total, (newTotal) => {
    if (form.payment_method === 'efectivo') {
        form.cash_amount = newTotal;
        form.qr_amount = 0;
    } else if (form.payment_method === 'qr') {
        form.qr_amount = newTotal;
        form.cash_amount = 0;
    }

    totalHighlight.value = true;
    clearTimeout(totalHighlightTimer);
    totalHighlightTimer = setTimeout(() => (totalHighlight.value = false), 600);
});

const addToCart = (product) => {
    const existing = cart.value.find((item) => item.product_id === product.id);

    if (existing) {
        existing.quantity++;
    } else {
        cart.value.push({
            product_id: product.id,
            name: product.name,
            price: Number(product.price),
            photo_path: product.photo_path,
            quantity: 1,
        });
    }
};

const decreaseQuantity = (item) => {
    if (item.quantity > 1) {
        item.quantity--;
    } else {
        cart.value = cart.value.filter((cartItem) => cartItem.product_id !== item.product_id);
    }
};

const form = useForm({
    type: 'mesa',
    table_number: null,
    payment_method: 'efectivo',
    cash_amount: 0,
    qr_amount: 0,
    items: [],
});

const paidEnough = computed(() => {
    if (form.payment_method === 'efectivo') return Number(form.cash_amount) >= total.value;
    if (form.payment_method === 'qr') return Number(form.qr_amount) >= total.value;

    return Number(form.cash_amount) > 0 && Number(form.qr_amount) > 0 && Number(form.cash_amount) + Number(form.qr_amount) >= total.value;
});

const mixedAmountMissing = computed(
    () => form.payment_method === 'mixto' && (Number(form.cash_amount) <= 0 || Number(form.qr_amount) <= 0),
);

const change = computed(() => {
    const paid = Number(form.cash_amount) + Number(form.qr_amount);

    return Math.max(0, paid - total.value);
});

const canSave = computed(() => cart.value.length > 0 && !form.processing && paidEnough.value);

const setPaymentMethod = (method) => {
    form.payment_method = method;

    if (method === 'efectivo') {
        form.cash_amount = total.value;
        form.qr_amount = 0;
    } else if (method === 'qr') {
        form.qr_amount = total.value;
        form.cash_amount = 0;
    }
};

const saleSuccess = ref(null);
let saleSuccessTimer;

const submit = () => {
    if (form.payment_method === 'efectivo') form.qr_amount = 0;
    if (form.payment_method === 'qr') {
        form.cash_amount = 0;
        form.qr_amount = total.value;
    }

    form.items = cart.value.map((item) => ({
        product_id: item.product_id,
        quantity: item.quantity,
    }));

    const saleTotal = total.value;
    const saleChange = change.value;

    form.post('/pos/sales', {
        preserveScroll: true,
        onSuccess: (response) => {
            cart.value = [];
            form.reset();

            saleSuccess.value = { total: saleTotal, change: saleChange };
            clearTimeout(saleSuccessTimer);
            saleSuccessTimer = setTimeout(() => (saleSuccess.value = null), 3000);

            const flash = response.props.flash ?? {};

            printTicket(response.props.printer, flash.print_sale);
        },
    });
};
</script>

<template>
    <Head title="Vender" />

    <AppLayout>
        <div class="flex h-screen gap-4 p-4">
            <!-- Productos -->
            <div class="flex flex-1 flex-col overflow-hidden rounded-xl bg-white shadow">
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 p-4">
                    <button
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                        :class="selectedCategory === null ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        @click="selectedCategory = null"
                    >
                        Todos
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category.id"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                        :class="selectedCategory === category.id ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        @click="selectedCategory = category.id"
                    >
                        {{ category.name }}
                    </button>

                    <input
                        v-model="search"
                        type="search"
                        placeholder="Buscar producto…"
                        class="ml-auto w-56 rounded-full border border-slate-200 px-4 py-1.5 text-sm focus:border-emerald-500 focus:outline-none"
                    />
                </div>

                <div class="grid flex-1 auto-rows-min grid-cols-2 gap-3 overflow-y-auto p-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                    <button
                        v-for="product in filteredProducts"
                        :key="product.id"
                        class="group overflow-hidden rounded-xl border border-slate-200 bg-white text-left shadow-sm transition hover:border-emerald-400 hover:shadow-md"
                        @click="addToCart(product)"
                    >
                        <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-100">
                            <img
                                v-if="product.photo_path"
                                :src="`/storage/${product.photo_path}`"
                                :alt="product.name"
                                class="absolute inset-0 h-full w-full object-cover object-center"
                            />
                            <div v-else class="flex h-full items-center justify-center text-4xl">🍽️</div>
                        </div>
                        <div class="p-3">
                            <p class="truncate text-sm font-semibold text-slate-800 group-hover:text-emerald-600">
                                {{ product.name }}
                            </p>
                            <p class="mt-1 text-lg font-bold text-emerald-600">
                                Bs. {{ Number(product.price).toFixed(2) }}
                            </p>
                        </div>
                    </button>

                    <p v-if="!filteredProducts.length" class="col-span-full py-12 text-center text-slate-400">
                        No hay productos.
                    </p>
                </div>
            </div>

            <!-- Carrito -->
            <div class="flex w-96 shrink-0 flex-col overflow-hidden rounded-xl bg-white shadow">
                <div class="flex items-center justify-between bg-slate-800 px-4 py-3 text-white">
                    <span class="font-semibold">🛒 Pedido</span>
                    <span class="rounded-full bg-emerald-500 px-2.5 py-0.5 text-sm font-bold">{{ cartCount }}</span>
                </div>

                <div class="flex-1 overflow-y-auto p-3">
                    <div v-if="!cart.length" class="flex h-full flex-col items-center justify-center text-slate-400">
                        <span class="text-5xl">🛒</span>
                        <p class="mt-2">El carrito está vacío</p>
                    </div>

                    <div
                        v-for="item in cart"
                        :key="item.product_id"
                        class="mb-2 flex items-center gap-3 rounded-lg border border-slate-100 p-2"
                    >
                        <img
                            v-if="item.photo_path"
                            :src="`/storage/${item.photo_path}`"
                            :alt="item.name"
                            class="h-10 w-10 rounded-md object-cover"
                        />
                        <div v-else class="flex h-10 w-10 items-center justify-center rounded-md bg-slate-100">🍽️</div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">{{ item.name }}</p>
                            <p class="text-xs text-slate-500">Bs. {{ item.price.toFixed(2) }} c/u</p>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-600 hover:bg-slate-200"
                                @click="decreaseQuantity(item)"
                            >
                                −
                            </button>
                            <span class="w-6 text-center text-sm font-semibold">{{ item.quantity }}</span>
                            <button
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700 hover:bg-emerald-200"
                                @click="item.quantity++"
                            >
                                +
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 border-t border-slate-100 p-4">
                    <!-- Tipo de venta -->
                    <div>
                        <p class="mb-1.5 text-sm font-semibold text-slate-500 uppercase">Tipo de venta</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                class="rounded-lg border-2 py-4 text-lg font-semibold transition"
                                :class="form.type === 'mesa' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500'"
                                @click="form.type = 'mesa'"
                            >
                                🍴 Para Mesa
                            </button>
                            <button
                                class="rounded-lg border-2 py-4 text-lg font-semibold transition"
                                :class="form.type === 'llevar' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500'"
                                @click="form.type = 'llevar'"
                            >
                                🛍️ Para Llevar
                            </button>
                        </div>
                        <input
                            v-if="form.type === 'mesa'"
                            v-model.number="form.table_number"
                            type="number"
                            min="1"
                            placeholder="N° de mesa (opcional)"
                            class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                        />
                    </div>

                    <!-- Método de pago -->
                    <div>
                        <p class="mb-1.5 text-sm font-semibold text-slate-500 uppercase">Método de pago</p>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="method in [
                                    { value: 'efectivo', label: '💵 Efectivo' },
                                    { value: 'qr', label: '📱 QR/Transf.' },
                                    { value: 'mixto', label: '💵+📱 Mixto' },
                                ]"
                                :key="method.value"
                                class="rounded-lg border-2 py-4 text-base font-semibold transition"
                                :class="form.payment_method === method.value ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500'"
                                @click="setPaymentMethod(method.value)"
                            >
                                {{ method.label }}
                            </button>
                        </div>

                        <div class="mt-2 space-y-2">
                            <div v-if="form.payment_method !== 'qr'" class="flex items-center gap-2">
                                <span class="w-20 text-sm font-medium text-slate-500">Efectivo Bs.</span>
                                <input
                                    v-model.number="form.cash_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="flex-1 rounded-lg border border-slate-300 px-3 py-2.5 text-right text-base font-semibold focus:border-emerald-500 focus:outline-none"
                                />
                            </div>
                            <div v-if="form.payment_method !== 'efectivo'" class="flex items-center gap-2">
                                <span class="w-20 text-sm font-medium text-slate-500">QR Bs.</span>
                                <input
                                    v-model.number="form.qr_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :readonly="form.payment_method === 'qr'"
                                    class="flex-1 rounded-lg border border-slate-300 px-3 py-2.5 text-right text-base font-semibold focus:border-emerald-500 focus:outline-none"
                                    :class="form.payment_method === 'qr' ? 'cursor-not-allowed bg-slate-100 text-slate-500' : ''"
                                />
                            </div>
                            <p
                                v-if="cart.length && change > 0"
                                class="rounded-lg bg-blue-50 px-3 py-2 text-right text-lg font-bold text-blue-700"
                            >
                                Cambio: Bs. {{ change.toFixed(2) }}
                            </p>
                            <p
                                v-if="cart.length && mixedAmountMissing"
                                class="rounded-lg bg-red-50 px-3 py-2 text-right text-base font-bold text-red-700"
                            >
                                Pago mixto: efectivo y QR deben ser mayores a 0
                            </p>
                            <p
                                v-if="cart.length && total - Number(form.cash_amount) - Number(form.qr_amount) > 0"
                                class="rounded-lg bg-red-50 px-3 py-2 text-right text-lg font-bold text-red-700"
                            >
                                Falta: Bs. {{ (total - Number(form.cash_amount) - Number(form.qr_amount)).toFixed(2) }}
                            </p>
                        </div>
                    </div>

                    <!-- Total -->
                    <div
                        class="flex items-center justify-between rounded-lg border-t border-slate-100 px-2 pt-3 pb-1 transition-colors duration-300"
                        :class="totalHighlight ? 'bg-amber-50' : ''"
                    >
                        <span class="text-sm font-semibold text-slate-500 uppercase">Total a cobrar</span>
                        <span
                            class="inline-block transform text-3xl font-bold transition-all duration-300"
                            :class="totalHighlight ? 'scale-125 text-amber-500' : 'scale-100 text-emerald-600'"
                        >
                            Bs. {{ total.toFixed(2) }}
                        </span>
                    </div>

                    <button
                        :disabled="!canSave"
                        class="w-full rounded-lg bg-emerald-500 py-3 font-bold text-white transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-40"
                        @click="submit"
                    >
                        ✓ Guardar Venta
                    </button>
                </div>
            </div>
        </div>

        <!-- Venta registrada -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300"
                enter-from-class="scale-50 opacity-0"
                leave-active-class="transition duration-300"
                leave-to-class="scale-110 opacity-0"
            >
                <div
                    v-if="saleSuccess"
                    class="fixed inset-0 z-[110] flex items-center justify-center bg-black/30"
                    @click="saleSuccess = null"
                >
                    <div class="flex flex-col items-center rounded-3xl bg-white px-12 py-10 shadow-2xl">
                        <span class="flex h-20 w-20 animate-bounce items-center justify-center rounded-full bg-emerald-500 text-5xl text-white shadow-lg shadow-emerald-300">
                            ✓
                        </span>
                        <p class="mt-4 text-2xl font-bold text-slate-800">¡Venta registrada!</p>
                        <p class="mt-1 text-4xl font-extrabold text-emerald-600">
                            Bs. {{ saleSuccess.total.toFixed(2) }}
                        </p>
                        <p v-if="saleSuccess.change > 0" class="mt-2 rounded-full bg-amber-100 px-4 py-1 text-lg font-semibold text-amber-700">
                            Cambio: Bs. {{ saleSuccess.change.toFixed(2) }}
                        </p>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
