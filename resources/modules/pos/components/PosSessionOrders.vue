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
  -  *  Last modified: 04/12/25, 11:40 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="session-orders-overlay" @click.self="posStore.closeSessionOrders()">
        <div class="session-orders-drawer">
            <!-- Header -->
            <div class="drawer-header">
                <div class="header-info">
                    <h5 class="drawer-title">
                        <i class="fas fa-receipt me-2"></i>Session Orders
                    </h5>
                    <span class="session-badge" v-if="posStore.currentSession">
                        {{ posStore.currentSession.session_number }}
                    </span>
                </div>
                <button @click="posStore.closeSessionOrders()" class="btn-close-drawer">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Summary Stats -->
            <div class="orders-summary">
                <div class="summary-stat">
                    <span class="stat-value">{{ posStore.sessionOrders.length }}</span>
                    <span class="stat-label">Orders</span>
                </div>
                <div class="summary-stat highlight">
                    <span class="stat-value">${{ formatCurrency(totalAmount) }}</span>
                    <span class="stat-label">Total Sales</span>
                </div>
            </div>

            <!-- Orders List -->
            <div class="orders-list">
                <div v-if="posStore.sessionOrdersLoading" class="loading-state">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Loading orders...</p>
                </div>

                <div v-else-if="posStore.sessionOrders.length === 0" class="empty-state">
                    <i class="fas fa-shopping-cart text-muted"></i>
                    <p>No orders in this session yet</p>
                    <small class="text-muted">Orders will appear here as you process sales</small>
                </div>

                <div v-else class="orders-container">
                    <div 
                        v-for="order in posStore.sessionOrders" 
                        :key="order.id"
                        class="order-card"
                        @click="toggleOrderDetails(order.id)"
                    >
                        <div class="order-header">
                            <div class="order-info">
                                <span class="order-number">{{ order.invoice_number }}</span>
                                <span class="order-time">
                                    <i class="fas fa-clock me-1"></i>{{ order.created_at }}
                                </span>
                            </div>
                            <div class="order-total">
                                ${{ formatCurrency(order.grand_total) }}
                            </div>
                        </div>
                        
                        <div class="order-meta">
                            <span class="customer-name">
                                <i class="fas fa-user me-1"></i>{{ order.customer_name }}
                            </span>
                            <span class="items-count">
                                {{ order.items_count }} item{{ order.items_count > 1 ? 's' : '' }}
                            </span>
                            <span :class="['status-badge', order.payment_status]">
                                {{ order.payment_status }}
                            </span>
                        </div>

                        <!-- Order Items (Expandable) -->
                        <transition name="slide">
                            <div v-if="expandedOrder === order.id" class="order-items">
                                <div 
                                    v-for="(item, idx) in order.items" 
                                    :key="idx"
                                    class="item-row"
                                >
                                    <span class="item-name">{{ item.name }}</span>
                                    <span class="item-qty">x{{ item.quantity }}</span>
                                    <span class="item-total">${{ formatCurrency(item.total) }}</span>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="drawer-footer">
                <button @click="refreshOrders" class="btn btn-outline-primary w-100" :disabled="posStore.sessionOrdersLoading">
                    <i class="fas fa-sync-alt me-2" :class="{ 'fa-spin': posStore.sessionOrdersLoading }"></i>
                    Refresh Orders
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePosStore } from '../posStore';

const posStore = usePosStore();
const expandedOrder = ref(null);

// Computed
const totalAmount = computed(() => {
    return posStore.sessionOrders.reduce((sum, order) => sum + order.grand_total, 0);
});

// Methods
const formatCurrency = (amount) => Number(amount || 0).toFixed(2);

const toggleOrderDetails = (orderId) => {
    expandedOrder.value = expandedOrder.value === orderId ? null : orderId;
};

const refreshOrders = () => {
    posStore.loadSessionOrders();
};
</script>

<style lang="scss" scoped>
.session-orders-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: flex-end;
    z-index: 9999;
    backdrop-filter: blur(2px);
}

.session-orders-drawer {
    width: 420px;
    max-width: 100%;
    height: 100vh;
    background: white;
    display: flex;
    flex-direction: column;
    box-shadow: -5px 0 20px rgba(0, 0, 0, 0.15);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}

.drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    background: var(--bs-primary);
    color: white;

    .header-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .drawer-title {
        margin: 0;
        font-weight: 600;
    }

    .session-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
    }

    .btn-close-drawer {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s;

        &:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    }
}

.orders-summary {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;

    .summary-stat {
        flex: 1;
        text-align: center;
        padding: 0.75rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

        &.highlight {
            background: var(--bs-primary);
            color: white;

            .stat-label {
                color: rgba(255, 255, 255, 0.8);
            }
        }
    }

    .stat-value {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #666;
    }
}

.orders-list {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
}

.loading-state,
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    text-align: center;

    i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    p {
        margin: 0;
        color: #666;
    }
}

.orders-container {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.order-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s;

    &:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 4px 12px rgba(0, 158, 247, 0.15);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }

    .order-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .order-number {
        font-weight: 600;
        color: #333;
    }

    .order-time {
        font-size: 0.8rem;
        color: #888;
    }

    .order-total {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--bs-success);
    }

    .order-meta {
        display: flex;
        gap: 0.75rem;
        font-size: 0.85rem;
        color: #666;
        flex-wrap: wrap;
    }

    .status-badge {
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;

        &.paid {
            background: #d4edda;
            color: #155724;
        }

        &.pending {
            background: #fff3cd;
            color: #856404;
        }

        &.partial {
            background: #d1ecf1;
            color: #0c5460;
        }
    }
}

.order-items {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px dashed #dee2e6;

    .item-row {
        display: flex;
        justify-content: space-between;
        padding: 0.35rem 0;
        font-size: 0.9rem;

        &:not(:last-child) {
            border-bottom: 1px solid #f5f5f5;
        }
    }

    .item-name {
        flex: 1;
        color: #444;
    }

    .item-qty {
        color: #888;
        margin: 0 1rem;
    }

    .item-total {
        font-weight: 600;
        color: #333;
    }
}

.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    max-height: 0;
}

.slide-enter-to,
.slide-leave-from {
    opacity: 1;
    max-height: 500px;
}

.drawer-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}
</style>
