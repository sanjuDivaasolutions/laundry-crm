<template>
    <div>
        <!-- Filters Card -->
        <div class="card mb-6">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h2><i class="bi bi-bar-chart me-2"></i> Reports</h2>
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
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Report Type</label>
                        <select v-model="filters.report_type" class="form-select" @change="loadReport">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3" v-if="filters.report_type === 'daily'">
                        <label class="form-label">Date</label>
                        <input v-model="filters.date" type="date" class="form-control" @change="loadReport" />
                    </div>
                    <div class="col-md-2 mb-3" v-if="filters.report_type === 'weekly'">
                        <label class="form-label">Week Start</label>
                        <input v-model="filters.start_date" type="date" class="form-control" @change="loadReport" />
                    </div>
                    <div class="col-md-2 mb-3" v-if="filters.report_type === 'monthly'">
                        <label class="form-label">Month</label>
                        <input v-model="filters.month" type="month" class="form-control" @change="loadReport" />
                    </div>
                    <div class="col-md-2 mb-3" v-if="filters.report_type !== 'daily'">
                        <label class="form-label">Start Date</label>
                        <input v-model="filters.trend_start" type="date" class="form-control" />
                    </div>
                    <div class="col-md-2 mb-3" v-if="filters.report_type !== 'daily'">
                        <label class="form-label">End Date</label>
                        <input v-model="filters.trend_end" type="date" class="form-control" />
                    </div>
                    <div class="col-md-2 mb-3">
                        <button class="btn btn-primary w-100" @click="loadAllData" :disabled="isLoading">
                            <span v-if="isLoading" class="spinner-border spinner-border-sm me-1"></span>
                            Apply Filters
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
            <!-- Summary Cards -->
            <div class="row mb-6" v-if="summary">
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
            <div class="row mb-6" v-if="revenueTrend.length > 0">
                <div class="col-12">
                    <div class="card">
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
                </div>
            </div>

            <!-- Top Services & Top Customers -->
            <div class="row mb-6">
                <div class="col-md-6 mb-4" v-if="topServices.length > 0">
                    <div class="card h-100">
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
                </div>
                <div class="col-md-6 mb-4" v-if="topCustomers.length > 0">
                    <div class="card h-100">
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
                </div>
            </div>

            <!-- Payment Methods & Status Distribution -->
            <div class="row mb-6">
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
        </template>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import ApiService from "@/core/services/ApiService";
import { $toastSuccess, $toastError } from "@/core/helpers/utility";

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
    report_type: "monthly",
    date: today,
    start_date: "",
    month: thisMonth,
    trend_start: monthStart,
    trend_end: today,
});

const formatCurrency = (amount) => {
    return parseFloat(amount || 0).toLocaleString("en-US", {
        style: "currency",
        currency: "USD",
    });
};

const loadReport = async () => {
    isLoading.value = true;
    try {
        let params = {};
        if (filters.report_type === "daily") {
            params = { date: filters.date };
        } else if (filters.report_type === "weekly") {
            params = { start_date: filters.start_date || undefined };
        } else {
            params = { month: filters.month || undefined };
        }

        const response = await ApiService.get(`reports/${filters.report_type}`, { params });
        summary.value = response.data.data || response.data;

        // Load sub-reports from summary if present
        topServices.value = summary.value.top_services || [];
        topCustomers.value = summary.value.top_customers || [];
        paymentMethods.value = summary.value.payment_methods || [];
        statusDistribution.value = summary.value.status_distribution || [];
    } catch (error) {
        console.error("Error loading report:", error);
        $toastError("Failed to load report");
    } finally {
        isLoading.value = false;
    }
};

const loadTrendData = async () => {
    if (!filters.trend_start || !filters.trend_end) return;
    try {
        const [trendRes, servicesRes, customersRes, paymentRes, statusRes] = await Promise.all([
            ApiService.get("reports/revenue-trend", { params: { start_date: filters.trend_start, end_date: filters.trend_end } }),
            ApiService.get("reports/top-services", { params: { start_date: filters.trend_start, end_date: filters.trend_end } }),
            ApiService.get("reports/top-customers", { params: { start_date: filters.trend_start, end_date: filters.trend_end } }),
            ApiService.get("reports/payment-methods", { params: { start_date: filters.trend_start, end_date: filters.trend_end } }),
            ApiService.get("reports/status-distribution", { params: { start_date: filters.trend_start, end_date: filters.trend_end } }),
        ]);
        revenueTrend.value = trendRes.data.data || trendRes.data || [];
        if (servicesRes.data.data) topServices.value = servicesRes.data.data;
        if (customersRes.data.data) topCustomers.value = customersRes.data.data;
        if (paymentRes.data.data) paymentMethods.value = paymentRes.data.data;
        if (statusRes.data.data) statusDistribution.value = statusRes.data.data;
    } catch (error) {
        console.error("Error loading trend data:", error);
    }
};

const loadAllData = async () => {
    isLoading.value = true;
    try {
        await loadReport();
        await loadTrendData();
    } finally {
        isLoading.value = false;
    }
};

const exportData = async (format) => {
    exporting.value = true;
    try {
        const params = {
            report_type: filters.report_type,
            date: filters.date,
            start_date: filters.trend_start,
            end_date: filters.trend_end,
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

onMounted(() => {
    loadAllData();
});
</script>
