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
    <div class="fullpage-pos-wrapper">
        <!-- Fullscreen Header -->
        <div class="fullscreen-header-wrapper">
            <!-- Primary Business Row -->
            <div class="fullscreen-header-primary">
                <!-- Left: Branding -->
                <div class="header-section header-left">
                    <i class="fas fa-capsules text-white fs-4 me-2"></i>
                    <h1 class="text-white fs-5 fw-semibold mb-0">Medical POS</h1>
                </div>

                <!-- Center: Stats -->
                <div class="header-section header-center">
                    <div class="header-stats">
                        <span class="stat-item">
                            <span class="stat-value text-white fw-bold">${{ formatCurrency(posStore.todaySales) }}</span>
                            <span class="stat-label text-white-75">Sales</span>
                        </span>
                        <span class="stat-divider">•</span>
                        <span class="stat-item">
                            <span class="stat-value text-white fw-bold">{{ posStore.transactionCount }}</span>
                            <span class="stat-label text-white-75">Trans</span>
                        </span>
                        <span class="stat-divider">•</span>
                        <span class="stat-item">
                            <span class="stat-value text-white fw-bold">{{ posStore.cartItemsCount }}</span>
                            <span class="stat-label text-white-75">{{ posStore.cartItemsCount === 1 ? 'Item' : 'Items' }}</span>
                        </span>
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="header-section header-right">
                    <button
                        v-if="posStore.hasActiveSession"
                        @click="posStore.toggleSessionOrders()"
                        class="header-btn"
                    >
                        <i class="fas fa-receipt me-1"></i>
                        Orders
                        <span class="orders-count" v-if="posStore.currentSession?.total_transactions > 0">
                            {{ posStore.currentSession.total_transactions }}
                        </span>
                    </button>
                    <button
                        v-if="posStore.hasActiveSession"
                        @click="posStore.openSessionModal('close')"
                        class="header-btn header-btn-danger"
                    >
                        <i class="fas fa-door-open me-1"></i>
                        End Session
                    </button>
                    <div class="user-menu">
                        <button class="header-btn">
                            <i class="fas fa-user-circle me-1"></i>
                            {{ userName }}
                            <i class="fas fa-chevron-down ms-1 fs-8"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Secondary Utilities Row -->
            <div class="header-utilities">
                <div class="shortcuts">
                    <span class="shortcut-hint">F1 Search</span>
                    <span class="divider">|</span>
                    <span class="shortcut-hint">F2 Scanner</span>
                    <span class="divider">|</span>
                    <span class="shortcut-hint">F8 Clear</span>
                    <span class="divider">|</span>
                    <span class="shortcut-hint">F10 Pay</span>
                </div>
                <div class="session-info">
                    <span class="session-id" v-if="posStore.hasActiveSession">
                        Session: {{ posStore.currentSession?.session_number }}
                    </span>
                    <span class="session-id text-warning" v-else>
                        <i class="fas fa-exclamation-circle me-1"></i>No Active Session
                    </span>
                    <router-link to="/pos" class="exit-fullscreen" title="Exit Fullscreen (Esc)">
                        <i class="fas fa-compress"></i>
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Main POS Content -->
        <div class="pos-main-container">
            <!-- Left Panel - Products -->
            <div class="products-panel">
                <!-- Search Bar -->
                <div class="search-section">
                    <div class="card shadow-sm">
                        <div class="card-body p-2">
                            <div class="row g-3 align-items-center">
                                <!-- Search Input -->
                                <div class="col-lg-7 col-md-6">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input
                                            id="pos-search-input"
                                            type="text"
                                            v-model="posStore.searchQuery"
                                            @input="posStore.searchProducts()"
                                            class="form-control"
                                            placeholder="Search by name, SKU, or scan barcode..."
                                            autocomplete="off"
                                        />
                                        <button
                                            @click="posStore.toggleScanner"
                                            class="btn"
                                            :class="posStore.scannerActive ? 'btn-warning' : 'btn-primary'"
                                            title="Toggle Barcode Scanner (F2)"
                                        >
                                            <i class="fas fa-barcode me-2"></i>
                                            {{ posStore.scannerActive ? 'Stop' : 'Scan' }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Category Filter -->
                                <div class="col-lg-3 col-md-4">
                                    <select
                                        v-model="posStore.selectedCategory"
                                        @change="posStore.filterByCategory()"
                                        class="form-select form-select-lg"
                                    >
                                        <option value="">All Categories</option>
                                        <option
                                            v-for="category in posStore.categories"
                                            :key="category.id"
                                            :value="category.id"
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Quick Actions -->
                                <div class="col-lg-2 col-md-2">
                                    <div class="btn-group w-100">
                                        <button
                                            @click="toggleView"
                                            class="btn btn-light btn-lg"
                                            title="Toggle View"
                                        >
                                            <i :class="posStore.viewMode === 'grid' ? 'fas fa-th' : 'fas fa-list'"></i>
                                        </button>
                                        <button
                                            @click="refreshProducts"
                                            class="btn btn-light btn-lg"
                                            title="Refresh"
                                            :disabled="posStore.loadingProducts"
                                        >
                                            <i class="fas fa-sync" :class="{ 'fa-spin': posStore.loadingProducts }"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Filters -->
                            <div v-if="hasActiveFilters" class="mt-2">
                                <span class="badge badge-light-primary me-2" v-if="posStore.searchQuery">
                                    Search: {{ posStore.searchQuery }}
                                    <i class="fas fa-times ms-1 cursor-pointer" @click="clearSearch"></i>
                                </span>
                                <span class="badge badge-light-info me-2" v-if="posStore.selectedCategory">
                                    Category: {{ getCategoryName(posStore.selectedCategory) }}
                                    <i class="fas fa-times ms-1 cursor-pointer" @click="clearCategory"></i>
                                </span>
                                <button @click="clearAllFilters" class="btn btn-sm btn-light-danger">
                                    Clear All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Categories -->
                <div class="quick-categories" v-if="posStore.categories.length > 0 && !posStore.loadingProducts">
                    <div class="categories-scroll">
                        <button
                            @click="selectCategory(null)"
                            class="category-btn"
                            :class="{ 'active': !posStore.selectedCategory }"
                        >
                            <i class="fas fa-th me-2"></i>All Products
                        </button>
                        <button
                            v-for="category in topCategories"
                            :key="category.id"
                            @click="selectCategory(category.id)"
                            class="category-btn"
                            :class="{ 'active': posStore.selectedCategory === category.id }"
                        >
                            <i :class="getCategoryIcon(category.name)" class="me-2"></i>
                            {{ category.name }}
                        </button>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="products-grid-container">
                    <!-- Loading State -->
                    <div v-if="posStore.loadingProducts" class="loading-state">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-3">Loading products...</p>
                    </div>

                    <!-- Products Grid/List -->
                    <div v-else-if="posStore.products.length > 0">
                        <!-- Grid View -->
                        <div v-if="posStore.viewMode === 'grid'" class="products-grid">
                            <div
                                v-for="product in sortedProducts"
                                :key="product.id"
                                @click="addToCart(product)"
                                class="product-card"
                                :class="{
                                    'out-of-stock': product.stock_quantity <= 0,
                                    'low-stock': product.is_low_stock && product.stock_quantity > 0
                                }"
                            >
                                <!-- Product icon removed per UI update -->

                                <!-- Product Info -->
                                <div class="product-info">
                                    <h6 class="product-name" :title="product.name">
                                        {{ product.name }}
                                    </h6>

                                    <!-- Medicine Badges -->
                                    <div class="medicine-badges" v-if="product.is_medicine">
                                        <span class="badge badge-light-info" v-if="product.strength">
                                            {{ product.strength }}
                                        </span>
                                        <span class="badge badge-light-warning ms-1" v-if="product.requires_prescription">
                                            <i class="fas fa-prescription"></i> Rx
                                        </span>
                                    </div>

                                    <!-- Price and Stock -->
                                    <div class="price-stock">
                                        <div class="price">${{ formatCurrency(product.selling_price) }}</div>
                                        <div class="stock">Stock: {{ product.stock_quantity }}</div>
                                    </div>

                                    <!-- Quick add removed per UI update -->
                                </div>
                            </div>
                        </div>

                        <!-- List View -->
                        <div v-else class="products-list">
                            <div
                                v-for="product in sortedProducts"
                                :key="product.id"
                                @click="addToCart(product)"
                                class="product-list-item"
                                :class="{
                                    'out-of-stock': product.stock_quantity <= 0,
                                    'low-stock': product.is_low_stock && product.stock_quantity > 0
                                }"
                            >
                                <!-- Product icon removed per UI update -->

                                <!-- Product Info -->
                                <div class="list-item-info">
                                    <h6 class="product-name">
                                        {{ product.name }}
                                        <span v-if="product.requires_prescription" class="badge badge-warning badge-sm ms-2">Rx</span>
                                    </h6>
                                    <div class="product-meta">
                                        <span v-if="product.category_name" class="text-muted">{{ product.category_name }}</span>
                                    </div>
                                </div>


                                <!-- Price -->
                                <div class="list-item-price">
                                    <span class="price">${{ formatCurrency(product.selling_price) }}</span>
                                </div>

                                <!-- Quick add removed per UI update -->
                            </div>
                        </div>
                    </div>

                    <!-- No Products -->
                    <div v-else class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-box-open text-warning"></i>
                        </div>
                        <h5>No products found</h5>
                        <p class="text-muted">Try adjusting your search or filter criteria</p>
                        <button @click="refreshProducts" class="btn btn-primary">
                            <i class="fas fa-sync me-2"></i>Reload Products
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Cart & Payment -->
            <div class="cart-panel">
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
        <PosSessionModal v-if="posStore.showSessionModal || posStore.closingSessionSummary" />
        <PosSessionOrders v-if="posStore.showSessionOrders" />

        <!-- Toast Notifications -->
        <PosNotifications />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePosStore } from './posStore';
