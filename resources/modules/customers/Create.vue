<template>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2>Create New Customer</h2>
            </div>
            <div class="card-toolbar">
                <router-link :to="{ name: 'customers.index' }" class="btn btn-light-danger">
                    <i class="bi bi-arrow-left me-2"></i>
                    Back to Customers
                </router-link>
            </div>
        </div>
        <div class="card-body">
            <form @submit.prevent="submitForm">
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
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <router-link :to="{ name: 'customers.index' }" class="btn btn-light">Cancel</router-link>
                    <button type="submit" class="btn btn-primary" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-check-lg me-2"></i>
                        Create Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import ApiService from "@/core/services/ApiService";
import { $toastSuccess, $toastError } from "@/core/helpers/utility";

const router = useRouter();
const loading = ref(false);
const errors = ref({});

const form = reactive({
    name: "",
    phone: "",
    address: "",
});

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        await ApiService.post("customers", { ...form }, false);
        $toastSuccess("Customer created successfully");
        router.push({ name: "customers.index" });
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            $toastError("Please fix the validation errors");
        } else {
            $toastError(error.response?.data?.message || "Failed to create customer");
        }
    } finally {
        loading.value = false;
    }
};
</script>
