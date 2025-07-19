<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    pagination: {
        type: Object,
        required: true,
        validator: (value: any) =>
            value &&
            typeof value.current_page === 'number' &&
            typeof value.last_page === 'number' &&
            (typeof value.prev_page_url === 'string' || value.prev_page_url === null) &&
            (typeof value.next_page_url === 'string' || value.next_page_url === null) &&
            typeof value.path === 'string'
    },
    currentFilters: {
        type: Object,
        default: () => ({})
    }
});

const selectedPage = ref(props.pagination?.current_page || 1);

watch(() => props.pagination?.current_page, (newPage) => {
    if (newPage !== undefined) {
        selectedPage.value = newPage;
    }
}, { immediate: true });

const goToPage = (pageNumber: number) => {
    if (router.processing) {
        return;
    }

    if (!props.pagination || !props.pagination.path) {
        console.warn("Le chemin de pagination est manquant. Impossible de naviguer.");
        return;
    }

    const params = {
        ...props.currentFilters,
        page: pageNumber,
    };

    router.get(props.pagination.path, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            selectedPage.value = pageNumber;
        }
    });
};

const goToPreviousPage = () => {
    if (props.pagination && props.pagination.prev_page_url) {
        goToPage(props.pagination.current_page - 1);
    }
};

const goToNextPage = () => {
    if (props.pagination && props.pagination.next_page_url) {
        goToPage(props.pagination.current_page + 1);
    }
};

const pageNumbersForDropdown = computed(() => {
    if (!props.pagination || props.pagination.last_page === undefined) {
        return [];
    }
    const pages = [];
    for (let i = 1; i <= props.pagination.last_page; i++) {
        pages.push(i);
    }
    return pages;
});

watch(selectedPage, (newPage) => {
    if (props.pagination && newPage !== props.pagination.current_page) {
        goToPage(newPage);
    }
});

const shouldShowDropdown = computed(() => {
    if (!props.pagination || props.pagination.last_page === undefined) {
        return false;
    }
    const threshold = 5;
    return props.pagination.last_page > threshold;
});

const simplePageButtons = computed(() => {
    if (!props.pagination || props.pagination.last_page === undefined) {
        return [];
    }
    const buttons = [];
    for (let i = 1; i <= props.pagination.last_page; i++) {
        buttons.push(i);
    }
    return buttons;
});
</script>

<template>
    <div v-if="pagination && pagination.last_page >= 1"
        class="mt-6 mb-3 flex flex-col sm:flex-row justify-center items-center gap-4">
        <button type="button" @click.prevent.stop="goToPreviousPage"
            :disabled="!pagination.prev_page_url || router.processing"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            :class="{
                'cursor-pointer': pagination.prev_page_url && !router.processing,
                'opacity-50 cursor-not-allowed': !pagination.prev_page_url || router.processing
            }">
            &laquo; Previous
        </button>

        <div class="flex items-center gap-2">
            <template v-if="shouldShowDropdown">
                <label for="page-select" class="sr-only">Select page</label>
                <select id="page-select" v-model="selectedPage"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                    <option v-for="page in pageNumbersForDropdown" :key="page" :value="page">
                        Page {{ page }} / {{ pagination.last_page }}
                    </option>
                </select>
            </template>
            <template v-else>
                <button v-for="page in simplePageButtons" :key="page" type="button" @click.prevent.stop="goToPage(page)"
                    :class="[
                        'px-4 py-2 border rounded-md shadow-sm text-sm font-medium cursor-pointer',
                        page === pagination.current_page
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                        router.processing ? 'opacity-50 cursor-not-allowed' : ''
                    ]" :disabled="router.processing">
                    {{ page }}
                </button>
            </template>
        </div>

        <button type="button" @click.prevent.stop="goToNextPage"
            :disabled="!pagination.next_page_url || router.processing"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            :class="{
                'cursor-pointer': pagination.next_page_url && !router.processing,
                'opacity-50 cursor-not-allowed': !pagination.next_page_url || router.processing
            }">
            Next &raquo;
        </button>
    </div>
</template>