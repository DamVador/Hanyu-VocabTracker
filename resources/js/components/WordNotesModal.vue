<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { X } from 'lucide-vue-next';

const emit = defineEmits(['close', 'saveNotes']);

const props = defineProps({
    isVisible: {
        type: Boolean,
        default: false,
    },
    initialNotes: {
        type: String,
        default: '',
    },
});

const currentNotes = ref('');
const textareaRef = ref<HTMLTextAreaElement | null>(null);

watch(() => props.isVisible, (newVal) => {
    if (newVal) {
        currentNotes.value = props.initialNotes;
        nextTick(() => {
            adjustTextareaHeight();
        });
    }
});

watch(() => props.initialNotes, (newVal) => {
    if (props.isVisible) {
        currentNotes.value = newVal;
        nextTick(() => {
            adjustTextareaHeight();
        });
    }
});

const adjustTextareaHeight = () => {
    if (textareaRef.value) {
        textareaRef.value.style.height = 'auto';
        textareaRef.value.style.height = textareaRef.value.scrollHeight + 'px';
    }
};

const handleSaveAndClose = () => {
    emit('saveNotes', currentNotes.value);
    emit('close');
};

const handleCancelAndClose = () => {
    emit('close');
};
</script>

<template>
    <div v-show="isVisible" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 p-4 text-black">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Word Notes & Mnemonics</h3>
                <button @click="handleCancelAndClose" class="text-gray-500 hover:text-gray-700">
                    <X class="h-6 w-6" />
                </button>
            </div>
            <div class="p-4">
                <textarea
                    ref="textareaRef"
                    v-model="currentNotes"
                    @input="adjustTextareaHeight"
                    class="w-full p-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 min-h-[100px] max-h-[400px] resize-y overflow-y-auto"
                    placeholder="Enter your notes, examples, or mnemonics here..."
                ></textarea>
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-end gap-2">
                <button @click="handleCancelAndClose" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </button>
                <button @click="handleSaveAndClose" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Save & Close
                </button>
            </div>
        </div>
    </div>
</template>