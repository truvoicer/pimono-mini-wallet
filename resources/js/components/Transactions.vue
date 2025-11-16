<script setup lang="ts">
import { Pagination } from '@/types/pagination';

import { Transaction } from '@/types/Transaction';
import { User } from '@/types/user';
import { router } from '@inertiajs/vue3';
import Datatable from './ui/table/Datatable.vue';
import Spinner from './ui/spinner/Spinner.vue';
import { Currency } from '@/types/currency';

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

</script>
<template>

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

        <div class="relative p-4 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <h2 class="flex items-center gap-2 text-lg font-medium">
                Balance: <span class="ml-2">{{ props.settings?.data?.currency.symbol || '£' }}{{ user?.balance }}</span>
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
                @page-change="page => router.reload({ only: ['transactions'], data: { page } })" >
                <template #amount="{ row }">
                    {{ props.settings?.data?.currency.symbol || '£' }}{{ row.amount }}
                </template>
                <template #commission_fee="{ row }">
                    {{ props.settings?.data?.currency.symbol || '£' }}{{ row.commission_fee }}
                </template>
            </Datatable>
        </div>
    </div>
</template>
