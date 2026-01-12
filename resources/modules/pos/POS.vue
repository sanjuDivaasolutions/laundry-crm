<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 30/11/25, 12:00 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="pos-container d-flex flex-column h-100">
        <!-- Header -->
        <PosHeader @toggle-orders="posStore.toggleOrders()" />

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex overflow-hidden">
            <!-- Left Panel - Products or Orders -->
            <div class="pos-products-panel flex-grow-1 d-flex flex-column">
                <!-- Orders List View -->
                <template v-if="posStore.showOrders">
                    <PosOrdersList @close="posStore.showOrders = false" />
                </template>

                <!-- Products View -->
                <template v-else>
                    <!-- Search and Filters -->
                    <PosSearchBar />

                    <!-- Quick Category Buttons -->
                    <PosQuickCategories v-if="!posStore.loadingProducts" />

                    <!-- Products Grid -->
                    <PosProductsGrid />
                </template>
            </div>

            <!-- Right Panel - Cart -->
            <div class="pos-cart-panel">
                <!-- Customer Selection -->
                <PosCustomerSelect />

                <!-- Cart Items -->
                <PosCart />

                <!-- Payment Section -->
                <PosPayment />
            </div>
        </div>

        <!-- Modals -->
        <PosReceiptModal v-if="posStore.showReceipt" />
        <PosBarcodeScanner v-if="posStore.scannerActive" />

        <!-- Toast Notifications -->
        <PosNotifications />
    </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { usePosStore } from './posStore';
import PosHeader from './components/PosHeader.vue';
import PosSearchBar from './components/PosSearchBar.vue';
import PosQuickCategories from './components/PosQuickCategories.vue';
import PosProductsGrid from './components/PosProductsGrid.vue';
import PosCustomerSelect from './components/PosCustomerSelect.vue';
import PosCart from './components/PosCart.vue';
import PosPayment from './components/PosPayment.vue';
import PosReceiptModal from './components/PosReceiptModal.vue';
import PosBarcodeScanner from './components/PosBarcodeScanner.vue';
import PosNotifications from './components/PosNotifications.vue';
import PosOrdersList from './components/PosOrdersList.vue';

const posStore = usePosStore();

// Keyboard shortcuts
const handleKeyboard = (e) => {
    // F1 - Focus search
    if (e.key === 'F1') {
        e.preventDefault();
        document.querySelector('#pos-search-input')?.focus();
    }
    // F2 - Toggle scanner
    else if (e.key === 'F2') {
        e.preventDefault();
        posStore.toggleScanner();
    }
    // F3 - Toggle orders
    else if (e.key === 'F3') {
        e.preventDefault();
        posStore.toggleOrders();
    }
    // F8 - Clear cart
    else if (e.key === 'F8') {
        e.preventDefault();
        if (confirm('Clear cart?')) {
            posStore.clearCart();
        }
    }
    // F10 - Process payment
    else if (e.key === 'F10') {
        e.preventDefault();
        if (posStore.canProcessPayment) {
            posStore.processSale();
        }
    }
};

onMounted(async () => {
    // Load products, categories, and payment modes (customers are searched on-demand)
    await Promise.all([
        posStore.loadAllProducts(),
        posStore.loadCategories(),
        posStore.loadPaymentModes()
    ]);

    // Add keyboard listener
    window.addEventListener('keydown', handleKeyboard);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyboard);
});
</script>

<style lang="scss" scoped>
.pos-container {
    height: 100vh;
    background: #f5f8fa;
    
    .pos-products-panel {
        padding: 0.75rem;
        background: white;
        border-right: 1px solid #e4e6ef;
    }

    .pos-cart-panel {
        width: 420px;
        display: flex;
        flex-direction: column;
        background: #f5f8fa;
        padding: 0.75rem;
    }
}

@media (max-width: 1200px) {
    .pos-container {
        .pos-cart-panel {
            width: 380px;
        }
    }
}

@media (max-width: 768px) {
    .pos-container {
        .pos-products-panel,
        .pos-cart-panel {
            width: 100% !important;
        }
        
        .flex-grow-1.d-flex {
            flex-direction: column;
        }
    }
}
</style>
