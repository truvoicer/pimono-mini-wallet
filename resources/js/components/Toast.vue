
<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Define available positions
type ToastPosition = 'top-center' | 'bottom-center' | 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right';

const props =defineProps<{
    show?: boolean;
    message?: string;
    type?: 'success' | 'error' | 'info';
    position?: ToastPosition;
}>();

const page = usePage();
const isVisible = ref(false);
const message = ref('');
const type = ref<'success' | 'error' | 'info'>('info');
const position = ref<ToastPosition>('top-center'); // Default position

let hideTimeout: number | null = null;

// CSS Classes for positioning
const positionClasses = {
    'top-center': 'top-5 left-1/2 -translate-x-1/2',
    'bottom-center': 'bottom-5 left-1/2 -translate-x-1/2',
    'top-left': 'top-5 left-5',
    'top-right': 'top-5 right-5',
    'bottom-left': 'bottom-5 left-5',
    'bottom-right': 'bottom-5 right-5',
};

// CSS Classes for styling based on type
const toastClasses = computed(() => {
    switch (type.value) {
        case 'success':
            return { bgColor: 'bg-green-500 hover:bg-green-600' };
        case 'error':
            return { bgColor: 'bg-red-500 hover:bg-red-600' };
        default: // info
            return { bgColor: 'bg-blue-500 hover:bg-blue-600' };
    }
});

function showToast(msg: string, toastType: 'success' | 'error' | 'info', toastPosition: ToastPosition = 'top-center', duration: number = 3000) {
    // Clear any existing timeout
    if (hideTimeout !== null) {
        clearTimeout(hideTimeout);
    }

    message.value = msg;
    type.value = toastType;
    position.value = toastPosition;
    isVisible.value = true;

    // Set timeout to hide the toast
    hideTimeout = window.setTimeout(hideToast, duration);
}

function hideToast() {
    isVisible.value = false;
    if (hideTimeout !== null) {
        clearTimeout(hideTimeout);
        hideTimeout = null;
    }
}

// watch(() => page.props.flash, (newFlash: { success?: string; error?: string }) => {
//     if (newFlash?.success) {
//         showToast(newFlash.success, 'success', 'top-right');
//     } else if (newFlash?.error) {
//         showToast(newFlash.error, 'error', 'top-right');
//     }
// }, { deep: true });

watch(() => props.show, (newValue: boolean) => {
    isVisible.value = newValue;
}, { deep: true });
watch(() => props.message, (newValue: string) => {
    message.value = newValue;
}, { deep: true });

watch(() => props.type, (newValue: 'success' | 'error' | 'info') => {
    type.value = newValue;
}, { deep: true });

watch(() => props.position, (newValue: ToastPosition) => {
    position.value = newValue;
}, { deep: true });

</script>

<template>
    <div
        v-if="isVisible"
        :class="[
            'fixed z-50 p-4 text-white rounded-lg shadow-xl transition-all duration-500 ease-in-out transform',
            toastClasses.bgColor,
            positionClasses[position],
        ]"
        @click="hideToast"
    >
        <div class="flex items-center space-x-3">
            <!-- Icon -->
            <svg v-if="type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <svg v-else-if="type === 'error'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>

            <!-- Message -->
            <p class="font-medium">{{ message }}</p>
        </div>
        <button class="absolute top-1 right-2 text-sm opacity-75 hover:opacity-100" @click.stop="hideToast">&times;</button>
    </div>
</template>


<style scoped>
.transition-all {
    transition-property: all;
}
</style>
