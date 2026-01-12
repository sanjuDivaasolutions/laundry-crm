<template>
    <div class="pos-payment">
        <!-- Order Summary Card -->
        <div class="card shadow-sm mb-3 order-summary">
            <div class="card-header bg-light py-2">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calculator me-2 text-primary"></i>Order Summary
                </h6>
            </div>
                <div class="card-body py-2">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span class="fw-bold">${{ formatCurrency(posStore.subtotal) }}</span>
                </div>
                <div class="summary-row">
                    <span>Tax ({{ posStore.taxRate }}%):</span>
                    <span>${{ formatCurrency(posStore.taxAmount) }}</span>
                </div>
                <div class="summary-row align-items-center">
                    <span>Discount:</span>
                    <div class="input-group input-group-sm discount-input">
                        <span class="input-group-text">$</span>
                        <input
                            type="number"
                            v-model.number="posStore.discount"
                            class="form-control text-end"
                            min="0"
                            :max="posStore.subtotal"
                            step="0.01"
                        />
                    </div>
                </div>
                <hr class="my-1">
                <div class="summary-row total-row">
                    <span>Total:</span>
                    <span class="text-primary">${{ formatCurrency(posStore.total) }}</span>
                </div>
            </div>
        </div>

        <!-- Payment Method Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light py-2">
                <h6 class="card-title mb-0">
                    <i class="fas fa-credit-card me-2 text-primary"></i>Payment Method
                </h6>
            </div>
            <div class="card-body py-3">
                <!-- Payment Mode Selection -->
                <div class="payment-modes-grid" v-if="posStore.paymentModes.length > 0">
                    <div
                        v-for="mode in posStore.paymentModes"
                        :key="mode.id"
                        class="payment-mode-item"
                        :class="{ 'active': posStore.selectedPaymentModeId === mode.id }"
                        @click="selectPaymentMode(mode.id)"
                    >
                        <i :class="getPaymentModeIcon(mode.name)" class="mode-icon"></i>
                        <span class="mode-name">{{ mode.name }}</span>
                    </div>
                </div>
                <div v-else class="text-center text-muted py-3">
                    <i class="fas fa-spinner fa-spin me-2"></i>Loading payment methods...
                </div>

                <!-- Cash Payment Options -->
                <div v-if="posStore.isCashPayment" class="cash-payment-section mt-3">
                    <label class="form-label fw-semibold">Amount Received</label>
                    <div class="input-group input-group-lg mb-2">
                        <span class="input-group-text">$</span>
                        <input
                            type="number"
                            v-model.number="posStore.amountReceived"
                            class="form-control amount-input"
                            :min="posStore.total"
                            step="0.01"
                            placeholder="0.00"
                        />
                    </div>

                    <!-- Quick Cash Buttons -->
                    <div class="quick-cash-grid">
                        <button
                            v-for="amount in quickCashAmounts"
                            :key="amount"
                            @click="setAmountReceived(amount)"
                            class="btn btn-outline-primary quick-cash-btn"
                            :class="{ 'active': posStore.amountReceived === amount }"
                        >
                            ${{ amount }}
                        </button>
                    </div>

                    <!-- Change Display -->
                    <div v-if="posStore.change > 0" class="change-display">
                        <i class="fas fa-coins me-2"></i>
                        Change: <strong>${{ formatCurrency(posStore.change) }}</strong>
                    </div>
                    <div v-else-if="posStore.amountReceived > 0 && posStore.amountReceived < posStore.total" class="change-display insufficient">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Insufficient: <strong>${{ formatCurrency(posStore.total - posStore.amountReceived) }}</strong> more needed
                    </div>
                </div>

                <!-- Card/Other Payment Options -->
                <div v-if="!posStore.isCashPayment && posStore.selectedPaymentModeId" class="other-payment-section mt-3">
                    <!-- Reference number removed per UI preference -->
                </div>

                <!-- Remarks (always visible) -->
                <div class="mt-3">
                    <label class="form-label">Remarks <small class="text-muted">(optional)</small></label>
                    <textarea
                        v-model="posStore.paymentRemarks"
                        class="form-control"
                        rows="2"
                        placeholder="Add any notes about this sale..."
                    ></textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons d-flex align-items-center">
            <button
                @click="processSale"
                class="btn btn-success btn-lg complete-sale-btn flex-grow-1"
                :disabled="!posStore.canProcessPayment || posStore.loading"
            >
                <span v-if="posStore.loading">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Processing...
                </span>
                <span v-else>
                    <i class="fas fa-check-circle me-2"></i>
                    Complete Sale (${{ formatCurrency(posStore.total) }})
                </span>
            </button>

            <button
                @click="printReceipt"
                class="btn btn-info btn-lg print-btn ms-3"
                :disabled="!posStore.lastTransaction"
            >
                <i class="fas fa-print me-1"></i>Print
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { usePosStore } from '../posStore';

const posStore = usePosStore();

