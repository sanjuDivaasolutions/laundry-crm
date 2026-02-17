<template>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Create New Order</h2>
            </div>
            <div class="card-toolbar">
                <router-link :to="{ name: 'orders.index' }" class="btn btn-light-danger">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Orders
                </router-link>
            </div>
        </div>
        <div class="card-body">
            <form @submit.prevent="submitForm">
                <!-- Customer & Order Info -->
                <div class="row mb-8">
                    <div class="col-12">
                        <h4 class="mb-5 text-gray-800">Order Information</h4>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">Customer</label>
                        <select
                            v-model="form.customer_id"
                            class="form-select"
                            :class="{ 'is-invalid': errors.customer_id }"
                        >
                            <option :value="null">Select Customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">
                                {{ c.name }} ({{ c.phone }})
                            </option>
                        </select>
                        <div v-if="errors.customer_id" class="invalid-feedback">{{ errors.customer_id[0] }}</div>
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label required">Order Date</label>
                        <input
                            v-model="form.order_date"
                            type="date"
                            class="form-control"
                            :class="{ 'is-invalid': errors.order_date }"
                        />
                        <div v-if="errors.order_date" class="invalid-feedback">{{ errors.order_date[0] }}</div>
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label">Promised Date</label>
                        <input
                            v-model="form.promised_date"
                            type="date"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label">Urgent</label>
                        <div class="form-check form-switch mt-3">
                            <input
                                v-model="form.urgent"
                                type="checkbox"
                                class="form-check-input"
                                id="urgent"
                            />
                            <label class="form-check-label" for="urgent">
                                {{ form.urgent ? 'Yes - Rush Order' : 'No' }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-9 mb-5">
                        <label class="form-label">Notes</label>
                        <input
                            v-model="form.notes"
                            type="text"
                            class="form-control"
                            placeholder="Special instructions..."
                        />
                    </div>
                </div>

                <!-- Order Items -->
                <div class="row mb-8">
                    <div class="col-12">
                        <div class="separator my-5"></div>
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <h4 class="text-gray-800 mb-0">
                                <i class="bi bi-list-check me-2"></i>
                                Order Items
                            </h4>
                            <button type="button" class="btn btn-sm btn-light-primary" @click="addItem">
                                <i class="bi bi-plus-lg me-1"></i> Add Item
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="ps-4" style="width: 200px;">Item</th>
                                        <th style="width: 200px;">Service</th>
                                        <th style="width: 80px;">Qty</th>
                                        <th style="width: 120px;">Unit Price</th>
                                        <th style="width: 120px;">Total</th>
                                        <th style="width: 120px;">Color</th>
                                        <th style="width: 120px;">Brand</th>
                                        <th style="width: 60px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in form.items" :key="index">
                                        <td class="ps-4">
                                            <select v-model="item.item_id" class="form-select form-select-sm" @change="onItemChange(index)">
                                                <option :value="null">Select</option>
                                                <option v-for="it in items" :key="it.id" :value="it.id">{{ it.name }}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select v-model="item.service_id" class="form-select form-select-sm" @change="onServiceChange(index)">
                                                <option :value="null">Select</option>
                                                <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input v-model.number="item.quantity" type="number" min="1" class="form-control form-control-sm" />
                                        </td>
                                        <td>
                                            <input v-model.number="item.unit_price" type="number" step="0.01" min="0" class="form-control form-control-sm" />
                                        </td>
                                        <td class="fw-semibold text-end">
                                            {{ formatAmount(item.quantity * item.unit_price) }}
                                        </td>
                                        <td>
                                            <input v-model="item.color" type="text" class="form-control form-control-sm" placeholder="Color" />
                                        </td>
                                        <td>
                                            <input v-model="item.brand" type="text" class="form-control form-control-sm" placeholder="Brand" />
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-icon btn-light-danger" @click="removeItem(index)" v-if="form.items.length > 1">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.items.length === 0">
                                        <td colspan="8" class="text-center text-muted py-5">
                                            No items added. Click "Add Item" to start.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="errors.items" class="text-danger small mt-1">{{ errors.items[0] }}</div>
                    </div>
                </div>

                <!-- Discount & Tax -->
                <div class="row mb-8">
                    <div class="col-12">
                        <div class="separator my-5"></div>
                        <h4 class="mb-5 text-gray-800">Pricing</h4>
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label">Discount Type</label>
                        <select v-model="form.discount_type" class="form-select">
                            <option value="flat">Flat Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label">Discount</label>
                        <input v-model.number="form.discount_amount" type="number" step="0.01" min="0" class="form-control" />
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label">Tax Rate (%)</label>
                        <input v-model.number="form.tax_rate" type="number" step="0.01" min="0" class="form-control" />
                    </div>
                    <div class="col-md-3 mb-5">
                        <div class="bg-light-primary rounded p-4 mt-7">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span class="fw-semibold">{{ formatAmount(subtotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Discount:</span>
                                <span class="fw-semibold text-danger">-{{ formatAmount(discountValue) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tax:</span>
                                <span class="fw-semibold">{{ formatAmount(taxValue) }}</span>
                            </div>
                            <div class="separator my-2"></div>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold fs-5">Total:</span>
                                <span class="fw-bold fs-5 text-primary">{{ formatAmount(total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-end gap-3">
                    <router-link :to="{ name: 'orders.index' }" class="btn btn-light">Cancel</router-link>
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-check-lg me-2"></i>
                        Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import ApiService from "@/core/services/ApiService";
import { $toastSuccess, $toastError } from "@/core/helpers/utility";

const router = useRouter();
const loading = ref(false);
const errors = ref({});
const customers = ref([]);
const items = ref([]);
const services = ref([]);

const form = reactive({
    customer_id: null,
    order_date: new Date().toISOString().split("T")[0],
    promised_date: "",
    urgent: false,
    notes: "",
    discount_type: "flat",
    discount_amount: 0,
    tax_rate: 0,
    items: [{ item_id: null, service_id: null, quantity: 1, unit_price: 0, color: "", brand: "" }],
});

const formatAmount = (val) => {
    return parseFloat(val || 0).toFixed(2);
};

const subtotal = computed(() => {
    return form.items.reduce((sum, i) => sum + (i.quantity || 0) * (i.unit_price || 0), 0);
});

const discountValue = computed(() => {
    if (form.discount_type === "percentage") {
        return subtotal.value * ((form.discount_amount || 0) / 100);
    }
    return form.discount_amount || 0;
});

const taxValue = computed(() => {
    return (subtotal.value - discountValue.value) * ((form.tax_rate || 0) / 100);
});

const total = computed(() => {
    return subtotal.value - discountValue.value + taxValue.value;
});

const addItem = () => {
    form.items.push({ item_id: null, service_id: null, quantity: 1, unit_price: 0, color: "", brand: "" });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const onItemChange = (index) => {
    const item = items.value.find((i) => i.id === form.items[index].item_id);
    if (item && item.price) {
        form.items[index].unit_price = parseFloat(item.price);
    }
};

const onServiceChange = (index) => {
    // Price might come from service-item pricing
};

const loadFormData = async () => {
    try {
        const [customersRes, itemsRes, servicesRes] = await Promise.all([
            ApiService.get("options/customers"),
            ApiService.get("options/items"),
            ApiService.get("options/services"),
        ]);
        customers.value = customersRes.data.data || [];
        items.value = itemsRes.data.data || [];
        services.value = servicesRes.data.data || [];
    } catch (error) {
        console.error("Error loading form data:", error);
    }
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        await ApiService.post("orders", { ...form }, false);
        $toastSuccess("Order created successfully");
        router.push({ name: "orders.index" });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            $toastError("Please fix the validation errors");
        } else {
            $toastError(error.response?.data?.message || "Failed to create order");
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadFormData();
});
</script>

<style scoped>
.separator {
    border-bottom: 1px dashed #e4e6ef;
}
</style>
