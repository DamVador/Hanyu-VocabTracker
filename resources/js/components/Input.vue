<script setup lang="ts">
import { onMounted, ref, useAttrs } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    autofocus: Boolean,
});

const emit = defineEmits(['update:modelValue']);

const input = ref<HTMLInputElement | null>(null);

const attrs = useAttrs();

onMounted(() => {
    if (input.value && props.autofocus) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        v-bind="attrs"
        class="
            bg-gray-50
            border border-gray-300 hover:border-gray-400
            rounded-md shadow-sm
            text-gray-900 placeholder-gray-400
            w-full px-3 py-1 h-9 text-base md:text-sm
            outline-none
            transition-colors duration-200 ease-in-out 
            focus:border-gray-400
            focus:ring-1 focus:ring-gray-400
        "
        :value="modelValue"
        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        ref="input"
    />
</template>