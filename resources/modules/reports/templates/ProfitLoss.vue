<template>
    <div class="container">
        <div v-if="false" class="card mb-5">
            <div class="card-header"></div>
        </div>
        <div class="card">
            <div
                v-if="title || subTitle"
                class="card-header justify-content-center"
            >
                <div class="card-title flex-column align-items-center">
                    <h4 v-if="title" class="mb-0">{{ title }}</h4>
                    <p v-if="subTitle" class="mb-0">{{ subTitle }}</p>
                </div>
            </div>
            <div class="card-header justify-content-center">
                <GlobalFilter
                    id="profit-loss-report"
                    v-if="filters.length > 0"
                    loading="loading"
                    :query="query"
                    :defaultQuery="defaultQuery"
                    :filters="globalFilters"
                    @filter-submit="onFilterSubmit"
                />
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table
                        class="table table-sm table-row-gray-400 gy-0 gs-0 mb-0"
                    >
                        <thead>
                            <tr>
                                <th
                                    v-for="h in reportHeaders"
                                    class="border-bottom fw-bold"
                                    :class="h.class"
                                >
                                    {{ h.label || "&nbsp;" }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="d in reportData">
                                <tr v-for="f in d.header">
                                    <th
                                        class="py-3 fs-4 fw-bold"
                                        :class="h.class"
                                        v-for="h in reportHeaders"
                                        v-html="getValue(h, f)"
                                    ></th>
                                </tr>
                                <tr v-for="sd in d.data">
                                    <td
                                        class="ps-3 fs-4"
                                        :class="h.class"
                                        v-for="h in reportHeaders"
                                        v-html="getValue(h, sd)"
                                    ></td>
                                </tr>
                                <tr v-for="f in d.footer">
                                    <th
                                        class="py-3 fs-4 fw-bold"
                                        :class="h.class"
                                        v-for="h in reportHeaders"
                                        v-html="getValue(h, f)"
                                    ></th>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { module as ProfitLossModule } from "@modules@/reports/modules/summary-profit-loss";
import { useRoute } from "vue-router";
import ApiService from "@/core/services/ApiService";
import GlobalFilter from "@/components/magic-datatable/filter-plugin/GlobalFilter.vue";

const route = useRoute();
const type = route.params.type;

const items = ref([]);

const reportModule = ref(null);
const reportHeaders = ref([]);
const reportData = ref([]);

const title = ref(null);
const subTitle = ref(null);

const dateRange =
    moment().startOf("year").format("MM/DD/YYYY") +
    " to " +
    moment().format("MM/DD/YYYY");

const filters = ref([]);
const query = ref({
    f_date_range: dateRange,
    f_business_id: null,
});
const defaultQuery = ref({
    f_date_range: dateRange,
    f_business_id: null,
});
const onFilterSubmit = (data) => {
    query.value = data;
    fetchData();
};
const globalFilters = computed(() => {
    if (!filters.value.length) return [];
    return filters.value.map((filter) => {
        return {
            ...filter,
            object: false,
        };
    });
});

const fetchData = async () => {
    const r = _.get(reportModule, "value.route", null);
    if (!r) return;
    const q = _.cloneDeep(query.value);
    ApiService.get(r, { params: q }).then((response) => {
        setData(response.data);
    });
};

const getValue = (header, data) => {
    if (header.value) {
        if (header.value === "blank_label") return "&nbsp;";
        return _.get(data, header.value, "");
    }
    return "";
};

const setData = (data) => {
    reportData.value = data.data;
    if (data.headers) {
        reportHeaders.value = data.headers;
    }
    if (data.title) {
        title.value = data.title;
    }
    if (data.subtitle) {
        subTitle.value = data.subtitle;
    }
};

onMounted(() => {
    let module = null;
    let headers = null;
    if (type === "summary-profit-loss" || type === "summary-balance-sheet") {
        module = ProfitLossModule;
        headers = ProfitLossModule.headers;
        filters.value = ProfitLossModule.filters || [];
    }
    reportModule.value = module;
    reportHeaders.value = headers;
    title.value = module ? module.title : null;
    subTitle.value = module ? module.subTitle : null;
    fetchData();
});
</script>

<style scoped></style>
