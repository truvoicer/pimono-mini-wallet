<template>
  <div :id="controlId" class="relative w-full">
    <label v-if="label" :for="name" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
    </label>

    <input
      v-if="name"
      type="hidden"
      :name="name"
      :value="value ? value.value : ''"
    />

    <div
      class="w-full relative cursor-pointer rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
      :class="{ 'border-indigo-500 ring-1 ring-indigo-500': isOpen }"
      @click="toggleDropdown"
    >
      <span :class="{'text-gray-500': !value, 'text-gray-900': value}">
        {{ value ? value.label : placeholder }}
      </span>
      <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
        <svg
          class="h-5 w-5 text-gray-400 transform transition-transform duration-200"
          :class="{ 'rotate-180': isOpen }"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
          aria-hidden="true"
        >
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
      </span>
    </div>

    <div
      v-show="isOpen"
      ref="menuRef"
      class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg overflow-auto border border-gray-200"
      style="max-height: 280px"
    >
      <div v-if="enableSearch" class="sticky top-0 bg-white p-2 border-b border-gray-100 z-20">
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Search options..."
          class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @click.stop
        />
      </div>

      <ul class="py-1">
        <li
          v-for="option in options"
          :key="option.value"
          class="cursor-pointer select-none relative py-2 pl-3 pr-9 text-gray-900 hover:bg-indigo-50"
          :class="{ 'bg-indigo-600 text-white hover:bg-indigo-700': value && value.value === option.value }"
          @click="selectOption(option)"
        >
          {{ option.label }}
        </li>

        <li v-if="isLoading" class="text-center text-sm text-gray-500 py-2">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-500 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loadMoreText }}
        </li>

        <li
          v-if="!isLoading && options.length === 0"
          class="text-center text-sm text-gray-500 py-2"
        >
          {{ isSearching ? `No results for "${searchTerm}"` : 'No options found' }}
        </li>
         <li
          v-if="!isLoading && !hasMore && options.length > 0"
          class="text-center text-xs text-gray-400 py-1 border-t border-gray-100"
        >
          End of results
        </li>
      </ul>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';

// --- Type Definitions ---

interface SelectOption {
  value: string | number;
  label: string;
}

interface PaginationObject<T> {
  data: T[];
  links: { first: string; last: string; prev: string | null; next: string | null; };
  meta: {
    current_page: number;
    last_page: number;
    total: number;
    // ... other meta properties
  };
}

// Define a generic type for the data mapper callback
type DataMapper<T> = (item: T) => SelectOption;

export default defineComponent({
  name: 'LoadMoreSelect',
  props: {
    value: {
      type: Object as () => SelectOption | null,
      default: null,
    },
    // The mapper function to transform API data objects into {value, label}
    dataMapper: {
        type: Function as unknown as () => DataMapper<any>, // Accept any object type T
        required: false,
        default: (item: SelectOption) => item, // Default: assume data is already {value, label}
    },
    fetchData: {
      type: Function as unknown as () => (page: number) => Promise<PaginationObject<any>>, // Now accepts any type T
      required: true,
    },
    searchData: {
      type: Function as unknown as () => (page: number, term: string) => Promise<PaginationObject<any>>, // Now accepts any type T
      required: false,
    },
    // ... other props remain the same
    loadMoreText: { type: String, default: 'Loading more...' },
    placeholder: { type: String, default: 'Select an option' },
    label: { type: String, default: '' },
    controlId: { type: String, default: 'load-more-select' },
    name: { type: String, default: '' },
    enableSearch: { type: Boolean, default: false },
    searchDebounce: { type: Number, default: 300 },
  },
  emits: ['update:value'],

  setup(props, { emit }) {
    // --- State ---
    const options = ref<SelectOption[]>([]);
    const isOpen = ref(false);
    const isLoading = ref(false);
    const currentPage = ref(1);
    const hasMore = ref(true);
    const searchTerm = ref('');
    const isSearching = ref(false);

    const menuRef = ref<HTMLDivElement | null>(null);
    let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

    // --- Core Logic: Fetching ---

    const fetchOptions = async (page: number, term: string = '', reset: boolean = false) => {
      if (isLoading.value) return;

      isLoading.value = true;
      try {
        const fetchFn = term && props.searchData ? props.searchData : props.fetchData;

        const response: PaginationObject<any> = term
          ? await fetchFn(page, term)
          : await fetchFn(page);

        // --- CRITICAL CHANGE: Map the data using the provided dataMapper prop ---
        const mappedOptions: SelectOption[] = response.data.map(item => props.dataMapper(item));
        // ------------------------------------------------------------------------

        const meta = response.meta;

        options.value = reset ? mappedOptions : [...options.value, ...mappedOptions];

        // Ensure the current selected value is always present in the list
        if (props.value && !options.value.some(opt => opt.value === props.value!.value)) {
            options.value = [props.value, ...options.value];
        }

        currentPage.value = meta.current_page;
        hasMore.value = meta.current_page < meta.last_page;

      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        isLoading.value = false;
      }
    };

    // --- Watchers & Initial Load ---

    // 1. Initial Load/Re-load when opening or changing search mode
    watch(isOpen, async (open) => {
        if (open) {
            await nextTick();
            if (options.value.length === 0 || isSearching.value) {
                const term = isSearching.value ? searchTerm.value : '';
                await fetchOptions(1, term, true);
            }
            menuRef.value?.addEventListener('scroll', handleScroll);
        } else {
            menuRef.value?.removeEventListener('scroll', handleScroll);
            searchTerm.value = '';
            isSearching.value = false;
        }
    });

    // 2. Search Debouncing Watcher
    watch(searchTerm, (newTerm) => {
        if (debounceTimeout) {
            clearTimeout(debounceTimeout);
        }

        if (newTerm) {
            isSearching.value = true;
            debounceTimeout = setTimeout(() => {
                fetchOptions(1, newTerm, true);
            }, props.searchDebounce);
        } else {
            isSearching.value = false;
            fetchOptions(1, '', true);
        }
    });

    // --- UI/Event Handlers ---

    const toggleDropdown = () => {
      isOpen.value = !isOpen.value;
    };

    const selectOption = (option: SelectOption) => {
      emit('update:value', option);
      isOpen.value = false;
    };

    const handleScroll = () => {
      const menuElement = menuRef.value;
      if (!menuElement || isLoading.value || !hasMore.value) return;

      const { scrollTop, scrollHeight, clientHeight } = menuElement;
      const scrollBottom = scrollHeight - (scrollTop + clientHeight);

      if (scrollBottom < 50) {
        const nextTerm = isSearching.value ? searchTerm.value : '';
        fetchOptions(currentPage.value + 1, nextTerm);
      }
    };

    // --- Lifecycle for Click Outside ---

    const handleClickOutside = (event: MouseEvent) => {
        const target = event.target as Node;
        const rootElement = document.getElementById(props.controlId);

        if (isOpen.value && rootElement && !rootElement.contains(target)) {
            isOpen.value = false;
        }
    };

    onMounted(() => {
        document.addEventListener('click', handleClickOutside);
    });

    onBeforeUnmount(() => {
        document.removeEventListener('click', handleClickOutside);
        menuRef.value?.removeEventListener('scroll', handleScroll);
        if (debounceTimeout) {
            clearTimeout(debounceTimeout);
        }
    });

    return {
      options,
      isOpen,
      isLoading,
      hasMore,
      searchTerm,
      isSearching,
      menuRef,
      toggleDropdown,
      selectOption,
      handleScroll,
    };
  },
});
</script>
