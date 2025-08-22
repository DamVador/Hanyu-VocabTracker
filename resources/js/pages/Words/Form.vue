<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import Input from '@/components/Input.vue';
import { Keyboard } from 'lucide-vue-next';
import PinyinKeyboard from '@/components/PinyinKeyboard.vue';
import { ref, watch, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    form: Object,
    allTags: Array,
    userStudySessions: Array,
    isEditMode: {
        type: Boolean,
        default: false,
    },
});

const currentTagInput = ref('');
const filteredTags = ref([]);
const showTagDropdown = ref(false);

watch(currentTagInput, (newInput) => {
    if (newInput.length > 0) {
        filteredTags.value = props.allTags.filter(tag =>
            tag.toLowerCase().includes(newInput.toLowerCase())
        ).filter(tag => !props.form.tags.includes(tag));
        showTagDropdown.value = true;
    } else {
        filteredTags.value = [];
        showTagDropdown.value = false;
    }
});

const addTag = (tag: string) => {
    if (tag && !props.form.tags.includes(tag)) {
        props.form.tags.push(tag);
    }
    currentTagInput.value = '';
    showTagDropdown.value = false;
};

const removeTag = (tag: string) => {
    props.form.tags = props.form.tags.filter((t: string) => t !== tag);
};

const showPinyinKeyboard = ref(false);

const insertPinyinChar = (char: string) => {
    const pinyinInput = document.getElementById('pinyin_input') as HTMLInputElement;
    if (!pinyinInput) return;

    const start = pinyinInput.selectionStart ?? 0;
    const end = pinyinInput.selectionEnd ?? 0;

    props.form.pinyin = props.form.pinyin.substring(0, start) + char + props.form.pinyin.substring(end);

    nextTick(() => {
        pinyinInput.setSelectionRange(start + char.length, start + char.length);
        pinyinInput.focus();
    });
};

const deletePinyinChar = () => {
    const pinyinInput = document.getElementById('pinyin_input') as HTMLInputElement;
    if (!pinyinInput) return;

    const start = pinyinInput.selectionStart ?? 0;
    const end = pinyinInput.selectionEnd ?? 0;

    if (start === end && start > 0) {
        props.form.pinyin = props.form.pinyin.substring(0, start - 1) + props.form.pinyin.substring(end);
        nextTick(() => {
            pinyinInput.setSelectionRange(start - 1, start - 1);
            pinyinInput.focus();
        });
    } else if (start !== end) {
        props.form.pinyin = props.form.pinyin.substring(0, start) + props.form.pinyin.substring(end);
        nextTick(() => {
            pinyinInput.setSelectionRange(start, start);
            pinyinInput.focus();
        });
    }
};

const notesTextareaRef = ref<HTMLTextAreaElement | null>(null);

watch(() => props.form.notes, () => {
    nextTick(() => {
        adjustNotesTextareaHeight();
    });
});

nextTick(() => {
    adjustNotesTextareaHeight();
});

const adjustNotesTextareaHeight = () => {
    if (notesTextareaRef.value) {
        notesTextareaRef.value.style.height = 'auto';
        notesTextareaRef.value.style.height = notesTextareaRef.value.scrollHeight + 'px';
    }
};

const toggleStudySession = (sessionId: number, event: Event) => {
    const isChecked = (event.target as HTMLInputElement).checked;
    
    if (!Array.isArray(props.form.study_session_ids)) {
        props.form.study_session_ids = [];
    }

    if (isChecked) {
        if (!props.form.study_session_ids.includes(sessionId)) {
            props.form.study_session_ids.push(sessionId);
        }
    } else {
        props.form.study_session_ids = props.form.study_session_ids.filter((id) => id !== sessionId);
    }
};

</script>

