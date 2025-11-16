<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import Toast from '@/components/Toast.vue';
import type { BreadcrumbItemType } from '@/types';
import { provide, reactive } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}
const toastState = reactive({
  show: false,
  message: '',
  type: 'info' as 'success' | 'error' | 'info',
  position: 'top-right' as 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right',
});
provide('toast', {
  showToast,
})

function showToast(message: string, type: 'success' | 'error' | 'info' = 'info', position: 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right' = 'top-right') {
  toastState.message = message;
  toastState.type = type;
  toastState.position = position;
  toastState.show = true;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <Toast
            :show="toastState.show"
            :message="toastState.message"
            :type="toastState.type"
            :position="toastState.position"
        />
    </AppShell>
</template>
