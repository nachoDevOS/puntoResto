<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../layouts/AppLayout.vue';

defineProps({
    categories: Array,
});

const toggleActive = (category) => {
    router.patch(`/categories/${category.id}/toggle-active`, {}, { preserveScroll: true });
};

const editing = ref(null);
const showForm = ref(false);

const form = useForm({
    name: '',
    is_active: true,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (category) => {
    editing.value = category;
    form.name = category.name;
    form.is_active = category.is_active;
    form.clearErrors();
    showForm.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
            form.reset();
        },
    };

    if (editing.value) {
        form.put(`/categories/${editing.value.id}`, options);
    } else {
        form.post('/categories', options);
    }
};

const destroy = (category) => {
    if (confirm(`¿Eliminar la categoría "${category.name}"?`)) {
        useForm({}).delete(`/categories/${category.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Categorías" />

    <AppLayout>
        <div class="mx-auto max-w-3xl p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-800">Categorías</h1>
                <button
                    class="rounded-lg bg-emerald-500 px-4 py-2 font-semibold text-white transition hover:bg-emerald-600"
                    @click="openCreate"
                >
                    ➕ Nueva categoría
                </button>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Productos</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="category in categories" :key="category.id">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ category.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ category.products_count }}</td>
                            <td class="px-4 py-3">
                                <button
                                    class="group inline-flex cursor-pointer items-center gap-2"
                                    :title="category.is_active ? 'Clic para desactivar' : 'Clic para activar'"
                                    @click="toggleActive(category)"
                                >
                                    <span
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition"
                                        :class="category.is_active ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <span
                                            class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                                            :class="category.is_active ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        ></span>
                                    </span>
                                    <span
                                        class="text-xs font-semibold"
                                        :class="category.is_active ? 'text-emerald-600' : 'text-slate-400'"
                                    >
                                        {{ category.is_active ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button
                                    class="rounded-lg bg-blue-50 p-2 text-blue-600 transition hover:bg-blue-100"
                                    title="Editar categoría"
                                    @click="openEdit(category)"
                                >
                                    ✏️
                                </button>
                                <button
                                    class="ml-2 rounded-lg bg-red-50 p-2 text-red-600 transition hover:bg-red-100"
                                    title="Eliminar categoría"
                                    @click="destroy(category)"
                                >
                                    🗑️
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!categories.length">
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">Sin categorías. Creá la primera.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showForm = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-bold text-slate-800">
                    {{ editing ? 'Editar categoría' : 'Nueva categoría' }}
                </h2>

                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" for="name">Nombre</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:outline-none"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300" />
                        Activa
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
