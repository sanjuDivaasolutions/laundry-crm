<template>
    <div class="pos-customer-select card shadow-sm mb-3">
        <div class="card-body py-3">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-bold mb-0">
                    <i class="fas fa-user me-2 text-primary"></i>Customer
                </label>
                <button
                    @click="showAddModal = true"
                    class="btn btn-sm btn-light-primary"
                    title="Add New Customer"
                >
                    <i class="fas fa-plus me-1"></i> New
                </button>
            </div>

            <!-- Customer Search -->
            <div class="customer-search-wrapper">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="searchCustomers"
                        @focus="showDropdown = true"
                        @blur="handleBlur"
                        class="form-control"
                        placeholder="Search customer by name, phone, email..."
                        autocomplete="off"
                    />
                    <button
                        v-if="selectedCustomer"
                        @click="clearCustomer"
                        class="btn-clear"
                        title="Clear selection"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                    <div v-if="searching" class="search-spinner">
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>
                </div>

                <!-- Selected Customer Display -->
                <div v-if="selectedCustomer && !showDropdown" class="selected-customer">
                    <div class="customer-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="customer-details">
                        <div class="customer-name">{{ selectedCustomer.name }}</div>
                        <div class="customer-contact">
                            <span v-if="selectedCustomer.phone">
                                <i class="fas fa-phone me-1"></i>{{ selectedCustomer.phone }}
                            </span>
                            <span v-if="selectedCustomer.email" class="ms-2">
                                <i class="fas fa-envelope me-1"></i>{{ selectedCustomer.email }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Search Results Dropdown -->
                <div v-if="showDropdown && (customers.length > 0 || searchQuery)" class="search-dropdown">
                    <!-- Walk-in Customer Option -->
                    <div
                        @mousedown.prevent="selectWalkIn"
                        class="dropdown-item walk-in-option"
                        :class="{ 'active': !selectedCustomer }"
                    >
                        <div class="item-avatar walk-in">
                            <i class="fas fa-walking"></i>
                        </div>
                        <div class="item-info">
                            <div class="item-name">Walk-in Customer</div>
                            <div class="item-meta">No customer information required</div>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div
                        v-for="customer in customers"
                        :key="customer.id"
                        @mousedown.prevent="selectCustomer(customer)"
                        class="dropdown-item"
                        :class="{ 'active': selectedCustomer?.id === customer.id }"
                    >
                        <div class="item-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="item-info">
                            <div class="item-name">{{ customer.name }}</div>
                            <div class="item-meta">
                                <span v-if="customer.phone">{{ customer.phone }}</span>
                                <span v-if="customer.email" class="ms-2">{{ customer.email }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- No Results -->
                    <div v-if="customers.length === 0 && searchQuery && !searching" class="dropdown-empty">
                        <i class="fas fa-search mb-2"></i>
                        <p>No customers found</p>
                        <button @mousedown.prevent="openAddWithSearch" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Add "{{ searchQuery }}" as new customer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Prescription Warning -->
            <div v-if="posStore.hasRxMedicines" class="rx-warning mt-3">
                <div class="rx-warning-content">
                    <i class="fas fa-prescription rx-icon"></i>
                    <div class="rx-text">
                        <strong>Prescription Required!</strong>
                        <p>This order contains prescription medicines.</p>
                    </div>
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            v-model="posStore.prescriptionVerified"
                            id="prescriptionCheck"
                        />
                        <label class="form-check-label" for="prescriptionCheck">Verified</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Customer Modal -->
        <Teleport to="body">
            <div v-if="showAddModal" class="modal-backdrop" @click.self="closeModal">
                <div class="add-customer-modal">
                    <div class="modal-header">
                        <h5><i class="fas fa-user-plus me-2"></i>Add New Customer</h5>
                        <button @click="closeModal" class="btn-close-modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                v-model="newCustomer.name"
                                class="form-control"
                                :class="{ 'is-invalid': errors.name }"
                                placeholder="Enter customer name"
                                ref="nameInput"
                            />
                            <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input
                                type="text"
                                v-model="newCustomer.phone"
                                class="form-control"
                                placeholder="Enter phone number"
                            />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                v-model="newCustomer.email"
                                class="form-control"
                                :class="{ 'is-invalid': errors.email }"
                                placeholder="Enter email address"
                            />
                            <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="closeModal" class="btn btn-light">Cancel</button>
                        <button @click="saveCustomer" class="btn btn-primary" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas fa-save me-1"></i>
                            Save & Select
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, reactive, nextTick, watch } from 'vue';
import { usePosStore } from '../posStore';
import axios from 'axios';

const posStore = usePosStore();

// State
const searchQuery = ref('');
const customers = ref([]);
const selectedCustomer = ref(null);
const showDropdown = ref(false);
const searching = ref(false);
const showAddModal = ref(false);
const saving = ref(false);
const nameInput = ref(null);

const newCustomer = reactive({
    name: '',
    phone: '',
    email: ''
});

const errors = reactive({
    name: '',
    email: ''
});

let searchTimeout = null;

// Methods
const searchCustomers = () => {
    clearTimeout(searchTimeout);

    if (!searchQuery.value || searchQuery.value.length < 2) {
        customers.value = [];
        return;
    }

    searching.value = true;

    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get('/pos/customers', {
                params: { search: searchQuery.value }
            });

            if (response.data.success) {
                customers.value = response.data.data;
            }
        } catch (error) {
            console.error('Error searching customers:', error);
            customers.value = [];
        } finally {
            searching.value = false;
        }
    }, 300);
};

