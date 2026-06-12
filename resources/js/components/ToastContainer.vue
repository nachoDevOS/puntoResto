<script setup>
import { useToast } from '../composables/useToast';

const props = defineProps({
    position: { type: String, default: 'bottom-center' },
});

const { toasts, dismiss } = useToast();

const positionClasses = {
    'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2 flex-col-reverse',
    'top-left': 'top-4 left-60 flex-col',
};

const styles = {
    success: { bar: 'bg-emerald-500', icon: '✓', iconBg: 'bg-emerald-100 text-emerald-600' },
    error: { bar: 'bg-red-500', icon: '✕', iconBg: 'bg-red-100 text-red-600' },
    warning: { bar: 'bg-amber-500', icon: '!', iconBg: 'bg-amber-100 text-amber-600' },
    info: { bar: 'bg-blue-500', icon: 'i', iconBg: 'bg-blue-100 text-blue-600' },
};
</script>

<template>
    <Teleport to="body">
        <div class="pointer-events-none fixed z-[100] flex w-80 gap-2" :class="positionClasses[props.position]">
            <TransitionGroup
                enter-active-class="transition duration-300"
                enter-from-class="translate-y-4 opacity-0"
                leave-active-class="transition duration-200"
                leave-to-class="translate-y-4 opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto flex items-center gap-3 overflow-hidden rounded-xl bg-white p-3 shadow-lg ring-1 ring-black/5"
                >
                    <div class="w-1 self-stretch rounded-full" :class="styles[toast.type].bar"></div>
                    <span
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                        :class="styles[toast.type].iconBg"
                    >
                        {{ styles[toast.type].icon }}
                    </span>
                    <p class="flex-1 text-sm font-medium text-slate-700">{{ toast.message }}</p>
                    <button
                        class="shrink-0 rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        @click="dismiss(toast.id)"
                    >
                        ✕
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
