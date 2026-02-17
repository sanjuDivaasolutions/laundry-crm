<template>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Edit Customer</h2>
                <span class="text-muted ms-3" v-if="form.customer_code">{{ form.customer_code }}</span>
            </div>
            <div class="card-toolbar">
                <router-link :to="{ name: 'customers.index' }" class="btn btn-light-danger">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Customers
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
                    <div class="col-12">
                        <h4 class="mb-5 text-gray-800">Customer Information</h4>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.name }"
                            placeholder="Customer full name"
                        />
                        <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">Phone</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="form-control"
                            :class="{ 'is-invalid': errors.phone }"
                            placeholder="Phone number"
                        />
                        <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone[0] }}</div>
                    </div>
                    <div class="col-12 mb-5">
                        <label class="form-label">Address</label>
                        <textarea
                            v-model="form.address"
                            class="form-control"
                            rows="2"
                            placeholder="Customer address (optional)"
                        ></textarea>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch mt-3">
                            <input v-model="form.is_active" type="checkbox" class="form-check-input" id="is_active" />
                            <label class="form-check-label" for="is_active">{{ form.is_active ? 'Active' : 'Inactive' }}</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <router-link :to="{ name: 'customers.index' }" class="btn btn-light">Cancel</router-link>
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-check-lg me-2"></i>
                        Update Customer
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
const customerId = ref(null);

const form = reactive({
    name: "",
    customer_code: "",
    phone: "",
    address: "",
    is_active: true,
});

const loadFormData = async () => {
    pageLoading.value = true;
    try {
        customerId.value = route.params.id;
        const response = await ApiService.get(`customers/${customerId.value}/edit`);
        const customer = response.data.data;
        form.name = customer.name;
        form.customer_code = customer.customer_code;
        form.phone = customer.phone;
        form.address = customer.address || "";
        form.is_active = customer.is_active;
    } catch (error) {
        console.error("Error loading customer:", error);
        $toastError("Failed to load customer");
        router.push({ name: "customers.index" });
    } finally {
        pageLoading.value = false;
    }
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        await ApiService.put(`customers/${customerId.value}`, {
            name: form.name,
            phone: form.phone,
            address: form.address,
            is_active: form.is_active,
        });
        $toastSuccess("Customer updated successfully");
        router.push({ name: "customers.index" });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            $toastError("Please fix the validation errors");
        } else {
            $toastError(error.response?.data?.message || "Failed to update customer");
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadFormData();
});
</script>
