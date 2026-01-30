<template>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Create New Item</h2>
            </div>
            <div class="card-toolbar">
                <router-link :to="{ name: 'items.index' }" class="btn btn-light-danger">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to List
                </router-link>
            </div>
        </div>
        <div class="card-body">
            <form @submit.prevent="submitForm">
                <!-- Basic Information -->
                <div class="row mb-8">
                    <div class="col-12">
                        <h4 class="mb-5 text-gray-800">Basic Information</h4>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">Item Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.name }"
                            placeholder="Enter item name (e.g., Shirt, Pants)"
                        />
                        <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">Default Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control"
                                :class="{ 'is-invalid': errors.price }"
                                placeholder="0.00"
                            />
                        </div>
                        <div v-if="errors.price" class="text-danger small mt-1">{{ errors.price[0] }}</div>
                        <div class="form-text">Used when no service-specific price is set</div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label">Display Order</label>
                        <input
                            v-model="form.display_order"
                            type="number"
                            min="0"
                            class="form-control"
                            placeholder="0"
                        />
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch mt-3">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="form-check-input"
                                id="is_active"
                            />
                            <label class="form-check-label" for="is_active">
                                {{ form.is_active ? 'Active' : 'Inactive' }}
                            </label>
                        </div>
                    </div>
                    <div class="col-12 mb-5">
                        <label class="form-label">Description</label>
                        <textarea
                            v-model="form.description"
                            class="form-control"
                            rows="3"
                            placeholder="Enter description (optional)"
                        ></textarea>
                    </div>
                </div>

                <!-- Service Prices Section -->
                <div class="row mb-8" v-if="services.length > 0">
                    <div class="col-12">
                        <div class="separator my-5"></div>
                        <h4 class="mb-5 text-gray-800">
                            <i class="bi bi-tags me-2"></i>
                            Service Prices
                        </h4>
                        <p class="text-muted mb-5">
                            Set specific prices for each service. Leave empty to use the default price.
                        </p>
                    </div>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="ps-4" style="width: 50px;">
                                            <div class="form-check">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    @change="toggleAllServices"
                                                    :checked="allServicesChecked"
                                                />
                                            </div>
                                        </th>
                                        <th>Service</th>
                                        <th style="width: 200px;">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="service in services" :key="service.id">
                                        <td class="ps-4">
                                            <div class="form-check">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input"
                                                    v-model="servicePriceEnabled[service.id]"
                                                    @change="onServiceToggle(service.id)"
                                                />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-35px symbol-circle bg-light-primary me-3">
                                                    <span class="symbol-label text-primary fw-bold">
                                                        {{ service.name.charAt(0) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-gray-800">{{ service.name }}</span>
                                                    <span class="text-muted d-block fs-7">{{ service.code }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">$</span>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control"
                                                    v-model="servicePrices[service.id]"
                                                    :disabled="!servicePriceEnabled[service.id]"
                                                    :placeholder="form.price ? form.price.toString() : '0.00'"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Alert if no services -->
                <div v-else class="alert alert-warning d-flex align-items-center mb-8">
                    <i class="bi bi-exclamation-triangle fs-2 me-3"></i>
                    <div>
                        <h5 class="mb-1">No Services Available</h5>
                        <p class="mb-0">Please create services first before setting service-specific prices.</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-3">
                    <router-link :to="{ name: 'items.index' }" class="btn btn-light">
                        Cancel
                    </router-link>
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-check-lg me-2"></i>
                        Create Item
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
const services = ref([]);

const form = reactive({
    name: "",
    description: "",
    price: 0,
    display_order: 0,
    is_active: true,
});

const servicePrices = reactive({});
const servicePriceEnabled = reactive({});

const allServicesChecked = computed(() => {
    return services.value.length > 0 && services.value.every(s => servicePriceEnabled[s.id]);
});

const toggleAllServices = (event) => {
    const checked = event.target.checked;
    services.value.forEach(service => {
        servicePriceEnabled[service.id] = checked;
        if (!checked) {
            servicePrices[service.id] = null;
        }
    });
};

const onServiceToggle = (serviceId) => {
    if (!servicePriceEnabled[serviceId]) {
        servicePrices[serviceId] = null;
    }
};

const loadFormData = async () => {
    try {
        // Load services from create endpoint
        const response = await ApiService.get("items/create");
        services.value = response.data.meta.services || [];

        // Initialize service prices
        services.value.forEach(service => {
            servicePrices[service.id] = null;
            servicePriceEnabled[service.id] = false;
        });
    } catch (error) {
        console.error("Error loading form data:", error);
        $toastError("Failed to load form data");
    }
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        // Build service_prices array
        const service_prices = [];
        for (const service of services.value) {
            if (servicePriceEnabled[service.id] && servicePrices[service.id] !== null && servicePrices[service.id] !== "") {
                service_prices.push({
                    service_id: service.id,
                    price: parseFloat(servicePrices[service.id]),
                    is_active: true,
                });
            }
        }

        const payload = {
            ...form,
            service_prices,
        };

        await ApiService.post("items", payload, false);
        $toastSuccess("Item created successfully");
        router.push({ name: "items.index" });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            $toastError("Please fix the validation errors");
        } else {
            $toastError(error.response?.data?.message || "Failed to create item");
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