import { useAuthStore } from '@/stores/auth';
import PosCustomerSelect from './components/PosCustomerSelect.vue';
import PosCart from './components/PosCart.vue';
import PosPayment from './components/PosPayment.vue';
import PosReceiptModal from './components/PosReceiptModal.vue';
import PosBarcodeScanner from './components/PosBarcodeScanner.vue';
import PosNotifications from './components/PosNotifications.vue';
import PosSessionModal from './components/PosSessionModal.vue';
import PosSessionOrders from './components/PosSessionOrders.vue';

const posStore = usePosStore();
const authStore = useAuthStore();

// State
const currentDateTime = ref('');

// Computed
const userName = computed(() => authStore.user?.name || 'Guest User');

const topCategories = computed(() => posStore.categories.slice(0, 10));

const hasActiveFilters = computed(() => posStore.searchQuery || posStore.selectedCategory);

// Show products ordered by stock (highest stock first) so in-stock appear top
const sortedProducts = computed(() => {
    const list = Array.isArray(posStore.products) ? [...posStore.products] : [];
    return list.sort((a, b) => {
        const sa = Number(a.stock_quantity || 0);
        const sb = Number(b.stock_quantity || 0);
        if (sb !== sa) return sb - sa; // descending by stock
        return (a.name || '').localeCompare(b.name || '');
    });
});