const selectCustomer = (customer) => {
    selectedCustomer.value = customer;
    posStore.selectedCustomer = customer.id;
    searchQuery.value = customer.name;
    showDropdown.value = false;
};

const selectWalkIn = () => {
    selectedCustomer.value = null;
    posStore.selectedCustomer = null;
    searchQuery.value = '';
    showDropdown.value = false;
};

const clearCustomer = () => {
    selectedCustomer.value = null;
    posStore.selectedCustomer = null;
    searchQuery.value = '';
    customers.value = [];
};

const handleBlur = () => {
    setTimeout(() => {
        showDropdown.value = false;
    }, 200);
};

const openAddWithSearch = () => {
    newCustomer.name = searchQuery.value;
    showAddModal.value = true;
    showDropdown.value = false;
    nextTick(() => {
        nameInput.value?.focus();
    });
};

const closeModal = () => {
    showAddModal.value = false;
    newCustomer.name = '';
    newCustomer.phone = '';
    newCustomer.email = '';
    errors.name = '';
    errors.email = '';
};

const validateForm = () => {
    let isValid = true;
    errors.name = '';
    errors.email = '';

    if (!newCustomer.name.trim()) {
        errors.name = 'Name is required';
        isValid = false;
    }

    if (newCustomer.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newCustomer.email)) {
        errors.email = 'Invalid email format';
        isValid = false;
    }

    return isValid;
};

const saveCustomer = async () => {
    if (!validateForm()) return;

    saving.value = true;

    try {
        const response = await axios.post('/pos/customers', {
            name: newCustomer.name.trim(),
            phone: newCustomer.phone.trim() || null,
            email: newCustomer.email.trim() || null
        });

        if (response.data.success) {
            const customer = response.data.data;
            selectCustomer(customer);
            closeModal();
            posStore.success = `Customer "${customer.name}" created successfully`;
        }
    } catch (error) {
        console.error('Error creating customer:', error);
        if (error.response?.data?.errors) {
            const serverErrors = error.response.data.errors;
            if (serverErrors.name) errors.name = serverErrors.name[0];
            if (serverErrors.email) errors.email = serverErrors.email[0];
        } else {
            posStore.error = 'Failed to create customer';
        }
    } finally {
        saving.value = false;
    }
};

