<template>
    <div class="barcode-quick-add mb-4">
        <div class="card border-dashed border-primary">
            <div class="card-header bg-light py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-barcode me-2"></i>
                        Barcode Quick Add
                    </h6>
                    <button
                        @click="toggleScanner"
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                    >
                        <i :class="scannerActive ? 'fas fa-times' : 'fas fa-barcode'"></i>
                        {{ scannerActive ? 'Close Scanner' : 'Open Scanner' }}
                    </button>
                </div>
            </div>
            <div v-if="scannerActive" class="card-body py-3">
                <BarcodeScanner
                    @product-found="onProductFound"
                    @scan-error="onScanError"
                    placeholder="Scan or type barcode to quickly add products..."
                    :auto-focus="true"
                />

                <!-- Quick add controls -->
                <div v-if="scannedProduct" class="mt-3 p-3 bg-light rounded">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Product</label>
                            <div class="fw-bold">{{ scannedProduct.name }}</div>
                            <small class="text-muted">{{ scannedProduct.sku }}</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity</label>
                            <input
                                v-model.number="quickAddQuantity"
                                type="number"
                                class="form-control"
                                min="1"
                                step="0.01"
                                @keydown.enter="addToItems"
                            />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Rate</label>
                            <input
                                v-model.number="quickAddRate"
                                type="number"
                                class="form-control"
                                min="0"
                                step="0.01"
                                @keydown.enter="addToItems"
                            />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Amount</label>
                            <div class="form-control-plaintext fw-bold">
                                {{ quickAddAmount.toFixed(2) }}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button
                                @click="addToItems"
                                type="button"
                                class="btn btn-primary w-100"
                                :disabled="!canAddItem"
                            >
                                <i class="fas fa-plus"></i>
                                Add Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import emitter from '@/core/plugins/mitt';
import { $toastSuccess, $toastError } from '@/core/helpers/utility';

const props = defineProps({
    itemsField: {
        type: String,
        default: 'items'
    },
    warehouseField: {
        type: String,
        default: 'warehouse'
    },
    entry: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['item-added']);

const scannerActive = ref(false);
const scannedProduct = ref(null);
const quickAddQuantity = ref(1);
const quickAddRate = ref(0);

const quickAddAmount = computed(() => {
    return (quickAddQuantity.value || 0) * (quickAddRate.value || 0);
});

const canAddItem = computed(() => {
    return scannedProduct.value &&
           quickAddQuantity.value > 0 &&
           quickAddRate.value > 0;
});

const toggleScanner = () => {
    scannerActive.value = !scannerActive.value;
    if (!scannerActive.value) {
        resetScannedProduct();
    }
};

const onProductFound = (product) => {
    scannedProduct.value = product;
    quickAddRate.value = product.rate || 0;

    // Focus quantity field for quick entry
    setTimeout(() => {
        const quantityInput = document.querySelector('.barcode-quick-add input[type="number"]');
        if (quantityInput) {
            quantityInput.focus();
            quantityInput.select();
        }
    }, 100);
};

const onScanError = (error) => {
    scannedProduct.value = null;
    $toastError('Product not found or scan error');
};

const addToItems = () => {
    if (!canAddItem.value) return;

    const newItem = {
        id: null,
        product_id: scannedProduct.value.id,
        product: scannedProduct.value,
        unit: scannedProduct.value.unit ? {
            id: scannedProduct.value.unit.id,
            name: scannedProduct.value.unit.name
        } : {},
        rate: quickAddRate.value,
        quantity: quickAddQuantity.value,
        amount: quickAddAmount.value,
        // Add shelf if warehouse is selected
        shelf: getDefaultShelf()
    };

    // Add item to the entry's items array
    if (!props.entry[props.itemsField]) {
        props.entry[props.itemsField] = [];
    }

    props.entry[props.itemsField].push(newItem);

    // Emit events to trigger form updates
    emitter.emit('onProductChange', {
        item: newItem,
        items: props.entry[props.itemsField]
    });

    emit('item-added', newItem);

    $toastSuccess(`Added ${scannedProduct.value.name} to items`);

    // Reset for next scan
    resetScannedProduct();
    quickAddQuantity.value = 1;
};

const getDefaultShelf = () => {
    // If warehouse is selected, try to get first available shelf
    const warehouse = props.entry[props.warehouseField];
    if (warehouse && warehouse.shelves && warehouse.shelves.length > 0) {
        return {
            id: warehouse.shelves[0].id,
            name: warehouse.shelves[0].name
        };
    }
    return null;
};

const resetScannedProduct = () => {
    scannedProduct.value = null;
    quickAddRate.value = 0;
};
</script>

<style scoped>
.border-dashed {
    border-style: dashed !important;
}

.card-header {
    border-bottom: 1px dashed #e1e5e9;
}

.barcode-quick-add .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>