// Methods
const formatCurrency = (amount) => Number(amount || 0).toFixed(2);

const truncate = (text, length) => {
    if (!text) return '';
    return text.length <= length ? text : text.substring(0, length) + '...';
};

const updateDateTime = () => {
    const now = new Date();
    currentDateTime.value = now.toLocaleString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const addToCart = (product) => {
    if (product.stock_quantity <= 0) {
        posStore.error = `${product.name} is out of stock!`;
        return;
    }
    posStore.addToCart(product);
};

// Quick add feature has been removed from the full page POS UI.

const selectCategory = (categoryId) => {
    posStore.selectedCategory = categoryId || '';
    posStore.filterByCategory();
};

const toggleView = () => {
    posStore.toggleViewMode();
};

const refreshProducts = async () => {
    await posStore.loadAllProducts();
};

const clearSearch = () => {
    posStore.searchQuery = '';
    posStore.searchProducts();
};

const clearCategory = () => {
    posStore.selectedCategory = '';
    posStore.filterByCategory();
};

const clearAllFilters = () => {
    clearSearch();
    clearCategory();
};

const getCategoryName = (categoryId) => {
    const category = posStore.categories.find(c => c.id === categoryId);
    return category ? category.name : '';
};

const getCategoryIcon = (categoryName) => {
    const iconMap = {
        'Medicine': 'fas fa-pills',
        'Tablets': 'fas fa-tablets',
        'Syrup': 'fas fa-prescription-bottle',
        'Injection': 'fas fa-syringe',
        'Equipment': 'fas fa-stethoscope',
        'Surgical': 'fas fa-user-md',
        'Vitamins': 'fas fa-capsules',
        'First Aid': 'fas fa-first-aid'
    };

    for (const [key, icon] of Object.entries(iconMap)) {
        if (categoryName && categoryName.includes(key)) {
            return icon;
        }
    }
    return 'fas fa-tag';
};

// Product icons removed from Full Page POS UI; kept in other components where needed.

// Keyboard shortcuts
const handleKeyboard = (e) => {
    if (e.key === 'F1') {
        e.preventDefault();
        document.querySelector('#pos-search-input')?.focus();
    } else if (e.key === 'F2') {
        e.preventDefault();
        posStore.toggleScanner();
    } else if (e.key === 'F8') {
        e.preventDefault();
        if (confirm('Clear cart?')) {
            posStore.clearCart();
        }
    } else if (e.key === 'F10') {
        e.preventDefault();
        if (posStore.canProcessPayment) {
            posStore.processSale();
        }
    } else if (e.key === 'Escape') {
        e.preventDefault();
        // Exit fullscreen on Escape
        window.location.href = '/pos';
    }
};

let dateInterval;

onMounted(async () => {
    // Load products, categories, and payment modes (customers are searched on-demand)
    await Promise.all([
        posStore.loadAllProducts(),
        posStore.loadCategories(),
        posStore.loadPaymentModes()
    ]);

    // Update date/time
    updateDateTime();
    dateInterval = setInterval(updateDateTime, 60000);

    // Load stats from store
    await posStore.loadTodayStats();

    // Check for active session
    const hasSession = await posStore.checkActiveSession();
    if (!hasSession) {
        // Show open session modal
        posStore.openSessionModal('open');
    }

    // Add keyboard listener
    window.addEventListener('keydown', handleKeyboard);
});

onUnmounted(() => {
    if (dateInterval) clearInterval(dateInterval);
    window.removeEventListener('keydown', handleKeyboard);
});
</script>

<style lang="scss" scoped>
.fullpage-pos-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: #f5f8fa;
    display: flex;
    flex-direction: column;
    z-index: 9999;
    overflow: hidden;
}

