<script setup lang="ts">
import { useAttrs } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean, Array, Object],
        default: '',
    },
    options: {
        type: Array as () => { value: string | number; label: string }[],
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Select an option',
    },
    multiple: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const attrs = useAttrs();
</script>

<template>
    <select
        v-bind="attrs"
        :value="modelValue"
        @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        class="
            bg-gray-50
            border border-gray-300 hover:border-gray-400
            rounded-md shadow-sm
            text-gray-900 placeholder-gray-400 // Placeholder here mainly for consistency with input styles
            w-full px-3 py-1 h-9 text-base md:text-sm
            outline-none
            transition-colors duration-200 ease-in-out
            focus:border-gray-400
            focus:ring-1 focus:ring-gray-400
        "
    >
        <option value="" disabled selected>{{ placeholder }}</option>
        <option v-for="(option, index) in options" :key="index" :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>