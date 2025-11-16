<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { index } from '@/actions/App/Http/Controllers/TransactionController';
import { Pagination } from '@/types/pagination';
import { Transaction } from '@/types/Transaction';
import { onMounted } from 'vue';
import { User } from '@/types/user';
import Transactions from '@/components/Transactions.vue';
import { Currency } from '@/types/currency';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: index().url,
    },
];

const props = defineProps<{
    transactions?: Pagination & { data: Transaction[] };
    user?: User;
    settings: {
        data: {
            timezone: string;
            currency: Currency;
        };
    } | null;
}>();

onMounted(() => {
    router.reload({ only: ['transactions', 'user'] });
});

</script>

<template>

    <Head title="Transactions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Transactions
        :transactions="props.transactions" :user="props.user.data" :settings="props.settings" />
    </AppLayout>
</template>
