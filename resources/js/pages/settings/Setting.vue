<script setup lang="ts">
import SettingsController from '@/actions/App/Http/Controllers/Settings/SettingsController';
import { edit } from '@/routes/settings';
import { Form, Head, usePage } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Currency } from '@/types/currency';
import { index } from '@/actions/App/Http/Controllers/Currency/Data/CurrencyController';
import { ref } from 'vue';
import LoadMoreSelect from '@/components/ui/LoadMoreSelect.vue';

// --- Type Definitions (Must match the component's internal types) ---
interface SelectOption {
    value: string | number;
    label: string;
}

interface Props {
    settings: {
        data: {
            timezone: string;
            currency: Currency;
        };
    } | null;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: edit().url,
    },
];

const page = usePage();

const { csrf_token } = page.props;

const fetchCurrencies = async (page: number, filterTerm: string = '') => {

    try {
        const response = await fetch(
            index().url + `?page=${page}` + (filterTerm ? `&search=${encodeURIComponent(filterTerm)}` : ''),
            {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json', // Good practice to include
                    'X-CSRF-TOKEN': csrf_token, // 2. Add the token to the headers
                },
            });

        if (!response.ok) {
            throw new Error(`Error: ${response.statusText}`);
        }
        const data = await response.json();
        if (!data) {
            throw new Error('No data found');
        }
        return data;


    } catch (error) {
        console.error('Error fetching countries:', error);
        throw error;
    }
};


/**
 * 💡 Simulates the API call when the user types in the search box.
 */
const currencySearchRequest = async (page: number, term: string) => {
    try {
        const response = await fetch(
            index().url + `?page=${page}` + (term ? `&search=${encodeURIComponent(term)}` : ''),
            {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json', // Good practice to include
                    'X-CSRF-TOKEN': csrf_token, // 2. Add the token to the headers
                },
            });

        if (!response.ok) {
            throw new Error(`Error: ${response.statusText}`);
        }
        const data = await response.json();
        if (!data) {
            throw new Error('No data found');
        }
        return data;


    } catch (error) {
        console.error('Error fetching countries:', error);
        throw error;
    }
};

const selectedCurrency = ref<SelectOption | null>(
    props.settings?.data?.currency ? {
    value: props.settings?.data?.currency.id || '',
    label: props.settings?.data?.currency.name || '',
} : null
);
const handleSelection = (user: SelectOption | null) => {
    selectedCurrency.value = user;
};
const mapData = (item: Currency): SelectOption => ({
    value: item.id,
    label: item.name,
});

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">

                <Form v-bind="SettingsController.update()" class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }">
                    <div class="grid gap-2">
                        <Label for="timezone">Timezone</Label>
                        <Input id="timezone" class="mt-1 block w-full" name="timezone"
                            :default-value="settings?.data?.timezone || 'UTC'" required autocomplete="timezone"
                            placeholder="Timezone" />
                        <InputError class="mt-2" :message="errors.timezone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="currency_id">Currency</Label>
                        <LoadMoreSelect control-id="currency-select" label="Select a Currency" name="currency_id"
                            :data-mapper="mapData" :enable-search="true" :value="selectedCurrency"
                            @update:value="handleSelection" :fetch-data="fetchCurrencies"
                            :search-data="currencySearchRequest"
                            placeholder="Start typing to search currencies..."
                            load-more-text="Loading more currencies..." :search-debounce="500" />
                        <InputError class="mt-2" :message="errors.currency_id" />
                    </div>


                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" data-test="update-profile-button">Save</Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="recentlySuccessful" class="text-sm text-neutral-600">
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

        </SettingsLayout>
    </AppLayout>
</template>
