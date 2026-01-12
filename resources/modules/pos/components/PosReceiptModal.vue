<template>
    <div class="modal-backdrop show" @click.self="close">
        <div class="modal show d-block">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Receipt</h5>
                        <button @click="close" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Sale Completed!</h4>
                        </div>
                        
                        <div v-if="posStore.lastTransaction" class="receipt-details">
                            <p><strong>Invoice:</strong> {{ posStore.lastTransaction.invoice_number }}</p>
                            <p><strong>Total:</strong> ${{ formatCurrency(posStore.lastTransaction.total) }}</p>
                            <p v-if="posStore.lastTransaction.change > 0">
                                <strong>Change:</strong> ${{ formatCurrency(posStore.lastTransaction.change) }}
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="print" class="btn btn-primary">
                            <i class="fas fa-print me-2"></i>Print Receipt
                        </button>
                        <button @click="close" class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePosStore } from '../posStore';

const posStore = usePosStore();

const close = () => {
    posStore.showReceipt = false;
};

const print = () => {
    window.print();
};

const formatCurrency = (amount) => {
    return Number(amount).toFixed(2);
};
</script>

<style lang="scss" scoped>
.modal-backdrop {
    background: rgba(0, 0, 0, 0.5);
}

.modal {
    overflow: auto;
}
</style>