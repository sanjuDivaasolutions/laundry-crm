<template>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold m-0">Order Items</h3>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-flush align-middle table-row-bordered table-row-solid gy-4 gs-9">
                    <thead class="border-gray-200 fs-5 fw-bold bg-lighten">
                        <tr>
                            <th class="min-w-150px">Item</th>
                            <th class="min-w-100px text-center">Qty</th>
                            <th class="min-w-125px text-end">Price</th>
                            <th class="min-w-125px text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody class="fs-6 fw-semobold text-gray-600">
                        <tr v-for="item in items" :key="item.id">
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 fw-bold">{{ item.item_name }}</span>
                                    <span class="text-muted fs-7">{{ item.service_name }}</span>
                                </div>
                            </td>
                            <td class="text-center">{{ item.quantity }}</td>
                            <td class="text-end">{{ formatCurrency(item.unit_price) }}</td>
                            <td class="text-end fw-bold text-gray-900">{{ formatCurrency(item.total_price) }}</td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="4" class="text-center py-10">No items found in this order.</td>
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
    items: {
        type: Array,
        required: true,
    },
});
</script>
