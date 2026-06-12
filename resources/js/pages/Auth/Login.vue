<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="PuntoResto" />

    <div class="relative grid min-h-screen place-items-center overflow-hidden bg-slate-950 px-4 py-8">
        <!-- Resplandor de fondo -->
        <div class="pointer-events-none absolute -top-40 -left-40 h-[28rem] w-[28rem] rounded-full bg-emerald-500/15 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 -bottom-40 h-[28rem] w-[28rem] rounded-full bg-sky-500/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative grid w-full max-w-5xl items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
            <section class="text-white">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-200">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400" aria-hidden="true"></span>
                    PuntoResto · Sistema POS
                </div>
                <h2 class="mt-5 max-w-xl text-4xl font-black leading-tight sm:text-5xl">
                    Gestiona ventas, mesas y reportes
                    <span class="bg-gradient-to-r from-emerald-300 to-sky-300 bg-clip-text text-transparent">desde un solo lugar.</span>
                </h2>
                <p class="mt-4 max-w-lg text-base leading-7 text-slate-300">
                    Sistema desarrollado para restaurantes que necesitan vender rapido, imprimir tickets y revisar sus movimientos con claridad.
                </p>

                <div class="mt-8 grid max-w-xl gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 transition hover:border-emerald-300/40 hover:bg-white/10">
                        <p class="text-2xl font-black text-emerald-300">POS</p>
                        <p class="mt-1 text-sm text-slate-300">Ventas agiles</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 transition hover:border-sky-300/40 hover:bg-white/10">
                        <p class="text-2xl font-black text-sky-300">USB</p>
                        <p class="mt-1 text-sm text-slate-300">Tickets directos</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 transition hover:border-amber-300/40 hover:bg-white/10">
                        <p class="text-2xl font-black text-amber-300">24/7</p>
                        <p class="mt-1 text-sm text-slate-300">Listo para operar</p>
                    </div>
                </div>

                <!-- Marketing Solucion Digital -->
                <a
                    href="https://www.soluciondigital.dev/"
                    target="_blank"
                    rel="noopener"
                    class="group mt-8 flex max-w-xl items-center justify-between gap-4 rounded-2xl border border-emerald-300/20 bg-gradient-to-r from-emerald-400/10 to-sky-400/5 p-5 transition hover:border-emerald-300/50 hover:from-emerald-400/20"
                >
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Desarrollado por</p>
                        <p class="mt-1 text-2xl font-black text-emerald-300 transition group-hover:text-emerald-200">
                            Solucion Digital
                        </p>
                        <p class="mt-1 text-sm text-slate-400">
                            ¿Necesitas un sistema como este para tu negocio? Hablemos.
                        </p>
                    </div>
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-400 text-xl font-black text-slate-950 shadow-lg shadow-emerald-500/30 transition group-hover:translate-x-1 group-hover:bg-emerald-300"
                        aria-hidden="true"
                    >
                        →
                    </span>
                </a>
            </section>

            <div class="w-full rounded-2xl bg-white p-8 shadow-2xl shadow-emerald-500/10">
                <h1 class="mb-5 text-center text-2xl font-bold text-slate-800">🍽️ PuntoResto</h1>

                <div class="space-y-5 text-center">
                    <p class="text-sm leading-6 text-slate-500">
                        Acceso libre para operar ventas, productos y reportes sin usuario ni contrasena.
                    </p>

                    <Link
                        href="/pos"
                        class="block w-full rounded-lg bg-emerald-500 py-3 text-base font-bold text-white transition hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/30"
                    >
                        Ingresar al sistema
                    </Link>
                </div>

                <p class="mt-5 text-center text-xs font-medium text-slate-500">
                    Desarrollado por
                    <a
                        href="https://www.soluciondigital.dev/"
                        target="_blank"
                        rel="noopener"
                        class="font-bold text-emerald-600 hover:text-emerald-700"
                    >
                        Solucion Digital
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>
