<template>
    <div>
        <!-- Header Card with Filters -->
        <div class="card mb-6">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <button class="btn btn-sm btn-icon btn-light me-3" @click="goBack">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </button>
                    <h2>
                        <i :class="[reportConfig.icon, 'me-2']"></i>
                        {{ reportConfig.title }}
                    </h2>
                </div>
                <div class="card-toolbar gap-2">
                    <button class="btn btn-sm btn-light-success" @click="exportData('excel')" :disabled="exporting">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                    </button>
                    <button class="btn btn-sm btn-light-danger" @click="exportData('pdf')" :disabled="exporting">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                    </button>
                </div>
            </div>
            <div class="card-body pt-2">
                <div class="row align-items-end">
                    <!-- Daily: date picker -->
                    <div class="col-md-3 mb-3" v-if="reportType === 'daily'">
                        <label class="form-label">Date</label>
                        <input v-model="filters.date" type="date" class="form-control" />
                    </div>

                    <!-- Weekly: week start picker -->
                    <div class="col-md-3 mb-3" v-if="reportType === 'weekly'">
                        <label class="form-label">Week Start</label>
                        <input v-model="filters.start_date" type="date" class="form-control" />
                    </div>

                    <!-- Monthly: month picker -->
                    <div class="col-md-3 mb-3" v-if="reportType === 'monthly'">
                        <label class="form-label">Month</label>
                        <input v-model="filters.month" type="month" class="form-control" />
                    </div>

                    <!-- Range-based reports: start + end date -->
                    <template v-if="isRangeReport">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Start Date</label>
                            <input v-model="filters.range_start" type="date" class="form-control" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">End Date</label>
                            <input v-model="filters.range_end" type="date" class="form-control" />
                        </div>
                    </template>

                    <!-- Limit selector for top-services / top-customers -->
                    <div class="col-md-2 mb-3" v-if="reportType === 'top-services' || reportType === 'top-customers'">
                        <label class="form-label">Limit</label>
                        <select v-model="filters.limit" class="form-select">
                            <option :value="5">5</option>
                            <option :value="10">10</option>
                            <option :value="20">20</option>
                            <option :value="50">50</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <button class="btn btn-primary w-100" @click="loadData" :disabled="isLoading">
                            <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span>
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-10">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3 text-muted">Loading report data...</p>
        </div>

        <template v-else>
            <!-- Summary Cards (daily, weekly, monthly) -->
            <div class="row mb-6" v-if="summary && isSummaryReport">
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="card bg-light-primary">
                        <div class="card-body py-5">
                            <div class="text-muted fs-7">Total Orders</div>
                            <div class="fw-bold fs-2 text-primary">{{ summary.total_orders }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="card bg-light-success">
                        <div class="card-body py-5">
                            <div class="text-muted fs-7">Total Revenue</div>
                            <div class="fw-bold fs-2 text-success">{{ formatCurrency(summary.total_revenue) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="card bg-light-info">
                        <div class="card-body py-5">
                            <div class="text-muted fs-7">Collected</div>
                            <div class="fw-bold fs-2 text-info">{{ formatCurrency(summary.total_collected) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="card bg-light-warning">
                        <div class="card-body py-5">
                            <div class="text-muted fs-7">Outstanding</div>
                            <div class="fw-bold fs-2 text-warning">{{ formatCurrency(summary.outstanding_amount || summary.pending_amount || 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4" v-if="summary.total_items !== undefined">
                    <div class="card">
                        <div class="card-body py-5">
                            <div class="text-muted fs-7">Total Items</div>
                            <div class="fw-bold fs-2">{{ summary.total_items }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4" v-if="summary.average_order_value !== undefined">
                    <div class="card">
                        <div class="card-body py-5">
                            <div class="text-muted fs-7">Avg Order Value</div>
                            <div class="fw-bold fs-2">{{ formatCurrency(summary.average_order_value) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4" v-if="summary.urgent_orders !== undefined">
                    <div class="card">
                        <div class="card-body py-5">
                            <div class="text-muted fs-7">Urgent Orders</div>
                            <div class="fw-bold fs-2 text-danger">{{ summary.urgent_orders }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 mb-4" v-if="summary.new_customers !== undefined">
                    <div class="card">
                        <div class="card-body py-5">
                            <div class="text-muted fs-7">New Customers</div>
                            <div class="fw-bold fs-2">{{ summary.new_customers }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Trend Table -->
            <div class="card mb-6" v-if="reportType === 'revenue-trend' && revenueTrend.length > 0">
                <div class="card-header">
                    <h3 class="card-title">Revenue Trend</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-hover gy-3">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th>Date</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Collected</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="day in revenueTrend" :key="day.date">
                                    <td>{{ day.date }}</td>
                                    <td class="text-end">{{ day.orders }}</td>
                                    <td class="text-end">{{ formatCurrency(day.revenue) }}</td>
                                    <td class="text-end">{{ formatCurrency(day.collected) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold bg-light">
                                    <td>Total</td>
                                    <td class="text-end">{{ revenueTrend.reduce((s, d) => s + d.orders, 0) }}</td>
                                    <td class="text-end">{{ formatCurrency(revenueTrend.reduce((s, d) => s + d.revenue, 0)) }}</td>
                                    <td class="text-end">{{ formatCurrency(revenueTrend.reduce((s, d) => s + d.collected, 0)) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Services Table -->
            <div class="card mb-6" v-if="(reportType === 'top-services' || isCompoundReport) && topServices.length > 0">
                <div class="card-header">
                    <h3 class="card-title">Top Services</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered gy-3">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th>Service</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in topServices" :key="s.service_name">
                                    <td>{{ s.service_name }}</td>
                                    <td class="text-end">{{ s.order_count }}</td>
                                    <td class="text-end">{{ s.total_quantity }}</td>
                                    <td class="text-end">{{ formatCurrency(s.total_revenue) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Customers Table -->
            <div class="card mb-6" v-if="(reportType === 'top-customers' || isCompoundReport) && topCustomers.length > 0">
                <div class="card-header">
                    <h3 class="card-title">Top Customers</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered gy-3">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in topCustomers" :key="c.id">
                                    <td>{{ c.name }}</td>
                                    <td>{{ c.phone }}</td>
                                    <td class="text-end">{{ c.order_count }}</td>
                                    <td class="text-end">{{ formatCurrency(c.total_spent) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Methods (weekly/monthly) -->
            <div class="row mb-6" v-if="isCompoundReport && (paymentMethods.length > 0 || statusDistribution.length > 0)">
                <div class="col-md-6 mb-4" v-if="paymentMethods.length > 0">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Payment Methods</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-row-bordered gy-3">
                                    <thead>
                                        <tr class="fw-bold text-muted">
                                            <th>Method</th>
                                            <th class="text-end">Count</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="p in paymentMethods" :key="p.method">
                                            <td>{{ p.method }}</td>
                                            <td class="text-end">{{ p.count }}</td>
                                            <td class="text-end">{{ formatCurrency(p.total) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4" v-if="statusDistribution.length > 0">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Order Status Distribution</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-row-bordered gy-3">
                                    <thead>
                                        <tr class="fw-bold text-muted">
                                            <th>Status</th>
                                            <th class="text-end">Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="s in statusDistribution" :key="s.status">
                                            <td>{{ s.status }}</td>
                                            <td class="text-end">{{ s.count }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="!isLoading && !summary && revenueTrend.length === 0 && topServices.length === 0 && topCustomers.length === 0"
                class="text-center py-10"
            >
                <i class="bi bi-inbox fs-2x text-muted"></i>
                <p class="text-muted mt-3">No data available for the selected filters.</p>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import ApiService from "@/core/services/ApiService";
import { $toastSuccess, $toastError } from "@/core/helpers/utility";

const route = useRoute();
const router = useRouter();

const reportType = computed(() => route.params.type);

const reportConfigs = {
    daily: { title: "Daily Summary", icon: "bi bi-calendar-day" },
    weekly: { title: "Weekly Summary", icon: "bi bi-calendar-week" },
    monthly: { title: "Monthly Summary", icon: "bi bi-calendar-month" },
    "revenue-trend": { title: "Revenue Trend", icon: "bi bi-graph-up" },
    "top-services": { title: "Top Services", icon: "bi bi-trophy" },
    "top-customers": { title: "Top Customers", icon: "bi bi-people" },
};

const reportConfig = computed(() => reportConfigs[reportType.value] || { title: "Report", icon: "bi bi-bar-chart" });

const isSummaryReport = computed(() => ["daily", "weekly", "monthly"].includes(reportType.value));
const isRangeReport = computed(() => ["revenue-trend", "top-services", "top-customers"].includes(reportType.value));
const isCompoundReport = computed(() => ["weekly", "monthly"].includes(reportType.value));

const isLoading = ref(false);
const exporting = ref(false);
const summary = ref(null);
const revenueTrend = ref([]);
const topServices = ref([]);
const topCustomers = ref([]);
const paymentMethods = ref([]);
const statusDistribution = ref([]);

const today = new Date().toISOString().split("T")[0];
const thisMonth = today.substring(0, 7);
const monthStart = thisMonth + "-01";

const filters = reactive({
    date: today,
    start_date: "",
    month: thisMonth,
    range_start: monthStart,
    range_end: today,
    limit: 10,
});

const formatCurrency = (amount) => {
    return parseFloat(amount || 0).toLocaleString("en-US", {
        style: "currency",
        currency: "USD",
    });
};

const resetData = () => {
    summary.value = null;
    revenueTrend.value = [];
    topServices.value = [];
    topCustomers.value = [];
    paymentMethods.value = [];
    statusDistribution.value = [];
};

const loadSummaryReport = async () => {
    let params = {};
    if (reportType.value === "daily") {
        params = { date: filters.date };
    } else if (reportType.value === "weekly") {
        params = { start_date: filters.start_date || undefined };
    } else {
        params = { month: filters.month || undefined };
    }

    const response = await ApiService.get(`reports/${reportType.value}`, { params });
    summary.value = response.data.data || response.data;

    topServices.value = summary.value.top_services || [];
    topCustomers.value = summary.value.top_customers || [];
    paymentMethods.value = summary.value.payment_methods || [];
    statusDistribution.value = summary.value.status_distribution || [];
};

const loadRevenueTrend = async () => {
    const params = { start_date: filters.range_start, end_date: filters.range_end };
    const response = await ApiService.get("reports/revenue-trend", { params });
    revenueTrend.value = response.data.data || response.data || [];
};

const loadTopServices = async () => {
    const params = {
        start_date: filters.range_start,
        end_date: filters.range_end,
        limit: filters.limit,
    };
    const response = await ApiService.get("reports/top-services", { params });
    topServices.value = response.data.data || response.data || [];
};

const loadTopCustomers = async () => {
    const params = {
        start_date: filters.range_start,
        end_date: filters.range_end,
        limit: filters.limit,
    };
    const response = await ApiService.get("reports/top-customers", { params });
    topCustomers.value = response.data.data || response.data || [];
};

const loadData = async () => {
    isLoading.value = true;
    resetData();
    try {
        if (isSummaryReport.value) {
            await loadSummaryReport();
        } else if (reportType.value === "revenue-trend") {
            await loadRevenueTrend();
        } else if (reportType.value === "top-services") {
            await loadTopServices();
        } else if (reportType.value === "top-customers") {
            await loadTopCustomers();
        }
    } catch (error) {
        console.error("Error loading report:", error);
        $toastError("Failed to load report");
    } finally {
        isLoading.value = false;
    }
};

const exportData = async (format) => {
    exporting.value = true;
    try {
        const params = {
            report_type: reportType.value,
            date: filters.date,
            start_date: filters.range_start,
            end_date: filters.range_end,
            month: filters.month,
        };
        await ApiService.post(`exports/reports/${format}`, params, false);
        $toastSuccess(`Report ${format} export queued. You will be notified when ready.`);
    } catch (error) {
        $toastError(error.response?.data?.message || "Failed to queue export");
    } finally {
        exporting.value = false;
    }
};

const goBack = () => {
    router.push({ name: "reports.index" });
};

watch(() => route.params.type, () => {
    loadData();
});

onMounted(() => {
    if (!reportConfigs[reportType.value]) {
        router.replace({ name: "reports.index" });
        return;
    }
    loadData();
});
</script>
