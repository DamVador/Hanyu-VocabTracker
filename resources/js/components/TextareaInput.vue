<template>
    <textarea
        class="
            bg-gray-50
            border border-gray-300 hover:border-gray-400
            rounded-md shadow-sm
            text-gray-900 placeholder-gray-400
            w-full px-3 py-2 text-base md:text-sm // Note: py-2 pour les textarea pour un meilleur padding vertical
            outline-none
            transition-colors duration-200 ease-in-out
            focus:border-gray-400
            focus:ring-1 focus:ring-gray-400
        "
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        ref="textarea"
    ></textarea>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';

defineProps(['modelValue']);

defineEmits(['update:modelValue']);

const textarea = ref(null);

onMounted(() => {
    if (textarea.value.hasAttribute('autofocus')) {
        textarea.value.focus();
    }
});

defineExpose({ focus: () => textarea.value.focus() });
</script>