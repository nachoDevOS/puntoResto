import { ref } from 'vue';

const toasts = ref([]);
let nextId = 1;

export function useToast() {
    const dismiss = (id) => {
        toasts.value = toasts.value.filter((toast) => toast.id !== id);
    };

    const show = (message, type = 'success', duration = 3500) => {
        const id = nextId++;
        toasts.value.push({ id, message, type });

        if (duration) {
            setTimeout(() => dismiss(id), duration);
        }
    };

    return {
        toasts,
        dismiss,
        success: (message) => show(message, 'success'),
        error: (message) => show(message, 'error', 5000),
        info: (message) => show(message, 'info'),
        warning: (message) => show(message, 'warning', 4500),
    };
}