// Watch for modal open to focus input
watch(showAddModal, (val) => {
    if (val) {
        nextTick(() => {
            nameInput.value?.focus();
        });
    }
});
</script>

<style lang="scss" scoped>
.pos-customer-select {
    .form-label {
        font-size: 0.9rem;
    }
}

// Customer Search
.customer-search-wrapper {
    position: relative;
}

.search-input-group {
    position: relative;
    display: flex;
    align-items: center;

    .search-icon {
        position: absolute;
        left: 12px;
        color: #999;
        font-size: 0.9rem;
        z-index: 1;
    }

    .form-control {
        padding-left: 38px;
        padding-right: 70px;
        height: 42px;
        border-radius: 8px;
        font-size: 0.9rem;

        &:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.1);
        }
    }

    .btn-clear {
        position: absolute;
        right: 40px;
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        padding: 5px;

        &:hover {
            color: #dc3545;
        }
    }

    .search-spinner {
        position: absolute;
        right: 12px;
    }
}

// Selected Customer Display
.selected-customer {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    margin-top: 0.5rem;
    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05) 0%, rgba(var(--bs-primary-rgb), 0.1) 100%);
    border-radius: 8px;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.2);

    .customer-avatar {
        width: 40px;
        height: 40px;
        background: var(--bs-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .customer-details {
        flex: 1;
    }

    .customer-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #333;
    }

    .customer-contact {
        font-size: 0.8rem;
        color: #666;

        i {
            font-size: 0.7rem;
        }
    }
}

// Search Dropdown
.search-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    margin-top: 4px;

    &::-webkit-scrollbar {
        width: 6px;
    }

    &::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background-color 0.15s;
    border-bottom: 1px solid #f0f0f0;

    &:last-child {
        border-bottom: none;
    }

    &:hover {
        background-color: #f8f9fa;
    }

    &.active {
        background-color: rgba(var(--bs-primary-rgb), 0.1);

        .item-name {
            color: var(--bs-primary);
        }
    }

    &.walk-in-option {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;

        .item-avatar.walk-in {
            background: #6c757d;
        }
    }

    .item-avatar {
        width: 36px;
        height: 36px;
        background: var(--bs-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .item-info {
        flex: 1;
        min-width: 0;
    }

    .item-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: #333;
    }

    .item-meta {
        font-size: 0.8rem;
        color: #666;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
}

.dropdown-empty {
    padding: 1.5rem;
    text-align: center;
    color: #666;

    i {
        font-size: 1.5rem;
        opacity: 0.5;
    }

    p {
        margin: 0.5rem 0;
        font-size: 0.9rem;
    }
}

// Prescription Warning
.rx-warning {
    background: linear-gradient(135deg, #fff8e6 0%, #fff3cd 100%);
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 0.75rem 1rem;
}

.rx-warning-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;

    .rx-icon {
        font-size: 1.5rem;
        color: #856404;
    }

    .rx-text {
        flex: 1;

        strong {
            display: block;
            color: #856404;
            font-size: 0.9rem;
        }

        p {
            margin: 0;
            font-size: 0.8rem;
            color: #856404;
        }
    }

    .form-check {
        margin: 0;
    }

    .form-check-input {
        width: 3rem;
        height: 1.5rem;
        cursor: pointer;

        &:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
    }
}

// Modal
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    backdrop-filter: blur(2px);
}

.add-customer-modal {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    animation: modalSlideIn 0.2s ease;

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e9ecef;

        h5 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .btn-close-modal {
            background: none;
            border: none;
            font-size: 1.1rem;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            line-height: 1;

            &:hover {
                color: #dc3545;
            }
        }
    }

    .modal-body {
        padding: 1.25rem;

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-control {
            height: 42px;
            border-radius: 8px;

            &:focus {
                border-color: var(--bs-primary);
                box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.1);
            }
        }
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 1rem 1.25rem;
        border-top: 1px solid #e9ecef;
        background: #f8f9fa;
        border-radius: 0 0 12px 12px;
    }
}
</style>
