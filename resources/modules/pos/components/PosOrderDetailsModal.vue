<template>
    <div class="modal-backdrop" @click.self="$emit('close')">
        <div class="order-details-modal">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="header-info">
                    <h5 class="modal-title mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Order Details
                    </h5>
                    <span class="badge bg-primary ms-2">{{ order.invoice_number }}</span>
                </div>
                <button @click="$emit('close')" class="btn-close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Loading State -->
                <div v-if="loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <template v-else>
                    <!-- Order Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted">Customer</small>
                                    <div class="fw-semibold">
                                        {{ order.customer?.name || 'Walk-in Customer' }}
                                    </div>
                                    <small v-if="order.customer?.phone" class="text-muted">
                                        {{ order.customer.phone }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted">Date & Time</small>
                                    <div class="fw-semibold">{{ order.date }}</div>
                                    <small class="text-muted">{{ order.time }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted">Cashier</small>
                                    <div class="fw-semibold">{{ order.cashier }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon" :class="getStatusIconClass(order.payment_status)">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="info-content">
                                    <small class="text-muted">Payment Status</small>
                                    <div>
                                        <span
                                            class="badge"
                                            :class="getStatusBadgeClass(order.payment_status)"
                                        >
                                            {{ formatStatus(order.payment_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="items-section mb-4">
                        <h6 class="section-title">
                            <i class="fas fa-shopping-basket me-2"></i>Order Items
                        </h6>
                        <div class="items-table-wrapper">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center" style="width: 80px;">Qty</th>
                                        <th class="text-end" style="width: 100px;">Price</th>
                                        <th class="text-end" style="width: 100px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in order.items" :key="item.id">
                                        <td>
                                            <div class="product-name">{{ item.product_name }}</div>
                                            <small class="text-muted">SKU: {{ item.product_sku }}</small>
                                        </td>
                                        <td class="text-center">{{ item.quantity }}</td>
                                        <td class="text-end">${{ formatCurrency(item.unit_price) }}</td>
                                        <td class="text-end fw-semibold">${{ formatCurrency(item.total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="summary-section mb-4">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>${{ formatCurrency(order.sub_total) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax</span>
                            <span>${{ formatCurrency(order.tax_total) }}</span>
                        </div>
                        <div class="summary-row total">
                            <span>Grand Total</span>
                            <span>${{ formatCurrency(order.grand_total) }}</span>
                        </div>
                    </div>

                    <!-- Payments -->
                    <div v-if="order.payments && order.payments.length > 0" class="payments-section mb-4">
                        <h6 class="section-title">
                            <i class="fas fa-credit-card me-2"></i>Payments
                        </h6>
                        <div class="payments-list">
                            <div
                                v-for="payment in order.payments"
                                :key="payment.id"
                                class="payment-item"
                            >
                                <div class="payment-info">
                                    <div class="payment-mode">
                                        <i :class="getPaymentIcon(payment.payment_mode)" class="me-2"></i>
                                        {{ payment.payment_mode }}
                                    </div>
                                    <small class="text-muted">{{ payment.date }}</small>
                                    <small v-if="payment.reference_no" class="text-muted d-block">
                                        Ref: {{ payment.reference_no }}
                                    </small>
                                </div>
                                <div class="payment-amount">
                                    ${{ formatCurrency(payment.amount) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div v-if="order.remark" class="remarks-section">
                        <h6 class="section-title">
                            <i class="fas fa-sticky-note me-2"></i>Remarks
                        </h6>
                        <p class="text-muted mb-0">{{ order.remark }}</p>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button @click="$emit('close')" class="btn btn-light">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button @click="$emit('print', order)" class="btn btn-primary">
                    <i class="fas fa-print me-1"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    order: {
        type: Object,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    }
});

defineEmits(['close', 'print']);

const formatCurrency = (amount) => {
    return Number(amount || 0).toFixed(2);
};

const formatStatus = (status) => {
    const statusMap = {
        'paid': 'Paid',
        'pending': 'Pending',
        'partial': 'Partial'
    };
    return statusMap[status] || status;
};

const getStatusBadgeClass = (status) => {
    const classMap = {
        'paid': 'bg-success',
        'pending': 'bg-warning text-dark',
        'partial': 'bg-info'
    };
    return classMap[status] || 'bg-secondary';
};

const getStatusIconClass = (status) => {
    const classMap = {
        'paid': 'text-success',
        'pending': 'text-warning',
        'partial': 'text-info'
    };
    return classMap[status] || 'text-secondary';
};

const getPaymentIcon = (mode) => {
    const modeLC = mode.toLowerCase();
    if (modeLC.includes('cash')) return 'fas fa-money-bill-wave';
    if (modeLC.includes('card') || modeLC.includes('credit') || modeLC.includes('debit')) return 'fas fa-credit-card';
    if (modeLC.includes('bank') || modeLC.includes('transfer')) return 'fas fa-university';
    if (modeLC.includes('check') || modeLC.includes('cheque')) return 'fas fa-money-check';
    return 'fas fa-wallet';
};
</script>

<style lang="scss" scoped>
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 1rem;
}

.order-details-modal {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;

    .header-info {
        display: flex;
        align-items: center;
    }

    .modal-title {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .btn-close {
        opacity: 0.5;
        transition: opacity 0.2s;

        &:hover {
            opacity: 1;
        }
    }
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;

    &::-webkit-scrollbar {
        width: 6px;
    }

    &::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    &::-webkit-scrollbar-thumb {
        background: #c4c4c4;
        border-radius: 3px;
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
}

// Info Cards
.info-card {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    gap: 0.75rem;

    .info-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 8px;
        color: var(--bs-primary);
        font-size: 1rem;
    }

    .info-content {
        flex: 1;
        min-width: 0;

        small {
            display: block;
            line-height: 1.3;
        }
    }
}

// Section Titles
.section-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

// Items Table
.items-table-wrapper {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;

    .table {
        margin-bottom: 0;

        th {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            background: #f8f9fa;
        }

        td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .product-name {
            font-weight: 500;
        }
    }
}

// Summary Section
.summary-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.35rem 0;
        font-size: 0.9rem;

        &.total {
            border-top: 1px solid #dee2e6;
            margin-top: 0.5rem;
            padding-top: 0.75rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--bs-primary);
        }
    }
}

// Payments Section
.payments-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;

    .payment-mode {
        font-weight: 500;
    }

    .payment-amount {
        font-weight: 600;
        font-size: 1rem;
        color: var(--bs-success);
    }
}

// Remarks Section
.remarks-section {
    background: #fff9e6;
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #ffe69c;
}
</style>
