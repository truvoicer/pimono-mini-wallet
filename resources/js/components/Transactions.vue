<script setup lang="ts">
import { Pagination } from '@/types/pagination';

import { Transaction } from '@/types/Transaction';
import { User } from '@/types/user';
import { useEcho } from '@laravel/echo-vue';
import { router } from '@inertiajs/vue3';
import Datatable from './ui/table/Datatable.vue';
import Spinner from './ui/spinner/Spinner.vue';
import { inject } from 'vue';

const props = defineProps<{
    transactions?: Pagination & { data: Transaction[] };
    user?: User;
}>();
const toast = inject<{
    showToast: (msg: string, type: 'success' | 'error' | 'info', position?: string) => void;
}>('toast');

useEcho(
    `transactions.user.${props.user.id}`,
    "TransactionSent",
    (e: { transaction: Transaction }) => {
        router.reload({ only: ['transactions', 'user'] });
        if (typeof toast?.showToast === 'function') {
            toast.showToast('Transaction sent successfully', 'success', 'top-right');
        }
    },
);
useEcho(
    `transactions.user.${props.user.id}`,
    "TransactionReceived",
    (e: { transaction: Transaction }) => {
        router.reload({ only: ['transactions', 'user'] });
        if (typeof toast?.showToast === 'function') {
            toast.showToast('Transaction received successfully', 'success', 'top-right');
        }
    },
);

</script>
<template>

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

        <div class="relative p-4 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <h2 class="flex items-center gap-2 text-lg font-medium">
                Balance: {{ user?.balance }}
                <Spinner v-if="!user" />
            </h2>
        </div>
        <div class="relative p-4 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
            <Datatable v-if="transactions" :data="transactions" :columns="[
                { label: 'ID', field: 'id' },
                { label: 'Sender', field: 'sender.name' },
                { label: 'Receiver', field: 'receiver.name' },
                { label: 'Amount', field: 'amount' },
                { label: 'Commission Fee', field: 'commission_fee' },
                { label: 'Created At', field: 'created_at' },
            ]" :loading="!transactions" class="h-full"
                @page-change="page => router.reload({ only: ['transactions'], data: { page } })" />
        </div>
    </div>
</template>
