<template>
    <div class="pos-quick-categories mb-2" v-if="topCategories.length > 0">
        <div class="d-flex gap-2 overflow-auto pb-2">
            <button
                @click="selectCategory(null)"
                class="btn btn-light-primary btn-sm px-4 py-2 text-nowrap"
                :class="{ 'active': !posStore.selectedCategory }"
            >
                <i class="fas fa-th me-2"></i>
                All Products
            </button>
            <button
                v-for="category in topCategories"
                :key="category.id"
                @click="selectCategory(category.id)"
                class="btn btn-light btn-sm px-4 py-2 text-nowrap"
                :class="{ 
                    'btn-primary': posStore.selectedCategory === category.id,
                    'btn-light': posStore.selectedCategory !== category.id
                }"
            >
                <i :class="getCategoryIcon(category.name)" class="me-2"></i>
                {{ category.name }}
                <span class="badge badge-circle badge-white ms-2" v-if="category.product_count">
                    {{ category.product_count }}
                </span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePosStore } from '../posStore';

const posStore = usePosStore();

const topCategories = computed(() => {
    // Show top 8 categories for quick access
    return posStore.categories.slice(0, 8);
});

const selectCategory = (categoryId) => {
    posStore.selectedCategory = categoryId || '';
    posStore.filterByCategory();
};

const getCategoryIcon = (categoryName) => {
    // Map category names to appropriate icons
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
        if (categoryName.includes(key)) {
            return icon;
        }
    }
    
    return 'fas fa-tag';
};
</script>

<style lang="scss" scoped>
.pos-quick-categories {
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
        
        &:hover {
            background: #555;
        }
    }
    
    .btn {
        white-space: nowrap;
        transition: all 0.15s ease;

        &:hover {
            border-color: var(--bs-primary);
        }
        
        &.active, &.btn-primary {
            .badge-circle {
                background: white !important;
                color: var(--bs-primary) !important;
            }
        }
    }
    
    .badge-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        font-size: 0.75rem;
        background: var(--bs-gray-400);
        color: white;
    }
}
</style>