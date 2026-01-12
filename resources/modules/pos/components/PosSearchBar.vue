<template>
    <div class="pos-search-bar mb-1">
        <div class="card shadow-sm">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-md-7">
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
                                class="btn btn-primary"
                                :class="{ 'btn-warning': posStore.scannerActive }"
                                title="Toggle Barcode Scanner (F2)"
                            >
                                <i class="fas fa-barcode me-2"></i>
                                {{ posStore.scannerActive ? 'Stop' : 'Scan' }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="col-md-3">
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
                    <div class="col-md-2">
                        <div class="btn-group w-100" role="group">
                            <button 
                                @click="toggleView"
                                class="btn btn-light btn-lg"
                                title="Toggle View"
                            >
                                <i :class="viewIcon"></i>
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
                        Clear All Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePosStore } from '../posStore';

const posStore = usePosStore();

const viewIcon = computed(() => {
    return posStore.viewMode === 'grid' ? 'fas fa-th' : 'fas fa-list';
});

const hasActiveFilters = computed(() => {
    return posStore.searchQuery || posStore.selectedCategory;
});

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
</script>

<style lang="scss" scoped>
.pos-search-bar {
    .cursor-pointer {
        cursor: pointer;
    }
    
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
</style>