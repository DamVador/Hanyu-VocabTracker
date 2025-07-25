<script setup lang="ts">
import { Delete } from 'lucide-vue-next';

const emit = defineEmits(['insert', 'delete']);

const pinyinKeys = [
    ['a', 'ā', 'á', 'ǎ', 'à', 'e', 'ē', 'é', 'ě', 'è' ],
    ['i', 'ī', 'í', 'ǐ', 'ì','o', 'ō', 'ó', 'ǒ', 'ò'],
    ['u', 'ū', 'ú', 'ǔ', 'ù', 'v', 'ü', 'ǖ', 'ǘ', 'ǚ', 'ǜ'],
    ['n', 'l', 'g', 'k', 'h', 'b', 'p', 'm', 'f', 'd', 't'],
    ['j', 'q', 'x', 'z', 'c', 's', 'zh', 'ch', 'sh', 'r', 'y', 'w'],
    [' ', '.', ',', '?', 'DELETE_KEY']
];

const insertChar = (char: string) => {
    emit('insert', char);
};

const deleteChar = () => {
    emit('delete');
};
</script>

<template>
    <div class="bg-gray-50 p-4 rounded-lg shadow-inner border border-gray-200">
        <div v-for="(row, rowIndex) in pinyinKeys" :key="rowIndex" class="flex flex-wrap justify-center gap-1 mb-2">
            <template v-for="(char, charIndex) in row" :key="`${rowIndex}-${charIndex}`">
                <button
                    type="button"  tabindex="-1" v-if="char !== 'DELETE_KEY'"
                    @click="insertChar(char)"
                    :class="[
                        'py-1 bg-white border border-gray-300 rounded-md text-sm font-semibold text-gray-800 hover:bg-gray-100 active:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-150',
                        char === ' ' ? 'w-2/5' : 'w-[calc(8.333%-4px)]',
                        'flex-grow'
                    ]"
                >
                    {{ char }}
                </button>
                <button
                    type="button"  tabindex="-1" v-else-if="char === 'DELETE_KEY'"
                    @click="deleteChar"
                    class="py-1 bg-red-100 border border-red-300 rounded-md text-sm font-semibold text-red-700 hover:bg-red-200 active:bg-red-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-150 flex items-center justify-center w-[calc(8.333%-4px)] flex-grow"
                    aria-label="Delete"
                >
                    <Delete class="h-4 w-4" />
                </button>
            </template>
        </div>
    </div>
</template>

<style scoped>
/* Aucun style spécifique supplémentaire n'est généralement nécessaire avec Tailwind */
</style>