<template>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Edit Delivery Schedule</h2>
            </div>
            <div class="card-toolbar">
                <router-link :to="{ name: 'deliveries.index' }" class="btn btn-light-danger">
                    <i class="bi bi-arrow-left me-2"></i> Back
                </router-link>
            </div>
        </div>
        <div class="card-body">
            <div v-if="pageLoading" class="d-flex justify-content-center py-10">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <form v-else @submit.prevent="submitForm">
                <div class="row mb-8">
                    <div class="col-md-4 mb-5">
                        <label class="form-label required">Type</label>
                        <select v-model="form.type" class="form-select">
                            <option value="pickup">Pickup</option>
                            <option value="delivery">Delivery</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label required">Scheduled Date</label>
                        <input v-model="form.scheduled_date" type="date" class="form-control" />
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label">Scheduled Time</label>
                        <input v-model="form.scheduled_time" type="time" class="form-control" />
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label">Status</label>
                        <select v-model="form.status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="in_transit">In Transit</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-8 mb-5">
                        <label class="form-label">Address</label>
                        <input v-model="form.address" type="text" class="form-control" placeholder="Delivery/Pickup address" />
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
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import ApiService from "@/core/services/ApiService";
import { $toastSuccess, $toastError } from "@/core/helpers/utility";

const router = useRouter();
const route = useRoute();
const loading = ref(false);
const pageLoading = ref(true);
const errors = ref({});
const deliveryId = ref(null);

const form = reactive({
    type: "delivery",
    scheduled_date: "",
    scheduled_time: "",
    address: "",
    notes: "",
    status: "pending",
});

const loadFormData = async () => {
    pageLoading.value = true;
    try {
        deliveryId.value = route.params.id;
        const response = await ApiService.get(`deliveries/${deliveryId.value}/edit`);
        const delivery = response.data.data;
        form.type = delivery.type;
        form.scheduled_date = delivery.scheduled_date;
        form.scheduled_time = delivery.scheduled_time || "";
        form.address = delivery.address || "";
        form.notes = delivery.notes || "";
        form.status = delivery.status;
    } catch (error) {
        console.error("Error loading delivery:", error);
        $toastError("Failed to load delivery");
        router.push({ name: "deliveries.index" });
    } finally {
        pageLoading.value = false;
    }
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        await ApiService.put(`deliveries/${deliveryId.value}`, { ...form });
        $toastSuccess("Delivery updated successfully");
        router.push({ name: "deliveries.index" });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            $toastError("Please fix the validation errors");
        } else {
            $toastError(error.response?.data?.message || "Failed to update delivery");
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadFormData();
});
</script>