// Header Styles - Enterprise Clean Design
.fullscreen-header-wrapper {
    position: sticky;
    top: 0;
    z-index: 100;
}

// Primary Business Row
.fullscreen-header-primary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.55rem 1.5rem;
    background-color: var(--bs-primary);
    min-height: 50px;

    .header-section {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-left {
        flex: 0 0 auto;
    }

    .header-center {
        flex: 1 1 auto;
        justify-content: center;
    }

    .header-right {
        flex: 0 0 auto;
        gap: 0.5rem;
    }
}

// Stats - Inline Clean Layout
.header-stats {
    display: flex;
    align-items: center;
    gap: 1rem;

    .stat-item {
        display: inline-flex;
        align-items: baseline;
        gap: 0.35rem;
    }

    .stat-value {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .stat-label {
        font-size: 0.8rem;
        opacity: 0.85;
    }

    .stat-divider {
        color: rgba(255, 255, 255, 0.3);
        font-weight: 300;
    }
}

// Header Buttons - Subtle Professional Style
.header-btn {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
    transition: background 0.15s;
    cursor: pointer;
    white-space: nowrap;
    position: relative;

    &:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    &-danger {
        background: rgba(220, 53, 69, 0.15);
        border-color: rgba(220, 53, 69, 0.3);

        &:hover {
            background: rgba(220, 53, 69, 0.25);
        }
    }

    i {
        font-size: 0.75rem;
    }

    .orders-count {
        background: rgba(255, 255, 255, 0.9);
        color: var(--bs-primary);
        font-size: 0.7rem;
        padding: 0.1rem 0.35rem;
        border-radius: 10px;
        margin-left: 0.5rem;
        font-weight: 600;
    }
}

.user-menu {
    .header-btn {
        i.fa-chevron-down {
            font-size: 0.65rem;
            opacity: 0.7;
        }
    }
}

// Utilities Row - Secondary Information
.header-utilities {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.35rem 1.5rem;
    background: #f5f8fa;
    border-bottom: 1px solid #e4e6ef;
    min-height: 28px;

    .shortcuts {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .shortcut-hint {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;

        &:hover {
            color: #3f4254;
        }
    }

    .divider {
        color: #dee2e6;
    }

    .session-info {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .session-id {
        font-size: 0.75rem;
        color: #a1a5b7;
        font-family: 'Courier New', monospace;

        &.text-warning {
            color: #ffc107 !important;
        }
    }

    .exit-fullscreen {
        background: none;
        border: none;
        color: #6c757d;
        padding: 0.25rem 0.5rem;
        cursor: pointer;
        transition: color 0.15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;

        &:hover {
            color: var(--bs-primary);
        }
    }
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.75) !important;
}

// Main Container
.pos-main-container {
    flex: 1;
    display: flex;
    min-height: 0;
    overflow: hidden;
}

// Products Panel
.products-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 0.75rem;
    background: white;
    border-right: 1px solid #e4e6ef;
    overflow: hidden;

    .search-section {
        margin-bottom: 0.75rem;

        .input-group-text {
            border-right: none;
        }

        .form-control {
            border-left: none;

            &:focus {
                box-shadow: none;
                border-color: #ced4da;
            }
        }
    }

    .quick-categories {
        margin-bottom: 1rem;

        .categories-scroll {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;

            &::-webkit-scrollbar {
                height: 6px;
            }

            &::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 3px;
            }

            &::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 3px;
            }
        }

        .category-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            white-space: nowrap;
            transition: all 0.2s;
            font-size: 0.9rem;

            &:hover {
                border-color: var(--bs-primary);
                background: #fff;
            }

            &.active {
                background: var(--bs-primary);
                color: white;
                border-color: var(--bs-primary);
            }
        }
    }
}

