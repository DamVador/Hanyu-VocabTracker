<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { X, Eraser, Download } from 'lucide-vue-next';

const emit = defineEmits(['close', 'saveDrawing']);
const props = defineProps({
    initialDrawingDataUrl: {
        type: String,
        default: null,
    },
});

const canvasRef = ref<HTMLCanvasElement | null>(null);
let ctx: CanvasRenderingContext2D | null = null;
let isDrawing = false;
let currentDrawingData: string | null = null;

const setupCanvas = () => {
    if (!canvasRef.value) {
        console.error("Canvas element is not available.");
        return;
    }

    ctx = canvasRef.value.getContext('2d');
    if (!ctx) {
        console.error("Failed to get 2D context for canvas.");
        return;
    }

    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#333';
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height);

    if (currentDrawingData) {
        loadImageOnCanvas(currentDrawingData);
    } else if (props.initialDrawingDataUrl) {
        loadImageOnCanvas(props.initialDrawingDataUrl);
    }
};

onMounted(() => {
    setupCanvas();

    if (canvasRef.value) {
        canvasRef.value.addEventListener('mousedown', startDrawing);
        canvasRef.value.addEventListener('mousemove', draw);
        canvasRef.value.addEventListener('mouseup', stopDrawing);
        canvasRef.value.addEventListener('mouseout', stopDrawing);

        canvasRef.value.addEventListener('touchstart', startDrawing);
        canvasRef.value.addEventListener('touchmove', draw);
        canvasRef.value.addEventListener('touchend', stopDrawing);
        canvasRef.value.addEventListener('touchcancel', stopDrawing);
    }
});

watch(() => props.initialDrawingDataUrl, (newVal, oldVal) => {
    if (ctx && canvasRef.value) {
        if (newVal !== oldVal && newVal !== currentDrawingData) {
            if (newVal) {
                loadImageOnCanvas(newVal);
            } else {
                clearCanvas(false);
            }
        }
    }
}, { immediate: true });

onUnmounted(() => {
    if (canvasRef.value) {
        canvasRef.value.removeEventListener('mousedown', startDrawing);
        canvasRef.value.removeEventListener('mousemove', draw);
        canvasRef.value.removeEventListener('mouseup', stopDrawing);
        canvasRef.value.removeEventListener('mouseout', stopDrawing);

        canvasRef.value.removeEventListener('touchstart', startDrawing);
        canvasRef.value.removeEventListener('touchmove', draw);
        canvasRef.value.removeEventListener('touchend', stopDrawing);
        canvasRef.value.removeEventListener('touchcancel', stopDrawing);
    }
});

const getCoords = (e: MouseEvent | TouchEvent) => {
    const canvas = canvasRef.value;
    if (!canvas) return { offsetX: 0, offsetY: 0 };

    let clientX, clientY;
    if (e instanceof MouseEvent) {
        clientX = e.clientX;
        clientY = e.clientY;
    } else {
        clientX = e.touches[0].clientX;
        clientY = e.touches[0].clientY;
    }

    const rect = canvas.getBoundingClientRect();
    const offsetX = clientX - rect.left;
    const offsetY = clientY - rect.top;
    return { offsetX, offsetY };
};

const startDrawing = (e: MouseEvent | TouchEvent) => {
    if (!ctx) {
        console.warn("Canvas context is not initialized for drawing.");
        return;
    }
    isDrawing = true;
    const { offsetX, offsetY } = getCoords(e);
    ctx.beginPath();
    ctx.moveTo(offsetX, offsetY);
};

const draw = (e: MouseEvent | TouchEvent) => {
    if (!isDrawing || !ctx) return;
    e.preventDefault();
    const { offsetX, offsetY } = getCoords(e);
    ctx.lineTo(offsetX, offsetY);
    ctx.stroke();
    // OPTIONAL: Update currentDrawingData here if you want real-time preview in other places,
    // but don't emit to parent to avoid loop.
    // currentDrawingData = canvasRef.value!.toDataURL('image/png');
};

const stopDrawing = () => {
    isDrawing = false;
    ctx?.closePath();
};

const clearCanvas = (andSave: boolean = true) => {
    if (ctx && canvasRef.value) {
        ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height);
        currentDrawingData = canvasRef.value.toDataURL('image/png');
        if (andSave) {
            saveCurrentDrawing();
        }
    }
};

const downloadDrawing = () => {
    if (canvasRef.value) {
        const dataURL = canvasRef.value.toDataURL('image/png');
        const link = document.createElement('a');
        link.href = dataURL;
        link.download = `chinese_char_drawing_${Date.now()}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

const loadImageOnCanvas = (dataUrl: string) => {
    if (!ctx || !canvasRef.value) {
        console.warn("Canvas context not available to load image.");
        return;
    }

    const img = new Image();
    img.onload = () => {
        ctx!.clearRect(0, 0, canvasRef.value!.width, canvasRef.value!.height);
        ctx!.fillStyle = '#fff';
        ctx!.fillRect(0, 0, canvasRef.value!.width, canvasRef.value!.height);

        ctx!.drawImage(img, 0, 0, canvasRef.value!.width, canvasRef.value!.height);
        currentDrawingData = canvasRef.value!.toDataURL('image/png');
    };
    img.onerror = (error) => {
        console.error("Failed to load image on canvas:", error);
        clearCanvas(false);
    };
    img.src = dataUrl;
};

const saveCurrentDrawing = () => {
    if (canvasRef.value) {
        const dataURL = canvasRef.value.toDataURL('image/png');
        currentDrawingData = dataURL;
        emit('saveDrawing', dataURL);
    }
};

const closeModal = () => {
    saveCurrentDrawing();
    emit('close');
};
</script>

<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Draw Chinese Character</h3>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                    <X class="h-6 w-6" />
                </button>
            </div>
            <div class="p-4 flex flex-col items-center">
                <canvas ref="canvasRef" width="300" height="300"
                        class="border border-gray-300 bg-white touch-none"
                        style="touch-action: none;"></canvas>
                <div class="mt-4 flex gap-4">
                    <button @click="clearCanvas(true)" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                        <Eraser class="h-5 w-5 mr-2" /> Clear
                    </button>
                    <!-- <button @click="downloadDrawing" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        <Download class="h-5 w-5 mr-2" /> Save
                    </button> -->
                    <button @click="closeModal" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Done
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-4">Draw the character in the box above.</p>
            </div>
        </div>
    </div>
</template>