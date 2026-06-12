<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ToastContainer from '../../components/ToastContainer.vue';
import { useToast } from '../../composables/useToast';

const toast = useToast();

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
        onError: (errors) => toast.error(errors.email ?? errors.password ?? 'No se pudo iniciar sesión.'),
    });
};
</script>

<template>
    <Head title="Iniciar sesión" />
    <ToastContainer />

    <div class="flex min-h-screen items-center justify-center bg-slate-800 px-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-8 shadow-xl">
            <h1 class="mb-6 text-center text-2xl font-bold text-slate-800">🍽️ PuntoResto</h1>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="email">Correo</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700" for="password">Contraseña</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-emerald-500 py-2.5 font-semibold text-white transition hover:bg-emerald-600 disabled:opacity-50"
                >
                    🔑 Ingresar
                </button>
            </form>
        </div>
    </div>
</template>
