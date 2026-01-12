<template>
    <div class="barcode-field">
        <div class="input-group">
            <input
                :id="field.name"
                :value="modelValue"
                :name="field.name"
                :placeholder="field.placeholder"
                :required="field.required"
                :disabled="field.disabled || loading"
                class="form-control"
                autocomplete="off"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <button
                @click="generateBarcode"
                type="button"
                class="btn btn-outline-primary"
                :disabled="loading"
                title="Generate Unique Barcode"
            >
                <i class="fas fa-magic" v-if="!loading"></i>
                <span class="spinner-border spinner-border-sm" v-else></span>
            </button>
            <button
                @click="validateBarcode"
                type="button"
                class="btn btn-outline-secondary"
                :disabled="!modelValue || loading"
                title="Validate Barcode"
            >
                <i class="fas fa-check" v-if="!validating"></i>
                <span class="spinner-border spinner-border-sm" v-else></span>
            </button>
        </div>

        <!-- Validation feedback -->
        <div v-if="validationMessage" class="mt-1">
            <small :class="validationClass">
                <i :class="validationIcon"></i>
                {{ validationMessage }}
            </small>
        </div>

        <!-- Barcode preview -->
        <div v-if="modelValue && barcodeType" class="mt-2">
            <!-- SVG Preview (loaded via API) -->
            <div v-if="barcodePreviewSvg && !barcodeError" class="barcode-preview-wrapper" v-html="barcodePreviewSvg"></div>
            <!-- Fallback display -->
            <div v-else class="barcode-preview-fallback">
                <div class="barcode-text">
                    <i class="fas fa-barcode me-2"></i>
                    {{ modelValue }}
                </div>
                <small class="text-muted">Barcode: {{ barcodeType.toUpperCase() }}</small>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import ApiService from '@/core/services/ApiService';
import { $catchResponse, $toastSuccess, $toastError } from '@/core/helpers/utility';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    field: {
        type: Object,
        required: true
    },
    entry: {
        type: Object,
        default: () => ({})
    }
});

const barcodeType = computed(() => {
    return props.entry?.barcode_type || 'code128';
});

const emit = defineEmits(['update:modelValue']);

const loading = ref(false);
const validating = ref(false);
const validationMessage = ref('');
const validationStatus = ref(null); // 'valid', 'invalid', null
const barcodeError = ref(false);
const barcodePreviewSvg = ref(null);

// Load barcode preview via API call
const loadBarcodePreview = async () => {
    if (!props.modelValue) {
        barcodePreviewSvg.value = null;
        return;
    }

    try {
        const response = await ApiService.get('/barcodes/generate', {
            params: {
                data: props.modelValue,
                type: barcodeType.value,
                format: 'svg'
            },
            responseType: 'text'
        });
        barcodePreviewSvg.value = response.data;
        barcodeError.value = false;
    } catch (error) {
        console.error('Failed to load barcode preview:', error);
        barcodeError.value = true;
        barcodePreviewSvg.value = null;
    }
};

const validationClass = computed(() => {
    switch (validationStatus.value) {
        case 'valid':
            return 'text-success';
        case 'invalid':
            return 'text-danger';
        default:
            return 'text-muted';
    }
});

const validationIcon = computed(() => {
    switch (validationStatus.value) {
        case 'valid':
            return 'fas fa-check-circle';
        case 'invalid':
            return 'fas fa-exclamation-circle';
        default:
            return 'fas fa-info-circle';
    }
});

const generateBarcode = async () => {
    loading.value = true;
    try {
        const response = await ApiService.post('/barcodes/generate-unique', {
            prefix: ''
        });
        emit('update:modelValue', response.data.barcode);
        $toastSuccess('Unique barcode generated successfully');
        validationStatus.value = 'valid';
        validationMessage.value = 'Generated unique barcode';
    } catch (error) {
        $catchResponse(error);
    } finally {
        loading.value = false;
    }
};

const validateBarcode = async () => {
    if (!props.modelValue) return;

    validating.value = true;
    try {
        const response = await ApiService.post('/barcodes/validate', {
            barcode: props.modelValue,
            type: barcodeType.value
        });

        if (response.data.valid) {
            validationStatus.value = 'valid';
            validationMessage.value = 'Valid barcode format';
        } else {
            validationStatus.value = 'invalid';
            validationMessage.value = 'Invalid barcode format';
        }
    } catch (error) {
        validationStatus.value = 'invalid';
        validationMessage.value = 'Validation failed';
        $catchResponse(error);
    } finally {
        validating.value = false;
    }
};
// Watch for changes in barcode value to reset validation and load preview
watch(() => props.modelValue, (newVal) => {
    if (validationStatus.value) {
        validationStatus.value = null;
        validationMessage.value = '';
    }
    // Reset barcode error and load new preview
    barcodeError.value = false;
    if (newVal) {
        loadBarcodePreview();
    } else {
        barcodePreviewSvg.value = null;
    }
}, { immediate: true });
</script>

<style scoped>
.barcode-field {
    position: relative;
}

.barcode-preview-wrapper {
    display: inline-block;
    border: 1px solid #e1e5e9;
    border-radius: 4px;
    padding: 8px;
    background: white;
}

.barcode-preview-wrapper :deep(svg) {
    max-width: 200px;
    height: auto;
    display: block;
}

.barcode-preview-fallback {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 12px 16px;
    border: 1px solid #e1e5e9;
    border-radius: 4px;
    background: #f8f9fa;
}

.barcode-preview-fallback .barcode-text {
    font-family: monospace;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    letter-spacing: 2px;
}

.input-group .btn {
    border-left: 0;
}

.input-group .btn:first-of-type {
    border-left: 1px solid #e1e5e9;
}
</style>