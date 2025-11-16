<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { create, store } from '@/actions/App/Http/Controllers/TransactionController';
import TextInput from '@/components/TextInput.vue';
import Spinner from '@/components/ui/spinner/Spinner.vue';
import { Currency } from '@/types/currency';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transfer',
        href: create().url,
    },
];
const props = defineProps<{
    settings: {
        data: {
            timezone: string;
            currency: Currency;
        };
    } | null;
}>();
</script>

<template>
    <Head title="Transfer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full self-start justify-start flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:max-w-lg"
        >
            <div
                class="relative rounded-xl border border-sidebar-border/70  dark:border-sidebar-border p-6 md:max-w-lg"
            >
                <Form
                    :action="store()"
                    v-bind="create()"
                    :reset-on-success="['receiver_id', 'amount']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-6"
                >
                    <div class="grid gap-6">

                        <TextInput
                            label="Receiver ID"
                            id="receiver_id"
                            type="number"
                            name="receiver_id"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="receiver_id"
                            placeholder="Receiver ID"
                            :error="errors.receiver_id"
                        />

                        <TextInput
                            label="Amount"
                            id="amount"
                            type="number"
                            name="amount"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="amount"
                            :placeholder="`${props.settings?.data?.currency.symbol || '£'} 100`"
                            :error="errors.amount"
                        />

                        <button
                            type="submit"
                            class="mt-4 w-full"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                        >
                            <Spinner v-if="processing" />
                            Send
                        </button>
                    </div>

                </Form>
            </div>
        </div>
    </AppLayout>
</template>
