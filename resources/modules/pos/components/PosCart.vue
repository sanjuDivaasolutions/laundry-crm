<template>
    <div class="pos-cart card shadow-sm flex-grow-1 mb-3">
        <!-- Card Header -->
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <h5 class="card-title mb-0 d-flex align-items-center">
                <i class="fas fa-shopping-cart me-2 text-primary"></i>
                Shopping Cart
                <span class="badge badge-primary ms-2">{{ posStore.cartItemsCount }}</span>
            </h5>
            <button
                @click="clearCart"
                class="btn btn-sm btn-light-danger"
                :disabled="posStore.cartItems.length === 0"
            >
                <i class="fas fa-trash me-1"></i> Clear
            </button>
        </div>

        <!-- Cart Body -->
        <div class="card-body p-0 cart-body">
            <!-- Empty Cart -->
            <div v-if="posStore.cartItems.length === 0" class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-basket text-primary"></i>
                </div>
                <p class="text-muted mb-1">Cart is empty</p>
                <small class="text-muted">Add products to start a sale</small>
            </div>

                <!-- Cart Items -->
                <div v-else class="cart-items-wrapper">
                    <TransitionGroup name="cart-item" tag="div">
                        <div
                            v-for="(item, index) in posStore.cartItems"
                            :key="item.id"
                            class="cart-item"
                        >
                            <div class="cart-item-row">
                                <div class="cart-item-info">
                                    <div class="cart-item-name">
                                        {{ item.name }}
                                        <span v-if="item.requires_prescription" class="badge badge-warning badge-sm ms-1">Rx</span>
                                    </div>
                                    <div class="cart-item-meta d-none d-sm-block">
                                        <small class="text-muted">${{ formatCurrency(item.unit_price) }} each</small>
                                    </div>
                                </div>

                                <!-- Quantity Controls (inline) -->
                                <div class="quantity-controls inline-controls">
                                    <button
                                        @click="decreaseQuantity(index)"
                                        class="qty-btn qty-minus"
                                    >
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input
                                        type="number"
                                        v-model.number="item.quantity"
                                        @change="updateQuantity(index, item.quantity)"
                                        class="qty-input"
                                        min="0"
                                        :max="item.stock_quantity"
                                    />
                                    <button
                                        @click="increaseQuantity(index)"
                                        class="qty-btn qty-plus"
                                    >
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>

                                <div class="cart-item-total">
                                    ${{ formatCurrency(item.total) }}
                                </div>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>
        </div>

        <!-- Cart summary removed as requested -->
    </div>
</template>

<script setup>
import { usePosStore } from '../posStore';

const posStore = usePosStore();

// removeItem removed — removal happens when quantity becomes 0 via updateQuantity

const updateQuantity = (index, quantity) => {
    posStore.updateQuantity(index, quantity);
};

const increaseQuantity = (index) => {
    const item = posStore.cartItems[index];
    if (item.quantity < item.stock_quantity) {
        posStore.updateQuantity(index, item.quantity + 1);
    }
};

const decreaseQuantity = (index) => {
    const item = posStore.cartItems[index];
    // allow decreasing to 0 — store will remove when quantity <= 0
    if (item.quantity >= 0) {
        posStore.updateQuantity(index, item.quantity - 1);
    }
};

const clearCart = () => {
    if (confirm('Are you sure you want to clear the cart?')) {
        posStore.clearCart();
    }
};

const formatCurrency = (amount) => {
    return Number(amount).toFixed(2);
};
</script>

<style lang="scss" scoped>
.pos-cart {
    display: flex;
    flex-direction: column;
    height: 100%; // Ensure the cart takes full height of its container

    .card-header {
        flex-shrink: 0;
        border-bottom: 1px solid #e9ecef;
        padding: 0.45rem 0.65rem;
    }

    .card-title {
        font-size: 0.95rem;
        font-weight: 600;
    }

    .card-title .badge {
        font-size: 0.8rem;
        padding: 0.3rem 0.45rem;
    }

    .cart-body {
        flex: 1;
        overflow-y: auto;
        min-height: 0;

        &::-webkit-scrollbar {
            width: 6px;
        }

        &::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        &::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;

            &:hover {
                background: #a1a1a1;
            }
        }
    }

    .card-footer {
        flex-shrink: 0;
        border-top: 1px solid #e9ecef;
        padding: 0.6rem 0.75rem; /* smaller footer padding */
    }
}

// Empty Cart
.empty-cart {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 1rem;
    text-align: center;

    .empty-cart-icon {
        width: 60px;
        height: 60px;
        background: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;

        i {
            font-size: 1.5rem;
        }
    }
}

// Cart Items
.cart-items-wrapper {
    padding: 0;
}

.cart-item {
    padding: 0.55rem 0.65rem;
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s ease;

    &:hover {
        background-color: #f9fafb;
    }

    &:last-child {
        border-bottom: none;
    }
}

/* Single row layout */
.cart-item-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cart-item-info {
    flex: 1;
    min-width: 0;
}

.cart-item-name {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.1rem;
}

.inline-controls {
    display: flex;
    align-items: center;
}

.qty-input {
    width: 40px;
    height: 28px;
    padding: 0;
    font-size: 0.85rem;
}

.qty-btn {
    width: 26px;
    height: 26px;
    i { font-size: 0.7rem; }
}

.cart-item-total {
    width: 85px;
    text-align: right;
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--bs-success);
}

.cart-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.cart-item-info {
    flex: 1;
    min-width: 0;
    padding-right: 0.5rem;
}

.cart-item-name {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
    color: #212529;
    line-height: 1.3;

    // Handle long names
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.cart-item-meta {
    font-size: 0.75rem;
    line-height: 1.4;
    color: #a1a5b7;
}

/* remove-btn styles obsolete; remove button removed from template */

.cart-item-footer {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

// Quantity Controls
.quantity-controls {
    display: flex;
    align-items: center;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
    background: white;
}

.qty-btn {
    width: 26px;
    height: 26px;
    border: none;
    background: #f5f8fa;
    color: #6c757d;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;

    &:hover:not(:disabled) {
        background: var(--bs-primary);
        color: white;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    i {
        font-size: 0.7rem;
    }
}

.qty-input {
    width: 40px;
    height: 26px;
    border: none;
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
    text-align: center;
    font-size: 0.85rem;
    font-weight: 600;
    color: #212529;
    background: white;

    &:focus {
        outline: none;
    }

    // Hide spinners
    &::-webkit-inner-spin-button,
    &::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    -moz-appearance: textfield;
}

.cart-item-max {
    font-size: 0.7rem;
    color: #999;
    white-space: nowrap;
}

.cart-item-total {
    margin-left: auto;
    font-size: 1rem;
    font-weight: 600;
    color: var(--bs-success);
    white-space: nowrap;
}

// Cart Summary
.cart-summary {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .summary-label {
        font-size: 0.85rem;
        color: #666;
    }

    .summary-value {
        font-size: 0.95rem;
        font-weight: 600;
    }

    &.summary-total {
        padding-top: 0.5rem;
        border-top: 2px solid var(--bs-primary);
        margin-top: 0.5rem;

        .summary-label {
            font-weight: 700;
            font-size: 1rem;
            color: #212529;
        }

        .summary-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--bs-success);
        }
    }
}

// Transitions
.cart-item-enter-active,
.cart-item-leave-active {
    transition: all 0.3s ease;
}

.cart-item-enter-from {
    opacity: 0;
    transform: translateX(20px);
}

.cart-item-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

.cart-item-move {
    transition: transform 0.3s ease;
}
</style>