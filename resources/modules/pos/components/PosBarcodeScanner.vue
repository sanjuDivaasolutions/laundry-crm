<template>
    <div class="barcode-scanner-overlay">
        <div class="scanner-container">
            <div class="scanner-header">
                <h5>Barcode Scanner</h5>
                <button @click="close" class="btn btn-danger">
                    <i class="fas fa-times me-2"></i>Stop Scanner
                </button>
            </div>
            <div class="scanner-body">
                <div class="scanner-view">
                    <div class="scanner-line"></div>
                    <p class="text-center text-white mt-3">
                        Point camera at barcode to scan
                    </p>
                </div>
                
                <!-- Manual Input -->
                <div class="manual-input mt-3">
                    <input 
                        type="text" 
                        v-model="barcodeInput"
                        @keyup.enter="scanBarcode"
                        class="form-control"
                        placeholder="Or type barcode manually..."
                        ref="barcodeInputRef"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePosStore } from '../posStore';

const posStore = usePosStore();
const barcodeInput = ref('');
const barcodeInputRef = ref(null);

const close = () => {
    posStore.toggleScanner();
};

const scanBarcode = async () => {
    if (barcodeInput.value) {
        const product = await posStore.searchByBarcode(barcodeInput.value);
        if (product) {
            posStore.addToCart(product);
            close();
        }
        barcodeInput.value = '';
    }
};

onMounted(() => {
    // Focus on input
    barcodeInputRef.value?.focus();
});
</script>

<style lang="scss" scoped>
.barcode-scanner-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    
    .scanner-container {
        width: 90%;
        max-width: 600px;
        
        .scanner-header {
            background: white;
            padding: 1rem;
            border-radius: 0.5rem 0.5rem 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .scanner-body {
            background: black;
            padding: 2rem;
            border-radius: 0 0 0.5rem 0.5rem;
            
            .scanner-view {
                background: #222;
                height: 300px;
                border-radius: 0.5rem;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                
                .scanner-line {
                    position: absolute;
                    width: 80%;
                    height: 2px;
                    background: red;
                    animation: scan 2s linear infinite;
                }
            }
        }
    }
}

@keyframes scan {
    0% { top: 10%; }
    50% { top: 90%; }
    100% { top: 10%; }
}
</style>