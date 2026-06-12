<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';

defineProps({
    products: Array,
    categories: Array,
});

const toggleActive = (product) => {
    router.patch(`/products/${product.id}/toggle-active`, {}, { preserveScroll: true });
};

const editing = ref(null);
const showForm = ref(false);
const photoPreview = ref(null);

const form = useForm({
    category_id: '',
    name: '',
    price: '',
    photo: null,
    is_active: true,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    photoPreview.value = null;
    showForm.value = true;
};

const openEdit = (product) => {
    editing.value = product;
    form.category_id = product.category_id;
    form.name = product.name;
    form.price = product.price;
    form.photo = null;
    form.is_active = product.is_active;
    form.clearErrors();
    photoPreview.value = product.photo_path ? `/storage/${product.photo_path}` : null;
    showForm.value = true;
};

const onPhotoChange = (event) => {
    const file = event.target.files[0];
    form.photo = file ?? null;
    photoPreview.value = file ? URL.createObjectURL(file) : null;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showForm.value = false;
            form.reset();
            photoPreview.value = null;
        },
    };

    if (editing.value) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(`/products/${editing.value.id}`, options);
    } else {
        form.post('/products', options);
    }
};

const destroy = (product) => {
    if (confirm(`¿Eliminar el producto "${product.name}"?`)) {
        useForm({}).delete(`/products/${product.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Productos" />

    <AppLayout>
        <div class="mx-auto max-w-5xl p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-800">Productos</h1>
                <button
                    class="rounded-lg bg-emerald-500 px-4 py-2 font-semibold text-white transition hover:bg-emerald-600"
                    @click="openCreate"
                >
                    ➕ Nuevo producto
                </button>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3">Foto</th>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Categoría</th>
                            <th class="px-4 py-3">Precio</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="product in products" :key="product.id">
                            <td class="px-4 py-2">
                                <img
                                    v-if="product.photo_path"
                                    :src="`/storage/${product.photo_path}`"
                                    :alt="product.name"
                                    class="h-12 w-12 rounded-lg object-cover"
                                />
                                <div v-else class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-xl">🍽️</div>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ product.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ product.category?.name }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">Bs. {{ Number(product.price).toFixed(2) }}</td>
                            <td class="px-4 py-3">
                                <button
                                    class="group inline-flex cursor-pointer items-center gap-2"
                                    :title="product.is_active ? 'Clic para desactivar' : 'Clic para activar'"
                                    @click="toggleActive(product)"
                                >
                                    <span
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition"
                                        :class="product.is_active ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                                            :class="product.is_active ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        ></span>
                                    </span>
                                    <span
                                        class="text-xs font-semibold"
                                        :class="product.is_active ? 'text-emerald-600' : 'text-slate-400'"
                                    >
                                        {{ product.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button
                                    class="rounded-lg bg-blue-50 p-2 text-blue-600 transition hover:bg-blue-100"
                                    title="Editar producto"
                                    @click="openEdit(product)"
                                >
                                    ✏️
                                </button>
                                <button
                                    class="ml-2 rounded-lg bg-red-50 p-2 text-red-600 transition hover:bg-red-100"
                                    title="Eliminar producto"
                                    @click="destroy(product)"
                                >
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!products.length">
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">Sin productos. Creá el primero.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showForm = false">
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-bold text-slate-800">
                    {{ editing ? 'Editar producto' : 'Nuevo producto' }}
                </h2>

                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="category">Categoría</label>
                        <select
                            id="category"
                            v-model="form.category_id"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:outline-none"
                        >
                            <option value="" disabled>Seleccionar…</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="product-name">Nombre</label>
                        <input
                            id="product-name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="price">Precio (Bs.)</label>
                        <input
                            id="price"
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="photo">Foto</label>
                        <input
                            id="photo"
                            type="file"
                            accept="image/*"
                            class="w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:font-medium file:text-emerald-700"
                            @change="onPhotoChange"
                        />
                        <img v-if="photoPreview" :src="photoPreview" alt="Vista previa" class="mt-2 h-24 w-24 rounded-lg object-cover" />
                        <p v-if="form.errors.photo" class="mt-1 text-sm text-red-600">{{ form.errors.photo }}</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300" />
                        Activo (visible en ventas)
                    </label>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-slate-600 transition hover:bg-slate-100"
                            @click="showForm = false"
                        >
                            ✖️ Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-emerald-500 px-4 py-2 font-semibold text-white transition hover:bg-emerald-600 disabled:opacity-50"
                        >
                            💾 Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
