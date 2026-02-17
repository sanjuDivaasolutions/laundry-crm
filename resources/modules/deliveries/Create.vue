<template>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Schedule Delivery/Pickup</h2>
            </div>
            <div class="card-toolbar">
                <router-link :to="{ name: 'deliveries.index' }" class="btn btn-light-danger">
                    <i class="bi bi-arrow-left me-2"></i> Back
                </router-link>
            </div>
        </div>
        <div class="card-body">
            <form @submit.prevent="submitForm">
                <div class="row mb-8">
                    <div class="col-md-4 mb-5">
                        <label class="form-label required">Type</label>
                        <select v-model="form.type" class="form-select" :class="{ 'is-invalid': errors.type }">
                            <option value="pickup">Pickup</option>
                            <option value="delivery">Delivery</option>
                        </select>
                        <div v-if="errors.type" class="invalid-feedback">{{ errors.type[0] }}</div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label required">Scheduled Date</label>
                        <input v-model="form.scheduled_date" type="date" class="form-control" :class="{ 'is-invalid': errors.scheduled_date }" />
                        <div v-if="errors.scheduled_date" class="invalid-feedback">{{ errors.scheduled_date[0] }}</div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label">Scheduled Time</label>
                        <input v-model="form.scheduled_time" type="time" class="form-control" />
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Order</label>
                        <select v-model="form.order_id" class="form-select">
                            <option :value="null">Select Order (optional)</option>
                            <option v-for="o in orders" :key="o.id" :value="o.id">{{ o.order_number }}</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Customer</label>
                        <select v-model="form.customer_id" class="form-select">
                            <option :value="null">Select Customer (optional)</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.phone }})</option>
                        </select>
                    </div>
                    <div class="col-12 mb-5">
                        <label class="form-label">Address</label>
                        <textarea v-model="form.address" class="form-control" rows="2" placeholder="Delivery/Pickup address"></textarea>
                    </div>
                    <div class="col-12 mb-5">
                        <label class="form-label">Notes</label>
                        <textarea v-model="form.notes" class="form-control" rows="2" placeholder="Additional notes"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <router-link :to="{ name: 'deliveries.index' }" class="btn btn-light">Cancel</router-link>
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-check-lg me-2"></i>
                        Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import ApiService from "@/core/services/ApiService";
import { $toastSuccess, $toastError } from "@/core/helpers/utility";

const router = useRouter();
const loading = ref(false);
const errors = ref({});
const orders = ref([]);
const customers = ref([]);

const form = reactive({
    order_id: null,
    customer_id: null,
    type: "delivery",
    scheduled_date: new Date().toISOString().split("T")[0],
    scheduled_time: "",
    address: "",
    notes: "",
});

const loadFormData = async () => {
    try {
        const [ordersRes, customersRes] = await Promise.all([
            ApiService.get("options/orders"),
            ApiService.get("options/customers"),
        ]);
        orders.value = ordersRes.data.data || [];
        customers.value = customersRes.data.data || [];
    } catch (error) {
        console.error("Error loading form data:", error);
    }
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        await ApiService.post("deliveries", { ...form }, false);
        $toastSuccess("Delivery scheduled successfully");
        router.push({ name: "deliveries.index" });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            $toastError("Please fix the validation errors");
        } else {
            $toastError(error.response?.data?.message || "Failed to schedule delivery");
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadFormData();
});
</script>
