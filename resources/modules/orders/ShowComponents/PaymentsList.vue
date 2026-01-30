<template>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold m-0">Payment History</h3>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-flush align-middle table-row-bordered table-row-solid gy-4 gs-9">
                    <thead class="border-gray-200 fs-5 fw-bold bg-lighten">
                        <tr>
                            <th class="min-w-150px">Date</th>
                            <th class="min-w-100px">Method</th>
                            <th class="min-w-125px text-end">Amount</th>
                            <th class="min-w-100px">Status</th>
                        </tr>
                    </thead>
                    <tbody class="fs-6 fw-semobold text-gray-600">
                        <tr v-for="payment in payments" :key="payment.id">
                            <td>{{ payment.payment_date }}</td>
                            <td>{{ payment.payment_method_label }}</td>
                            <td class="text-end fw-bold text-gray-900">{{ formatCurrency(payment.amount) }}</td>
                            <td>
                                <span class="badge badge-light-success">
                                    {{ "Success" }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="payments.length === 0">
                            <td colspan="4" class="text-center py-10">No payments found for this order.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { formatCurrency } from "@utility@/currency";

const props = defineProps({
    payments: {
        type: Array,
        required: true,
    },
});
</script>
