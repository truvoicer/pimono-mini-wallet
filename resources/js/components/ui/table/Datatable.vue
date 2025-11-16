<script setup lang="ts">
import { Pagination } from '@/types/pagination';
defineEmits<{
    (e: 'page-change', page: number): void;
}>();
defineProps<{
    data: Pagination & {
        data: Record<string, unknown>[];
    },
    columns: { label: string; field: string }[],
    loading: boolean;
}>();

function getNestedField(row: Record<string, any>, field: string): any {
    return field.split('.').reduce((obj, key) => (obj && obj[key] !== 'undefined') ? obj[key] : null, row);
}
</script>
<template>
    <div class="h-full w-full overflow-auto">
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr>
                    <th v-for="(column, index) in columns" :key="index"
                        class="border-b border-sidebar-border/70 px-4 py-2 text-left font-medium dark:border-sidebar-border">
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading">
                    <td :colspan="columns.length" class="p-4 text-center">
                        Loading...
                    </td>
                </tr>
                <tr v-else-if="data.data.length === 0">
                    <td :colspan="columns.length" class="p-4 text-center">
                        No data available.
                    </td>
                </tr>
                <tr v-else v-for="(row, rowIndex) in data.data" :key="rowIndex">
                    <td v-for="(column, colIndex) in columns" :key="colIndex"
                        class="border-b border-sidebar-border/70 px-4 py-2 dark:border-sidebar-border">
                        <slot v-if="$slots[column.field]" :name="column.field" :row="row" />
                        <template v-else-if="column.field.includes('.')">
                            {{ getNestedField(row, column.field) }}
                        </template>
                        <template v-else>
                            {{ row[column.field] }}
                        </template>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="mt-4 flex justify-start">
            <ul>
                <li
                    v-for="page in data.meta.last_page"
                    :key="page"
                    class="inline-block mx-1"
                >
                    <a
                        @click="$emit('page-change', page)"
                        :class="{
                            'px-3 py-1 rounded-md cursor-pointer': true,
                            'bg-secondary text-white': data.meta.current_page === page,
                            'bg-blue text-foreground': data.meta.current_page !== page,
                        }"
                    >
                        {{ page }}
                    </a>
                </li>
            </ul>
        </div>

    </div>
</template>
