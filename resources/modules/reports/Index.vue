<template>
    <div>
        <div class="card mb-6">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h2><i class="bi bi-bar-chart me-2"></i> Reports</h2>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-6">Select a report type to view detailed analytics and insights.</p>
                <div class="row">
                    <div
                        v-for="report in reportTypes"
                        :key="report.type"
                        class="col-sm-6 col-lg-4 mb-5"
                    >
                        <div
                            class="card card-flush border border-hover-primary h-100 cursor-pointer"
                            @click="navigateToReport(report.type)"
                        >
                            <div class="card-body d-flex flex-column align-items-start p-6">
                                <div class="d-flex align-items-center w-100 mb-4">
                                    <div
                                        class="symbol symbol-50px me-4"
                                        :class="report.bgClass"
                                    >
                                        <span class="symbol-label" :class="report.bgClass">
                                            <i :class="[report.icon, report.textClass, 'fs-2']"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-bold fs-4 d-block">{{ report.title }}</span>
                                    </div>
                                    <i class="bi bi-chevron-right text-muted fs-4"></i>
                                </div>
                                <p class="text-muted fs-7 mb-0">{{ report.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from "vue-router";

const router = useRouter();

const reportTypes = [
    {
        type: "daily",
        title: "Daily Summary",
        description: "Today's orders, revenue, and key metrics at a glance.",
        icon: "bi bi-calendar-day",
        bgClass: "bg-light-primary",
        textClass: "text-primary",
    },
    {
        type: "weekly",
        title: "Weekly Summary",
        description: "Week-over-week performance and comparison data.",
        icon: "bi bi-calendar-week",
        bgClass: "bg-light-info",
        textClass: "text-info",
    },
    {
        type: "monthly",
        title: "Monthly Summary",
        description: "Monthly totals, trends, and breakdowns.",
        icon: "bi bi-calendar-month",
        bgClass: "bg-light-success",
        textClass: "text-success",
    },
    {
        type: "revenue-trend",
        title: "Revenue Trend",
        description: "Daily revenue breakdown over a selected date range.",
        icon: "bi bi-graph-up",
        bgClass: "bg-light-warning",
        textClass: "text-warning",
    },
    {
        type: "top-services",
        title: "Top Services",
        description: "Best performing services ranked by revenue and volume.",
        icon: "bi bi-trophy",
        bgClass: "bg-light-danger",
        textClass: "text-danger",
    },
    {
        type: "top-customers",
        title: "Top Customers",
        description: "Highest spending customers and their order history.",
        icon: "bi bi-people",
        bgClass: "bg-light-dark",
        textClass: "text-dark",
    },
];

const navigateToReport = (type) => {
    router.push({ name: "reports.show", params: { type } });
};
</script>