<template>
    <div>
        <div>
            <InputLabel for="chinese_word" value="Chinese Word" />
            <Input id="chinese_word" type="text" class="mt-1 block w-full" v-model="props.form.chinese_word" required
                autofocus />
            <InputError class="mt-2" :message="props.form.errors.chinese_word" />
        </div>

        <div class="mt-4">
            <InputLabel for="pinyin_input" value="Pinyin" />
            <div class="relative flex items-center">
                <Input id="pinyin_input" type="text" class="mt-1 block w-full pr-10" v-model="props.form.pinyin"
                    required />
                <button type="button" @click="showPinyinKeyboard = !showPinyinKeyboard"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    aria-label="Open Pinyin Keyboard">
                    <Keyboard class="h-5 w-5" />
                </button>
            </div>
            <InputError class="mt-2" :message="props.form.errors.pinyin" />

            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <PinyinKeyboard v-if="showPinyinKeyboard" @insert="insertPinyinChar" @delete="deletePinyinChar"
                    class="mt-4" />
            </Transition>
        </div>

        <div class="mt-4">
            <InputLabel for="translation" value="Translation" />
            <Input id="translation" type="text" class="mt-1 block w-full" v-model="props.form.translation" required />
            <InputError class="mt-2" :message="props.form.errors.translation" />
        </div>

        <div class="mt-4">
            <InputLabel for="notes" value="Notes & Mnemonics" />
            <textarea id="notes" ref="notesTextareaRef" v-model="props.form.notes" @input="adjustNotesTextareaHeight"
                class="
                    w-full transition-colors bg-gray-50 
                    focus:ring-1 duration-200 ease-in-out 
                    p-3 border border-gray-300 text-base 
                    md:text-sm hover:border-gray-400 
                    shadow-sm outline-none rounded-md 
                    text-gray-900 placeholder-gray-400 
                    focus:ring-gray-400 focus:border-gray-400 
                    min-h-[100px] max-h-[400px] resize-y overflow-y-auto mt-1 block
                "
                placeholder="Enter your notes, examples, or mnemonics here...">
            </textarea>
            <InputError class="mt-2" :message="props.form.errors.notes" />
        </div>

        <div class="mt-4">
            <InputLabel for="tags" value="Tags (type and press Enter, or select from dropdown)" />
            <Input id="tags" type="text" class="mt-1 block w-full" v-model="currentTagInput"
                @keydown.enter.prevent="addTag(currentTagInput)" />
            <InputError class="mt-2" :message="props.form.errors.tags" />

            <div v-if="showTagDropdown && filteredTags.length > 0"
                class="mt-1 border border-gray-300 rounded-md shadow-lg bg-white max-h-40 overflow-y-auto">
                <div v-for="tag in filteredTags" :key="tag" @click="addTag(tag)"
                    class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                    {{ tag }}
                </div>
            </div>

            <div class="mt-2 flex flex-wrap gap-2">
                <span v-for="tag in props.form.tags" :key="tag"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    {{ tag }}
                    <button type="button" @click="removeTag(tag)"
                        class="flex-shrink-0 ml-1.5 h-3 w-3 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none focus:bg-indigo-500 focus:text-white">
                        <span class="sr-only">Remove tag</span>
                        <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                            <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                        </svg>
                    </button>
                </span>
            </div>
        </div>

        <div class="mt-6">
            <InputLabel :value="isEditMode ? 'Belongs to Study Sessions' : 'Add to Study Sessions'" />
            <p class="text-sm text-gray-600 mb-2">Select one or more sessions this word belongs to:</p>
            <div class="border rounded-md p-4 max-h-48 overflow-y-auto bg-gray-50">
                <div v-if="props.userStudySessions?.length === 0" class="text-gray-500 italic">
                    You haven't created any study sessions yet.
                    <Link :href="route('study-sessions.create')" class="text-blue-600 hover:underline">
                    Create one</Link>!
                </div>
                <div v-for="session in props.userStudySessions" :key="session.id" class="flex items-center mb-2">
                    <input type="checkbox" :id="`session-${session.id}`" :value="session.id"
                        :checked="props.form.study_session_ids.includes(session.id)"
                        @change="toggleStudySession(session.id, $event)"
                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" />
                    <label :for="`session-${session.id}`" class="ml-2 text-sm text-gray-900 cursor-pointer">
                        {{ session.name }}
                    </label>
                </div>
            </div>
            <InputError class="mt-2" :message="props.form.errors.study_session_ids" />
        </div>
    </div>
</template>