// Products Grid Container
.products-grid-container {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;

    &::-webkit-scrollbar {
        width: 8px;
    }

    &::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    &::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;

        &:hover {
            background: #555;
        }
    }
}

// Products Grid
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
}

// Product Card
.product-card {
    background: white;
    border: 1px solid #e4e6ef;
    border-radius: 8px;
    padding: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    position: relative;

    &:hover:not(.out-of-stock) {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: var(--bs-primary);
    }

    &.out-of-stock {
        opacity: 0.6;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    &.low-stock {
        border-color: var(--bs-warning);
    }

    .stock-badge {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        z-index: 1;

        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
    }

    /* Product icons removed from full page POS */

    .product-info {
        text-align: center;
    }

    .product-name {
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 0.35rem;
        line-height: 1.3;
        /* Show full name and allow wrapping */
        white-space: normal;
        overflow: visible;
        display: block;
    }

    /* SKU display removed from product cards */

    .medicine-badges {
        margin-bottom: 0.5rem;

        .badge {
            font-size: 0.7rem;
        }
    }

    .price-stock {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;

        .price {
            color: var(--bs-primary);
            font-weight: 600;
            font-size: 1rem;
        }

        .stock {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-size: 0.7rem;
        }
    }

    .quick-add-buttons {
        display: flex;
        gap: 0.25rem;

        .btn {
            flex: 1;
            padding: 0.35rem;
            font-size: 0.8rem;
        }
    }
}

// List View Styles
.products-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.product-list-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    background: white;
    border: 2px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);

    &:hover:not(.out-of-stock) {
        border-color: var(--bs-primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    &.out-of-stock {
        opacity: 0.6;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    &.low-stock {
        border-color: var(--bs-warning);
    }

    /* List item icons removed from full page POS */

    .list-item-info {
        flex: 1;
        min-width: 0;

        .product-name {
            margin: 0;
            font-weight: 600;
            font-size: 0.95rem;
            /* Show full name in list view */
            white-space: normal;
            overflow: visible;
        }

        .product-meta {
            font-size: 0.8rem;
        }
    }

    .list-item-stock {
        flex-shrink: 0;

        .badge {
            font-size: 0.75rem;
        }
    }

    .list-item-price {
        flex-shrink: 0;
        min-width: 80px;
        text-align: right;

        .price {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--bs-primary);
        }
    }

    .list-item-actions {
        flex-shrink: 0;
        display: flex;
        gap: 0.25rem;

        .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
    }
}

// Loading & Empty States
.loading-state,
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    text-align: center;
    min-height: 300px;
}

