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

    <div class="grid min-h-screen place-items-center bg-slate-900 px-4 py-8">
        <div class="grid w-full max-w-5xl items-center gap-8 lg:grid-cols-[1.1fr_0.9fr]">
            <section class="text-white">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-200">
                    PuntoResto
                </div>
                <h2 class="mt-5 max-w-xl text-4xl font-black leading-tight sm:text-5xl">
                    Gestiona ventas, mesas y reportes desde un solo lugar.
                </h2>
                <p class="mt-4 max-w-lg text-base leading-7 text-slate-300">
                    Sistema desarrollado para restaurantes que necesitan vender rápido, imprimir tickets y revisar sus movimientos con claridad.
                </p>

                <div class="mt-8 grid max-w-xl gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-2xl font-black text-emerald-300">POS</p>
                        <p class="mt-1 text-sm text-slate-300">Ventas ágiles</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-2xl font-black text-sky-300">USB</p>
                        <p class="mt-1 text-sm text-slate-300">Tickets directos</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-2xl font-black text-amber-300">24/7</p>
                        <p class="mt-1 text-sm text-slate-300">Listo para operar</p>
                    </div>
                </div>

                <div class="mt-8 border-l-4 border-emerald-400 pl-4">
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Desarrollado por</p>
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <span class="inline-flex motion-safe:animate-pulse text-3xl" aria-hidden="true">👉</span>
                        <a
                            href="https://www.soluciondigital.dev/"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center rounded-full bg-emerald-400 px-4 py-2 text-xl font-black text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-300"
                        >
                            Solución Digital
                        </a>
                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-bold uppercase text-emerald-200">
                            Haz clic
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-400">Tecnología para negocios que venden todos los días.</p>
                </div>
            </section>

            <div class="w-full rounded-2xl bg-white p-8 shadow-xl">
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
                <p class="mt-5 text-center text-xs font-medium text-slate-500">
                    Desarrollado por
                    <a
                        href="https://www.soluciondigital.dev/"
                        target="_blank"
                        rel="noopener"
                        class="font-bold text-emerald-600 hover:text-emerald-700"
                    >
                        Solución Digital
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>
