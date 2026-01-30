<template>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold m-0">Order History</h3>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-flush align-middle table-row-bordered table-row-solid gy-4 gs-9">
                    <thead class="border-gray-200 fs-5 fw-bold bg-lighten">
                        <tr>
                            <th class="min-w-150px">Order #</th>
                            <th class="min-w-100px">Date</th>
                            <th class="min-w-125px text-end">Amount</th>
                            <th class="min-w-125px">Status</th>
                            <th class="min-w-100px text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="fs-6 fw-semobold text-gray-600">
                        <tr v-for="order in orders" :key="order.id">
                            <td>
                                <RouterLink :to="{ name: 'orders.show', params: { id: order.id } }" class="text-gray-800 text-hover-primary fw-bold">
                                    {{ order.order_number }}
                                </RouterLink>
                            </td>
                            <td>{{ order.order_date }}</td>
                            <td class="text-end fw-bold text-gray-900">{{ formatCurrency(order.total_amount) }}</td>
                            <td>
                                <span class="badge" :class="getStatusBadgeClass(order.payment_status)">
                                    {{ order.payment_status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <RouterLink :to="{ name: 'orders.show', params: { id: order.id } }" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                    <FormIcon icon="feather:eye" />
                                </RouterLink>
                            </td>
                        </tr>
                        <tr v-if="orders.length === 0">
                            <td colspan="5" class="text-center py-10">No orders found for this customer.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { formatCurrency } from "@utility@/currency";
import FormIcon from "@common@/components/form/FormIcon.vue";

const props = defineProps({
    orders: {
        type: Array,
        required: true,
    },
});

const getStatusBadgeClass = (status) => {
    return status === 'Paid' ? 'badge-light-success' : 'badge-light-warning';
};
</script>