.empty-state {
    .empty-icon {
        width: 100px;
        height: 100px;
        background: rgba(255, 193, 7, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;

        i {
            font-size: 3rem;
        }
    }

    h5 {
        margin-bottom: 0.5rem;
    }

    p {
        margin-bottom: 1.5rem;
    }
}

// Cart Panel
.cart-panel {
    width: 520px; /* increased cart size */
    display: flex;
    flex-direction: column;
    background: #f5f8fa;
    padding: 0.75rem; /* slightly reduced padding */
    overflow-y: auto;

    &::-webkit-scrollbar {
        width: 6px;
    }

    &::-webkit-scrollbar-track {
        background: transparent;
    }

    &::-webkit-scrollbar-thumb {
        background: #c4c4c4;
        border-radius: 3px;
    }
}

// Utility Classes
.cursor-pointer {
    cursor: pointer;
}

// Responsive
@media (max-width: 1600px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .cart-panel {
        width: 460px;
    }
}

@media (max-width: 1400px) {
    .fullscreen-header .header-content {
        padding: 0.65rem 1.25rem;
    }

    .fullscreen-header .header-center .stats-group {
        padding: 0.5rem 1rem;
        gap: 1rem;
    }

    .fullscreen-header .header-right .shortcuts-info {
        display: none;
    }

    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }

    .cart-panel {
        width: 420px;
    }
}

@media (max-width: 1200px) {
    .pos-main-container {
        flex-direction: column;
    }

    .products-panel {
        border-right: none;
        border-bottom: 1px solid #e4e6ef;
        max-height: 60vh;
    }

    .cart-panel {
        width: 100%;
        max-height: 40vh;
    }
}

@media (max-width: 991px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.5rem;
    }

    .cart-panel {
        max-height: 45vh;
    }

    .fullscreen-header .header-center {
        display: none;
    }
}
</style>
