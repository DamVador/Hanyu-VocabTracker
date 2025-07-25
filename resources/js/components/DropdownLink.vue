<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        default: '#', // Default to a non-functional hash if href is not provided for buttons
    },
    as: {
        type: String,
        default: 'a', // 'a' for link, 'button' for button
    },
});

const componentTag = computed(() => {
    return props.as === 'button' ? 'button' : Link; // Use Inertia Link component for 'a' or actual <Link>
});
</script>

<template>
    <component
        :is="componentTag"
        :href="href"
        class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
    >
        <slot />
    </component>
</template>