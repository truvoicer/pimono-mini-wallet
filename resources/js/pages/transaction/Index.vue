<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { index } from '@/actions/App/Http/Controllers/TransactionController';
import { Pagination } from '@/types/pagination';
import { Transaction } from '@/types/Transaction';
import { onMounted, watch } from 'vue';
import Datatable from '@/components/ui/table/Datatable.vue';
import { User } from '@/types/user';
import Spinner from '@/components/ui/spinner/Spinner.vue';
import { useEcho } from '@laravel/echo-vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: index().url,
    },
];

const props = defineProps<{
    transactions?: Pagination & { data: Transaction[] };
    user?: User;
}>();

onMounted(() => {
    router.reload({ only: ['transactions', 'user'] });
});
console.log('Initial user data:', props.user);
useEcho(
    `transactions.user.${props.user.data.id}`,
    "TransactionCreated",
    (e: { transaction: Transaction }) => {
        console.log(e.transaction);
    },
);
watch(
    () => props.user,
    () => {
        console.log('User data updated:', props.user);
    }
);

</script>

<template>
    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >

            <div
                class="relative p-4 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <h2 class="flex items-center gap-2 text-lg font-medium">
                    Balance: {{ user?.data?.balance }}
                    <Spinner v-if="!user" />
                </h2>
            </div>
            <div
                class="relative p-4 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <Datatable
                    v-if="transactions"
                    :data="transactions"
                    :columns="[
                        { label: 'ID', field: 'id' },
                        { label: 'Sender', field: 'sender.name' },
                        { label: 'Receiver', field: 'receiver.name' },
                        { label: 'Amount', field: 'amount' },
                        { label: 'Commission Fee', field: 'commission_fee' },
                        { label: 'Created At', field: 'created_at' },
                    ]"
                    :loading="!transactions"
                    class="h-full"
                    @page-change="page => router.reload({ only: ['transactions'], data: { page } })"
                />
            </div>
        </div>
    </AppLayout>
</template>
