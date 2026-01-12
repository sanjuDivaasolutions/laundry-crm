<template>
    <div class="barcode-scanner">
        <div class="input-group">
            <input
                ref="barcodeInput"
                v-model="scannedBarcode"
                @keydown.enter="handleScan"
                @input="handleInput"
                class="form-control"
                :placeholder="placeholder"
                autocomplete="off"
                :disabled="loading"
            />
            <button
                @click="toggleCamera"
                class="btn btn-outline-secondary"
                type="button"
                :disabled="loading"
            >
                <i :class="cameraActive ? 'fas fa-camera-slash' : 'fas fa-camera'"></i>
            </button>
        </div>

        <!-- Camera Scanner -->
        <div v-if="cameraActive" class="camera-container mt-3">
            <div ref="cameraElement" class="camera-view"></div>
            <button @click="stopCamera" class="btn btn-sm btn-secondary mt-2">
                Stop Camera
            </button>
        </div>

        <!-- Scan Result -->
        <div v-if="lastScannedProduct" class="scan-result mt-2 p-2 bg-light rounded">
            <small class="text-success">
                <i class="fas fa-check"></i>
                Found: {{ lastScannedProduct.name }} ({{ lastScannedProduct.sku }})
            </small>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="error-message mt-2 p-2 bg-danger text-white rounded">
            <small><i class="fas fa-exclamation-triangle"></i> {{ errorMessage }}</small>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import ApiService from '@/core/services/ApiService';

const props = defineProps({
    placeholder: {
        type: String,
        default: 'Scan or enter barcode...'
    },
    autoFocus: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['product-found', 'scan-error']);

const scannedBarcode = ref('');
const loading = ref(false);
const cameraActive = ref(false);
const lastScannedProduct = ref(null);
const errorMessage = ref('');
const barcodeInput = ref(null);
const cameraElement = ref(null);

let scanTimeout = null;
let quaggaInitialized = false;

const handleInput = () => {
    // Clear previous messages
    errorMessage.value = '';
    lastScannedProduct.value = null;

    // Debounce scanning for manual input
    clearTimeout(scanTimeout);
    scanTimeout = setTimeout(() => {
        if (scannedBarcode.value.length >= 8) {
            handleScan();
        }
    }, 500);
};

const handleScan = async () => {
    if (!scannedBarcode.value.trim() || loading.value) return;

    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await ApiService.get(`/v1/products/barcode/${scannedBarcode.value.trim()}`);
        lastScannedProduct.value = response.data;
        emit('product-found', response.data);

        // Play success sound
        playSound('success');

        // Clear input after successful scan
        setTimeout(() => {
            scannedBarcode.value = '';
            focusInput();
        }, 1000);

    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Product not found';
        emit('scan-error', error);

        // Play error sound
        playSound('error');

        // Clear input after error
        setTimeout(() => {
            scannedBarcode.value = '';
            focusInput();
        }, 2000);
    } finally {
        loading.value = false;
    }
};

const toggleCamera = async () => {
    if (cameraActive.value) {
        stopCamera();
    } else {
        await startCamera();
    }
};

const startCamera = async () => {
    try {
        // Dynamic import of Quagga to avoid loading if not needed
        const Quagga = await import('quagga');

        cameraActive.value = true;
        await nextTick();

        Quagga.default.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: cameraElement.value,
                constraints: {
                    width: 320,
                    height: 240,
                    facingMode: "environment"
                }
            },
            decoder: {
                readers: [
                    "code_128_reader",
                    "code_39_reader",
                    "ean_reader",
                    "ean_8_reader"
                ]
            }
        }, (err) => {
            if (err) {
                console.error('Quagga initialization failed:', err);
                errorMessage.value = 'Camera initialization failed';
                cameraActive.value = false;
                return;
            }
            Quagga.default.start();
            quaggaInitialized = true;
        });

        Quagga.default.onDetected((data) => {
            scannedBarcode.value = data.codeResult.code;
            handleScan();
            stopCamera();
        });

    } catch (error) {
        console.error('Camera start error:', error);
        errorMessage.value = 'Camera access denied or Quagga library not available';
        cameraActive.value = false;
    }
};

const stopCamera = async () => {
    if (cameraActive.value && quaggaInitialized) {
        try {
            const Quagga = await import('quagga');
            Quagga.default.stop();
            quaggaInitialized = false;
        } catch (error) {
            console.error('Error stopping camera:', error);
        }
        cameraActive.value = false;
    }
};

const focusInput = () => {
    if (barcodeInput.value) {
        barcodeInput.value.focus();
    }
};

const playSound = (type) => {
    // Create audio context for feedback
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.value = type === 'success' ? 800 : 400;
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
    } catch (error) {
        // Silently fail if audio context not available
    }
};

onMounted(() => {
    if (props.autoFocus) {
        nextTick(() => focusInput());
    }
});

onUnmounted(() => {
    stopCamera();
    clearTimeout(scanTimeout);
});

// Expose methods for parent component
defineExpose({
    focus: focusInput,
    clear: () => { scannedBarcode.value = ''; }
});
</script>

<style scoped>
.barcode-scanner {
    position: relative;
}

.camera-container {
    max-width: 320px;
}

.camera-view {
    border: 2px solid #007bff;
    border-radius: 4px;
    overflow: hidden;
    min-height: 240px;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.scan-result, .error-message {
    font-size: 0.875rem;
}

.input-group input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.error-message {
    animation: fadeIn 0.3s ease-in;
}

.scan-result {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .camera-container {
        max-width: 100%;
    }

    .camera-view {
        min-height: 200px;
    }
}
</style>