<template>
    <div>
        <MagicDatatable
            v-if="reportModule"
            :id="magicDatatableId"
            :table-title="reportModule.tableTitle || reportModule.plural"
            card-body-padding="no_padding"
            heading="Filters"
            :filters="reportFilters"
            :columns="reportColumns"
            :module="reportModule"
            pagination-position="both"
            :save-state="false"
        >
        </MagicDatatable>

        <EasyModalContainer
            v-if="reportModule?.supportsDetails"
            id="product-sale-details-modal"
            :title="getModalTitle()"
            size="modal-xl"
            :centered="true"
            :backdrop="true"
            :narrow-padding="true"
            :show="showDetailsModal"
            @close="closeDetailsModal"
        >
            <template #body>
                <div v-if="loadingDetails" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div v-else class="modal-scroll-container">
                    <template v-if="isSalesProductReport">
                        <div v-if="hasSalesProductDetails" class="d-flex flex-column gap-6">
                            <div
                                v-if="modalData.monthlyBreakdown && modalData.monthlyBreakdown.length"
                            >
                                <h6 class="fw-bold text-gray-700 mb-3">Monthly Breakdown</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th class="text-end">Quantity</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="month in modalData.monthlyBreakdown"
                                                :key="month.period"
                                            >
                                                <td>{{ month.label }}</td>
                                                <td class="text-end">{{ formatQuantity(month.quantity) }}</td>
                                                <td class="text-end">
                                                    {{ formatAmountLabel(month.amount, month.amount_label) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div
                                v-if="modalData.weeklyBreakdown && modalData.weeklyBreakdown.length"
                            >
                                <h6 class="fw-bold text-gray-700 mb-3">Weekly Breakdown</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Week</th>
                                                <th class="text-end">Quantity</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="week in modalData.weeklyBreakdown"
                                                :key="week.period"
                                            >
                                                <td>{{ week.label }}</td>
                                                <td class="text-end">{{ formatQuantity(week.quantity) }}</td>
                                                <td class="text-end">
                                                    {{ formatAmountLabel(week.amount, week.amount_label) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div v-if="modalData.data && modalData.data.length">
                                <h6 class="fw-bold text-gray-700 mb-3">Invoice Entries</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>SO #</th>
                                                <th class="text-end">{{ getQuantityColumnHeader() }}</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in modalData.data" :key="item.so_number + item.date">
                                                <td>{{ item.formatted_date }}</td>
                                                <td>{{ item.so_number }}</td>
                                                <td class="text-end">{{ formatQuantity(item.quantity) }}</td>
                                                <td class="text-end">
                                                    {{ formatAmountLabel(item.amount, item.amount_label) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold">
                                                <td colspan="2">Total</td>
                                                <td class="text-end">
                                                    {{ formatQuantity(modalData.totalQuantity || 0) }}
                                                </td>
                                                <td class="text-end">
                                                    {{
                                                        formatAmountLabel(
                                                            modalData.totalAmount || 0,
                                                            modalData.totalAmountLabel
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-muted">
                                No sales data found for this product in the selected period.
                            </p>
                        </div>
                    </template>
                    <template v-else>
                        <div v-if="modalData.data && modalData.data.length" class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>SO #</th>
                                        <th class="text-end">{{ getQuantityColumnHeader() }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in modalData.data" :key="item.so_number + item.date">
                                        <td>{{ item.formatted_date }}</td>
                                        <td>{{ item.so_number }}</td>
                                        <td class="text-end">{{ item.quantity }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="2">Total</td>
                                        <td class="text-end">{{ modalData.totalQuantity || 0 }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div v-else class="text-muted text-center py-4">
                            No details available for the selected period.
                        </div>
                    </template>
                </div>
            </template>
        </EasyModalContainer>
    </div>
</template>

<script setup>
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";
import EasyModalContainer from "@common@/modals/EasyModalContainer.vue";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import ApiService from "@/core/services/ApiService";

import { module as SummaryStockModule } from "../modules/summary-stock";
import { module as SalesProductModule } from "../modules/sales-product";
import { module as InwardsProductModule } from "../modules/inwards-product";
import { module as SalesCommissionModule } from "../modules/sales-commission";
import { module as SalesMonthlyModule } from "../modules/sales-month";

import emitter from "@/core/plugins/mitt";

const route = useRoute();
const type = route.params.type;

const reportModule = ref(null);
const reportFilters = ref(null);
const reportColumns = ref(null);

const magicDatatableId = "reports-index-magic-datatable";

// Modal state
const showDetailsModal = ref(false);
const loadingDetails = ref(false);
const modalData = ref({
    productName: "",
    totalQuantity: 0,
    totalAmount: 0,
    totalAmountLabel: null,
    currencySymbol: null,
    data: [],
    monthlyBreakdown: [],
    weeklyBreakdown: [],
});

const isSalesProductReport = computed(() => type === "sales-product");

const hasSalesProductDetails = computed(() => {
    if (!isSalesProductReport.value) {
        return false;
    }
    const monthly = modalData.value.monthlyBreakdown || [];
    const weekly = modalData.value.weeklyBreakdown || [];
    const entries = modalData.value.data || [];
    return monthly.length > 0 || weekly.length > 0 || entries.length > 0;
});

const formatNumber = (value, { minimumFractionDigits = 0, maximumFractionDigits = 2 } = {}) => {
    const numeric = Number(value ?? 0);
    if (!Number.isFinite(numeric)) {
        return "0";
    }
    return new Intl.NumberFormat(undefined, {
        minimumFractionDigits,
        maximumFractionDigits,
    }).format(numeric);
};

const formatQuantity = (value) => formatNumber(value);

const formatAmountLabel = (amount, label) => {
    if (label) {
        return label;
    }
    const symbol = modalData.value.currencySymbol || "";
    const formatted = formatNumber(amount, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return symbol ? `${symbol}${formatted}` : formatted;
};

// Modal functions
const openDetailsModal = async (row) => {
    showDetailsModal.value = true;
    loadingDetails.value = true;
    modalData.value.productName = row.name || 'Unknown Product';
    modalData.value.totalQuantity = 0;
    modalData.value.monthlyBreakdown = [];
    modalData.value.weeklyBreakdown = [];
    modalData.value.totalAmount = 0;
    modalData.value.totalAmountLabel = null;
    modalData.value.currencySymbol = null;

    try {
        // Get current filters from the MagicDatatable
        const currentFilters = {};
        if (reportFilters.value) {
            reportFilters.value.forEach(filter => {
                if (filter.value !== null && filter.value !== undefined && filter.value !== '') {
                    currentFilters[filter.name] = filter.value;
                }
            });
        }

        const response = await ApiService.get(`/reports/product-sale-details/${row.id}`, {
            params: currentFilters
        });

        modalData.value.productName = response.data.product_name || row.name;
        modalData.value.totalQuantity = response.data.total_quantity || 0;
        modalData.value.totalAmount = response.data.total_amount || 0;
        modalData.value.totalAmountLabel = response.data.total_amount_label || null;
        modalData.value.currencySymbol = response.data.currency_symbol || null;
        modalData.value.monthlyBreakdown = response.data.monthly_breakdown || [];
        modalData.value.weeklyBreakdown = response.data.weekly_breakdown || [];
        modalData.value.data = response.data.data || [];
    } catch (error) {
        console.error('Error fetching product sale details:', error);
        modalData.value.data = [];
        modalData.value.monthlyBreakdown = [];
        modalData.value.weeklyBreakdown = [];
        modalData.value.totalQuantity = 0;
        modalData.value.totalAmount = 0;
        modalData.value.totalAmountLabel = null;
    } finally {
        loadingDetails.value = false;
    }
};

const openInwardDetailsModal = async (row) => {
    showDetailsModal.value = true;
    loadingDetails.value = true;
    modalData.value.productName = row.name || 'Unknown Product';
    modalData.value.totalQuantity = 0;
    modalData.value.monthlyBreakdown = [];
    modalData.value.weeklyBreakdown = [];
    modalData.value.totalAmount = 0;
    modalData.value.totalAmountLabel = null;
    modalData.value.currencySymbol = null;

    try {
        // Get current filters from the MagicDatatable
        const currentFilters = {};
        if (reportFilters.value) {
            reportFilters.value.forEach(filter => {
                if (filter.value !== null && filter.value !== undefined && filter.value !== '') {
                    currentFilters[filter.name] = filter.value;
                }
            });
        }

        const response = await ApiService.get(`/reports/product-inward-details/${row.id}`, {
            params: currentFilters
        });

        modalData.value.productName = response.data.product_name || row.name;
        modalData.value.totalQuantity = response.data.total_quantity || 0;
        modalData.value.data = response.data.data || [];
    } catch (error) {
        console.error('Error fetching product inward details:', error);
        modalData.value.data = [];
        modalData.value.totalQuantity = 0;
    } finally {
        loadingDetails.value = false;
    }
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    modalData.value = {
        productName: "",
        totalQuantity: 0,
        totalAmount: 0,
        totalAmountLabel: null,
        currencySymbol: null,
        data: [],
        monthlyBreakdown: [],
        weeklyBreakdown: [],
    };
};

const openAgentCommissionDetailsModal = async (row) => {
    showDetailsModal.value = true;
    loadingDetails.value = true;
    modalData.value.productName = row.agent_name || 'Unknown Agent';
    modalData.value.totalQuantity = 0;
    modalData.value.monthlyBreakdown = [];
    modalData.value.weeklyBreakdown = [];
    modalData.value.totalAmount = 0;
    modalData.value.totalAmountLabel = null;
    modalData.value.currencySymbol = null;

    try {
        // Get current filters from the MagicDatatable
        const currentFilters = {};
        if (reportFilters.value) {
            reportFilters.value.forEach(filter => {
                if (filter.value !== null && filter.value !== undefined && filter.value !== '') {
                    currentFilters[filter.name] = filter.value;
                }
            });
        }

        const response = await ApiService.get(`/reports/agent-commission-details/${row.id}`, {
            params: currentFilters
        });

        modalData.value.productName = response.data.product_name || row.agent_name;
        modalData.value.totalQuantity = response.data.total_quantity || 0;
        modalData.value.data = response.data.data || [];
    } catch (error) {
        console.error('Error fetching agent commission details:', error);
        modalData.value.data = [];
        modalData.value.totalQuantity = 0;
    } finally {
        loadingDetails.value = false;
    }
};

const getModalTitle = () => {
    let prefix = 'Sales Details';
    if (type === 'inwards-product') {
        prefix = 'Inward Details';
    } else if (type === 'sales-commission') {
        prefix = 'Commission Details';
    }
    return `${prefix}: ${modalData.value.productName}`;
};

const getQuantityColumnHeader = () => {
    if (type === 'sales-commission') {
        return 'Commission';
    }
    return 'Quantity';
};

/*const module = computed(async () => {
    const { module } = await import(`./modules/${type}`);
    return module;
});*/

onMounted(async () => {
    let module = null;
    let filters = null;
    let columns = null;

    if (type === "summary-stock") {
        module = SummaryStockModule;
        filters = SummaryStockModule.filters;
        columns = SummaryStockModule.columns;
    } else if (type === "sales-product") {
        module = SalesProductModule;
        filters = SalesProductModule.filters;
        columns = SalesProductModule.columns;
    } else if (type === "inwards-product") {
        module = InwardsProductModule;
        filters = InwardsProductModule.filters;
        columns = InwardsProductModule.columns;
    } else if (type === "sales-commission") {
        module = SalesCommissionModule;
        filters = SalesCommissionModule.filters;
        columns = SalesCommissionModule.columns;
    } else if (type === "sales-month") {
        module = SalesMonthlyModule;
        filters = SalesMonthlyModule.filters;
        columns = SalesMonthlyModule.columns;
    }

    reportModule.value = module;
    reportFilters.value = filters;
    reportColumns.value = columns;
    if (reportModule.value.tableTitle) {
        emitter.emit(
            "update-page-title",
            "Report: " + reportModule.value.tableTitle
        );
    }

    // Listen for product sale details modal open event
    emitter.on('open-product-sale-details', openDetailsModal);
    // Listen for product inward details modal open event
    emitter.on('open-product-inward-details', openInwardDetailsModal);
    // Listen for agent commission details modal open event
    emitter.on('open-agent-commission-details', openAgentCommissionDetailsModal);
});

onBeforeUnmount(() => {
    emitter.emit("update-page-title", null);
    emitter.off('open-product-sale-details', openDetailsModal);
    emitter.off('open-product-inward-details', openInwardDetailsModal);
    emitter.off('open-agent-commission-details', openAgentCommissionDetailsModal);
});
</script>

<style scoped>
.modal-scroll-container {
    max-height: 70vh;
    overflow-y: auto;
    padding-right: 0.75rem;
}

@media (max-width: 768px) {
    .modal-scroll-container {
        max-height: 60vh;
    }
}
</style>