// Load payment modes on mount
onMounted(async () => {
    if (posStore.paymentModes.length === 0) {
        await posStore.loadPaymentModes();
    }
});

const quickCashAmounts = computed(() => {
    const total = posStore.total;
    if (total <= 0) return [5, 10, 20, 50];

    const base = Math.ceil(total);
    const amounts = [base];

    // Add convenient round amounts
    [5, 10, 20, 50, 100].forEach(increment => {
        const rounded = Math.ceil(total / increment) * increment;
        if (rounded > base && !amounts.includes(rounded)) {
            amounts.push(rounded);
        }
    });

    return amounts.slice(0, 4).sort((a, b) => a - b);
});

const selectPaymentMode = (modeId) => {
    posStore.selectedPaymentModeId = modeId;
    // Reset cash-specific fields when switching modes
    if (!posStore.isCashPayment) {
        posStore.amountReceived = posStore.total;
    }
};

const getPaymentModeIcon = (name) => {
    const lowerName = name.toLowerCase();
    if (lowerName.includes('cash')) return 'fas fa-money-bill-wave';
    if (lowerName.includes('card') || lowerName.includes('credit') || lowerName.includes('debit')) return 'fas fa-credit-card';
    if (lowerName.includes('bank') || lowerName.includes('transfer')) return 'fas fa-university';
    if (lowerName.includes('check') || lowerName.includes('cheque')) return 'fas fa-money-check';
    if (lowerName.includes('insurance')) return 'fas fa-shield-alt';
    if (lowerName.includes('mobile') || lowerName.includes('digital') || lowerName.includes('wallet')) return 'fas fa-mobile-alt';
    if (lowerName.includes('online') || lowerName.includes('internet')) return 'fas fa-globe';
    return 'fas fa-wallet';
};

const setAmountReceived = (amount) => {
    posStore.amountReceived = amount;
};

const processSale = async () => {
    await posStore.processSale();
};

// Hold sale removed from UI; function intentionally omitted

const printReceipt = () => {
    if (posStore.lastTransaction) {
        window.print();
    }
};

const formatCurrency = (amount) => {
    return Number(amount || 0).toFixed(2);
};
</script>

<style lang="scss" scoped>
.pos-payment {
    display: flex;
    flex-direction: column;
    height: 100%;
}

// Summary Rows
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
    font-size: 0.85rem;

    &.total-row {
        font-size: 1.15rem;
        font-weight: 600;
        padding-top: 0.5rem;
        border-top: 2px solid var(--bs-primary);
        margin-top: 0.5rem;
    }
}

.discount-input {
    width: 100px;

    .form-control {
        padding: 0.25rem 0.5rem;
    }
}

// Payment Modes Grid
.payment-modes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(64px, 1fr));
    gap: 0.25rem;
}

.payment-mode-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;

    &:hover {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), 0.05);
    }

    &.active {
        border-width: 2px;
        border-color: var(--bs-primary);
        background: #f1faff;
        color: var(--bs-primary);

        .mode-icon {
            color: var(--bs-primary);
        }
    }

    .mode-icon {
        font-size: 1.1rem;
        margin-bottom: 0.15rem;
        color: var(--bs-primary);
    }

    .mode-name {
        font-size: 0.7rem;
        font-weight: 600;
        text-align: center;
        line-height: 1.1;
    }
}

// Cash Payment Section
.cash-payment-section {
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.amount-input {
    font-size: 1.5rem !important;
    font-weight: 700;
    text-align: right;
}

.quick-cash-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.quick-cash-btn {
    padding: 0.5rem;
    font-weight: 600;
    font-size: 0.85rem;

    &.active {
        background: var(--bs-primary);
        color: white;
        border-color: var(--bs-primary);
    }
}

.change-display {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    text-align: center;
    font-size: 1.1rem;

    strong {
        font-size: 1.25rem;
    }

    &.insufficient {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
    }
}

// Other Payment Section
.other-payment-section {
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

/* Tighter Order Summary spacing */
.order-summary {
    .card-header {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid #e4e6ef;
    }

    .card-body {
        padding: 0.65rem 0.75rem;
    }

    .summary-row {
        padding: 0.3rem 0;
        font-size: 0.85rem;
    }
}

// Action Buttons
.action-buttons {
    margin-top: auto;
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;

    .complete-sale-btn {
        flex: 1 1 auto;
        min-width: 0;
    }

    .print-btn {
        min-width: 120px;
    }

    @media (max-width: 600px) {
        flex-direction: column;

        .print-btn {
            width: 100%;
        }
    }
}

.complete-sale-btn {
    padding: 0.875rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 10px;

    &:not(:disabled) {
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }
}

.secondary-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;

    .btn {
        padding: 0.625rem;
        font-weight: 500;
    }
}

// Card styling adjustments
.card-header {
    border-bottom: 1px solid #e9ecef;
}

.card-title {
    font-size: 0.95rem;
    font-weight: 600;
}
</style>
