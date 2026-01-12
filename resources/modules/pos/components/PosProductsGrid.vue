<template>
    <div class="pos-products-grid flex-grow-1">
        <!-- Loading State -->
        <div v-if="posStore.loadingProducts" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-3">Loading products...</p>
        </div>
        
        <!-- Products Grid/List -->
        <div v-else-if="posStore.products.length > 0" class="products-container">
            <!-- Grid View -->
            <div v-if="posStore.viewMode === 'grid'" class="row g-3">
                <div
                    v-for="product in posStore.products"
                    :key="product.id"
                    class="col-xxl-2 col-xl-3 col-lg-3 col-md-4 col-sm-6"
                >
                    <div 
                        @click="addToCart(product)"
                        class="product-card card h-100 cursor-pointer"
                        :class="{ 
                            'out-of-stock': product.stock_quantity <= 0,
                            'low-stock': product.is_low_stock && product.stock_quantity > 0
                        }"
                    >
                        
                        <!-- Product Image/Icon -->
                        <div class="card-body text-center p-3">
                            <div class="product-icon mb-3">
                                <div class="symbol symbol-70px mx-auto">
                                    <div class="symbol-label bg-light-primary">
                                        <i :class="getProductIcon(product)" class="text-primary fs-1"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Product Info -->
                            <h6 class="product-name fw-bold text-dark mb-1" :title="product.name">
                                {{ truncate(product.name, 25) }}
                            </h6>
                            
                            <!-- Medicine Specific -->
                            <div v-if="product.is_medicine" class="mb-2">
                                <span class="badge badge-light-info badge-sm" v-if="product.strength">
                                    {{ product.strength }}
                                </span>
                                <span class="badge badge-light-warning badge-sm ms-1" v-if="product.requires_prescription">
                                    <i class="fas fa-prescription"></i> Rx
                                </span>
                            </div>
                            
                            <!-- Price and Stock -->
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="text-start">
                                    <div class="fw-bold text-primary fs-5">
                                        ${{ formatCurrency(product.selling_price) }}
                                    </div>
                                    <small class="text-muted" v-if="product.unit">
                                        per {{ product.unit }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="badge badge-light-primary">
                                        Stock: {{ product.stock_quantity }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="products-list">
                <div
                    v-for="product in posStore.products"
                    :key="product.id"
                    @click="addToCart(product)"
                    class="product-list-item"
                    :class="{
                        'out-of-stock': product.stock_quantity <= 0,
                        'low-stock': product.is_low_stock && product.stock_quantity > 0
                    }"
                >
                    <!-- Product Icon -->
                    <div class="list-item-icon">
                        <div class="icon-wrapper">
                            <i :class="getProductIcon(product)" class="text-primary"></i>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="list-item-info">
                        <h6 class="product-name">
                            {{ product.name }}
                            <span v-if="product.requires_prescription" class="badge badge-warning badge-sm ms-2">Rx</span>
                        </h6>
                        <div class="product-meta">
                            <span v-if="product.category_name" class="text-muted ms-3">{{ product.category_name }}</span>
                        </div>
                    </div>

                    <!-- Stock Badge -->
                    <div class="list-item-stock">
                        <span v-if="product.stock_quantity <= 0" class="badge badge-danger">Out of Stock</span>
                        <span v-else-if="product.is_low_stock" class="badge badge-warning">Low: {{ product.stock_quantity }}</span>
                        <!-- In-stock badge removed per UI update -->
                    </div>

                    <!-- Price -->
                    <div class="list-item-price">
                        <span class="price">${{ formatCurrency(product.selling_price) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Products -->
        <div v-else class="text-center py-5">
            <div class="symbol symbol-100px mx-auto mb-3">
                <div class="symbol-label bg-light-warning">
                    <i class="fas fa-box-open text-warning fs-1"></i>
                </div>
            </div>
            <h5 class="text-muted">No products found</h5>
            <p class="text-muted">Try adjusting your search or filter criteria</p>
            <button @click="posStore.loadProducts()" class="btn btn-primary">
                <i class="fas fa-sync me-2"></i>Reload Products
            </button>
        </div>
    </div>
</template>

<script setup>
import { usePosStore } from '../posStore';

const posStore = usePosStore();

const addToCart = (product) => {
    if (product.stock_quantity <= 0) {
        posStore.error = `${product.name} is out of stock!`;
        return;
    }
    
    posStore.addToCart(product);
};

const getProductIcon = (product) => {
    if (product.is_service) return 'fas fa-concierge-bell';
    if (product.is_medicine) return 'fas fa-pills';
    if (product.category_name?.includes('Injection')) return 'fas fa-syringe';
    if (product.category_name?.includes('Equipment')) return 'fas fa-stethoscope';
    if (product.category_name?.includes('Syrup')) return 'fas fa-prescription-bottle';
    return 'fas fa-box';
};

const formatCurrency = (amount) => {
    return Number(amount).toFixed(2);
};

const truncate = (text, length) => {
    if (text.length <= length) return text;
    return text.substring(0, length) + '...';
};
</script>

<style lang="scss" scoped>
.pos-products-grid {
    overflow-y: auto;
    
    .products-container {
        padding: 0.5rem;
    }
    
    .product-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        
        &:hover:not(.out-of-stock) {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: var(--bs-primary);
        }
        
        &.out-of-stock {
            opacity: 0.6;
            cursor: not-allowed;
            
            .card-body {
                background: #f8f9fa;
            }
        }
        
        &.low-stock {
            border-color: var(--bs-warning);
        }
        
        .product-name {
            min-height: 2.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-icon {
            .symbol-label {
                transition: all 0.3s ease;
            }
        }
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    .z-index-1 {
        z-index: 1;
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

        .list-item-icon {
            flex-shrink: 0;

            .icon-wrapper {
                width: 45px;
                height: 45px;
                background: rgba(var(--bs-primary-rgb), 0.1);
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;

                i {
                    font-size: 1.25rem;
                }
            }
        }

        .list-item-info {
            flex: 1;
            min-width: 0;

            .product-name {
                margin: 0;
                font-weight: 600;
                font-size: 0.95rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
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
                font-weight: 600;
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
}

// Custom scrollbar
.pos-products-grid::-webkit-scrollbar {
    width: 8px;
}

.pos-products-grid::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.pos-products-grid::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;

    &:hover {
        background: #555;
    }
}

// Responsive for list view
@media (max-width: 992px) {
    .pos-products-grid {
        .product-list-item {
            flex-wrap: wrap;

            .list-item-info {
                order: 1;
                flex: 1 1 calc(100% - 60px);
            }

            .list-item-icon {
                order: 0;
            }

            .list-item-stock,
            .list-item-price,
            .list-item-actions {
                order: 2;
            }
        }
    }
}
</style>