<script setup>
import ReportSection from "@modules@/reports/components/ReportSection.vue";

const generalSection = {
    title: "Summary",
    list: [
        {
            title: "Profit & Loss",
            to: {
                name: "reports.show",
                params: { type: "summary-profit-loss" },
            },
        },
        {
            title: "Stock",
            to: {
                name: "reports.show",
                params: { type: "summary-stock" },
            },
        },
    ],
};
const salesSection = {
    title: "Sales",
    list: [
        {
            title: "Monthly Sales",
            to: {
                name: "reports.show",
                params: { type: "sales-month" },
            },
        },
        {
            title: "Sales by Product",
            to: { name: "reports.show", params: { type: "sales-product" } },
        },
        {
            title: "Sales Commission",
            to: { name: "reports.show", params: { type: "sales-commission" } },
        },
    ],
};
const purchaseSection = {
    title: "Purchase",
    list: [
        {
            title: "Inwards by Product",
            to: { name: "reports.show", params: { type: "inwards-product" } },
        },
    ],
};
</script>

<template>
    <div class="row">
        <div v-if="generalSection" class="col-xl-4 mb-10">
            <ReportSection
                :title="generalSection.title"
                :list="generalSection.list"
            />
        </div>
        <div v-if="salesSection" class="col-xl-4 mb-10">
            <ReportSection
                :title="salesSection.title"
                :list="salesSection.list"
            />
        </div>
        <div v-if="purchaseSection" class="col-xl-4 mb-10">
            <ReportSection
                :title="purchaseSection.title"
                :list="purchaseSection.list"
            />
        </div>
    </div>
</template>

<style scoped></style>
