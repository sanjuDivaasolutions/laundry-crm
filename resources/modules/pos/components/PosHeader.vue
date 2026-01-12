<template>
    <div class="pos-header bg-primary text-white shadow-sm">
        <div class="d-flex align-items-center justify-content-between px-4 py-2">
            <!-- Left Section -->
            <div class="d-flex align-items-center">
                <div class="symbol symbol-50px me-3">
                    <div class="symbol-label bg-white-o-20">
                        <i class="fas fa-cash-register text-white fs-1"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-white mb-0 fw-bold">Medical POS</h3>
                    <small class="text-white-75">{{ currentDateTime }}</small>
                </div>
            </div>
            
            <!-- Center Section - Stats -->
            <div class="d-flex align-items-center gap-4">
                <div class="text-center">
                    <div class="text-white-75 fs-7">Today's Sales</div>
                    <div class="text-white fw-semibold fs-5">${{ formatCurrency(todaySales) }}</div>
                </div>
                <div class="text-center">
                    <div class="text-white-75 fs-7">Transactions</div>
                    <div class="text-white fw-semibold fs-5">{{ transactionCount }}</div>
                </div>
                <div class="text-center">
                    <div class="text-white-75 fs-7">Cart Items</div>
                    <div class="text-white fw-semibold fs-5">{{ posStore.cartItemsCount }}</div>
                </div>
            </div>
            
            <!-- Right Section -->
            <div class="d-flex align-items-center gap-3">
                <!-- Orders Button -->
                <button
                    @click="$emit('toggle-orders')"
                    class="btn btn-header"
                    :class="{ 'active': posStore.showOrders }"
                    title="View Orders (F3)"
                >
                    <i class="fas fa-receipt me-2"></i>
                    <span class="d-none d-md-inline">Orders</span>
                </button>

                <!-- Shortcuts Info -->
                <div class="d-none d-xl-block">
                    <span class="badge badge-white-20 me-2">F1: Search</span>
                    <span class="badge badge-white-20 me-2">F2: Scanner</span>
                    <span class="badge badge-white-20 me-2">F3: Orders</span>
                    <span class="badge badge-white-20 me-2">F10: Pay</span>
                </div>

                <!-- User Info -->
                <div class="d-flex align-items-center">
                    <div class="text-end me-3">
                        <div class="text-white fw-bold">{{ userName }}</div>
                        <div class="text-white-75 fs-7">{{ posStore.currentTill }}</div>
                    </div>
                    <div class="symbol symbol-35px">
                        <div class="symbol-label bg-white-o-20">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { usePosStore } from '../posStore';
import { useAuthStore } from '@/stores/auth';

defineEmits(['toggle-orders']);

const posStore = usePosStore();
const authStore = useAuthStore();

const currentDateTime = ref('');
const todaySales = ref(0);
const transactionCount = ref(0);

const userName = computed(() => {
    return authStore.user?.name || 'Guest User';
});

const formatCurrency = (amount) => {
    return Number(amount).toFixed(2);
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

let interval;

onMounted(() => {
    updateDateTime();
    interval = setInterval(updateDateTime, 60000); // Update every minute
    
    // Load today's stats
    loadTodayStats();
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});

const loadTodayStats = async () => {
    try {
        const response = await axios.get('/pos/sales/summary');
        if (response.data.success) {
            todaySales.value = response.data.data.total_sales || 0;
            transactionCount.value = response.data.data.total_transactions || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
};
</script>

<style lang="scss" scoped>
.pos-header {
    background-color: var(--bs-primary); // Use theme primary color

    .btn-header {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.2s;

        &:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        &.active {
            background: white;
            color: var(--bs-primary);
            border-color: white;
        }
    }

    .badge-white-20 {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 0.3rem 0.5rem;
        font-size: 0.75rem;
    }

    .bg-white-o-20 {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    .text-white-75 {
        color: rgba(255, 255, 255, 0.75) !important;
    }
}
</style>