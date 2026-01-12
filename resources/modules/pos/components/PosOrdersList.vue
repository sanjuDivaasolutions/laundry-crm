<template>
    <div class="pos-orders-list">
        <!-- Header with Filters -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-receipt me-2 text-primary"></i>Today's Orders
                    </h6>
                    <button @click="$emit('close')" class="btn btn-sm btn-light">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body py-3">
                <div class="row g-2">
                    <!-- Search -->
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="text"
                                v-model="filters.search"
                                @input="debouncedSearch"
                                class="form-control"
                                placeholder="Invoice # or Customer..."
                            />
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div class="col-md-3">
                        <select v-model="filters.dateRange" @change="loadOrders" class="form-select form-select-sm">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="all">All Time</option>
                        </select>
                    </div>

                    <!-- Payment Status Filter -->
                    <div class="col-md-3">
                        <select v-model="filters.paymentStatus" @change="loadOrders" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                        </select>
                    </div>

                    <!-- Refresh -->
                    <div class="col-md-2">
                        <button @click="loadOrders" class="btn btn-sm btn-light w-100" :disabled="loading">
                            <i class="fas fa-sync" :class="{ 'fa-spin': loading }"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <!-- Loading State -->
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading orders...</p>
                </div>

                <!-- Orders List -->
                <div v-else-if="orders.length > 0" class="orders-table-wrapper">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 140px;">Invoice #</th>
                                <th style="width: 80px;">Time</th>
                                <th>Customer</th>
                                <th style="width: 60px;" class="text-center">Items</th>
                                <th style="width: 100px;" class="text-end">Total</th>
                                <th style="width: 90px;" class="text-center">Status</th>
                                <th style="width: 80px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="order in orders"
                                :key="order.id"
                                class="order-row"
                                @click="viewOrder(order)"
                            >
                                <td>
                                    <span class="fw-semibold text-primary">{{ order.invoice_number }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ order.time }}</small>
                                </td>
                                <td>
                                    <div v-if="order.customer">
                                        <span class="fw-medium">{{ order.customer.name }}</span>
                                        <br>
                                        <small class="text-muted">{{ order.customer.phone }}</small>
                                    </div>
                                    <span v-else class="text-muted">Walk-in Customer</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">{{ order.items_count }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">${{ formatCurrency(order.grand_total) }}</span>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge"
                                        :class="getStatusBadgeClass(order.payment_status)"
                                    >
                                        {{ formatStatus(order.payment_status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button
                                            @click.stop="viewOrder(order)"
                                            class="btn btn-light"
                                            title="View Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button
                                            @click.stop="printOrder(order)"
                                            class="btn btn-light"
                                            title="Print Receipt"
                                        >
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-5">
                    <div class="empty-icon mb-3">
                        <i class="fas fa-receipt text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-muted">No orders found</h6>
                    <p class="text-muted small mb-0">Orders will appear here after completing sales</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > pagination.per_page" class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }}
                        to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
                        of {{ pagination.total }} orders
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                <button class="page-link" @click="goToPage(pagination.current_page - 1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                            </li>
                            <li
                                v-for="page in visiblePages"
                                :key="page"
                                class="page-item"
                                :class="{ active: page === pagination.current_page }"
                            >
                                <button class="page-link" @click="goToPage(page)">{{ page }}</button>
                            </li>
                            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                <button class="page-link" @click="goToPage(pagination.current_page + 1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Order Details Modal -->
        <PosOrderDetailsModal
            v-if="selectedOrder"
            :order="selectedOrder"
            :loading="loadingDetails"
            @close="selectedOrder = null"
            @print="printOrder"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import PosOrderDetailsModal from './PosOrderDetailsModal.vue';

const emit = defineEmits(['close']);

// State
const orders = ref([]);
const loading = ref(false);
const loadingDetails = ref(false);
const selectedOrder = ref(null);
const filters = ref({
    search: '',
    dateRange: 'today',
    paymentStatus: ''
});
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0
});

let searchTimeout = null;

// Computed
const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    let start = Math.max(1, current - 2);
    let end = Math.min(last, current + 2);

    if (end - start < 4) {
        if (start === 1) {
            end = Math.min(last, start + 4);
        } else {
            start = Math.max(1, end - 4);
        }
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return pages;
});

// Methods
const loadOrders = async (page = 1) => {
    loading.value = true;

    try {
        const params = {
            page,
            per_page: pagination.value.per_page
        };

        // Add search
        if (filters.value.search) {
            params.search = filters.value.search;
        }

        // Add payment status
        if (filters.value.paymentStatus) {
            params.payment_status = filters.value.paymentStatus;
        }

        // Add date range
        const today = new Date();
        switch (filters.value.dateRange) {
            case 'today':
                // Default - API returns today's orders
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                params.date_from = formatDateForApi(yesterday);
                params.date_to = formatDateForApi(yesterday);
                break;
            case 'week':
                const weekStart = new Date(today);
                weekStart.setDate(weekStart.getDate() - 7);
                params.date_from = formatDateForApi(weekStart);
                params.date_to = formatDateForApi(today);
                break;
            case 'month':
                const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                params.date_from = formatDateForApi(monthStart);
                params.date_to = formatDateForApi(today);
                break;
            case 'all':
                params.show_all = true;
                break;
        }

        const response = await axios.get('/pos/orders', { params });

        if (response.data.success) {
            orders.value = response.data.data;
            pagination.value = response.data.pagination;
        }
    } catch (error) {
        console.error('Error loading orders:', error);
    } finally {
        loading.value = false;
    }
};

const viewOrder = async (order) => {
    loadingDetails.value = true;
    selectedOrder.value = order; // Show modal immediately with basic data

    try {
        const response = await axios.get(`/pos/orders/${order.id}`);
        if (response.data.success) {
            selectedOrder.value = response.data.data;
        }
    } catch (error) {
        console.error('Error loading order details:', error);
    } finally {
        loadingDetails.value = false;
    }
};

const printOrder = (order) => {
    // TODO: Implement print functionality
    window.print();
};

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        loadOrders(page);
    }
};

const debouncedSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadOrders(1);
    }, 300);
};

const formatCurrency = (amount) => {
    return Number(amount || 0).toFixed(2);
};

const formatDateForApi = (date) => {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
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

// Lifecycle
onMounted(() => {
    loadOrders();
});
</script>

<style lang="scss" scoped>
.pos-orders-list {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.orders-table-wrapper {
    max-height: 400px;
    overflow-y: auto;

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

.order-row {
    cursor: pointer;
    transition: background-color 0.2s;

    &:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
    }
}

.table {
    th {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-bottom-width: 1px;
    }

    td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
}

.badge {
    font-weight: 500;
    font-size: 0.75rem;
}

.pagination {
    .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
}
</style>
