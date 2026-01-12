<template>
    <div class="card">
        <div
            class="card-header"
            :class="moduleFilters.length > 0 ? 'border-0' : ''"
        >
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">{{
                    moduleTitle
                }}</span>

                <span
                    v-if="moduleSubTitle"
                    class="text-muted fw-semobold fs-7"
                    >{{ moduleSubTitle }}</span
                >
            </h3>
        </div>
        <div
            class="card-header justify-content-start"
            v-if="moduleFilters.length > 0"
        >
            <GlobalFilter
                id="line-chart"
                loading="loading"
                :query="moduleQuery"
                :defaultQuery="moduleDefaultQuery"
                :filters="moduleGlobalFilters"
                @filter-submit="onFilterSubmit"
            />
        </div>

        <div class="card-body">
            <apexchart
                ref="chartRef"
                type="line"
                :options="options"
                :series="series"
                :height="moduleChartHeight"
            ></apexchart>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import GlobalFilter from "@/components/magic-datatable/filter-plugin/GlobalFilter.vue";
import { getCSSVariableValue } from "@/assets/ts/_utils";

const emit = defineEmits(["filter"]);

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
});

/*const props = defineProps({
    title: {
        type: String,
        default: "Chart",
    },
    subTitle: {
        type: String,
        default: null,
    },
    endpoint: {
        type: String,
        required: true,
    },
    filters: {
        type: Array,
        default: () => [],
    },
    widgetClasses: String,
    height: Number,
    hideToolbar: {
        type: Boolean,
        default: true,
    },
});*/

const chartRef = ref(null);

const options = computed(() => {
    return chartOptions();
});

const moduleLoading = computed(() => {
    return _.get(props.module, "isLoading", false);
});

const moduleTitle = computed(() => {
    return _.get(props.module, "title", "");
});

const moduleSubTitle = computed(() => {
    return _.get(props.module, "subTitle", "");
});

const moduleChartHeight = computed(() => {
    return _.get(props.module, "chartHeight", 300);
});

const moduleData = computed(() => {
    return _.get(props.module, "data", []);
});

const moduleFilters = computed(() => {
    return _.get(props.module, "filters", []);
});

const moduleGlobalFilters = computed(() => {
    return moduleFilters.value.map((filter) => {
        return {
            ...filter,
            object: false,
        };
    });
});

const moduleQuery = computed(() => {
    return _.get(props.module, "query", {});
});

const moduleDefaultQuery = computed(() => {
    return _.get(props.module, "defaultQuery", {});
});

const onFilterSubmit = (query) => {
    emit("filter", { query, module: props.module });
};

/*const series = [
    {
        name: "Net Profit",
        data: [44, 55, 57, 56, 61, 58],
    },
    {
        name: "Revenue",
        data: [76, 85, 101, 98, 87, 105],
    },
];*/

const series = computed(() => {
    return _.get(moduleData.value, "series", []);
});

const chartOptions = () => {
    const labelColor = getCSSVariableValue("--kt-gray-500");
    const borderColor = getCSSVariableValue("--kt-gray-200");
    const baseColor = getCSSVariableValue("--kt-success");
    const secondaryColor = getCSSVariableValue("--kt-danger");

    const categories = _.get(moduleData.value, "categories", []);

    return {
        chart: {
            fontFamily: "inherit",
            type: "bar",
            toolbar: {
                show: false,
            },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "30%",
                borderRadius: 5,
            },
        },
        legend: {
            show: false,
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            show: true,
            width: 2,
            colors: ["transparent"],
        },
        xaxis: {
            categories: categories,
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
            labels: {
                style: {
                    colors: labelColor,
                    fontSize: "12px",
                },
            },
        },
        yaxis: {
            labels: {
                style: {
                    colors: labelColor,
                    fontSize: "12px",
                },
            },
        },
        fill: {
            opacity: 1,
        },
        states: {
            normal: {
                filter: {
                    type: "none",
                    value: 0,
                },
            },
            hover: {
                filter: {
                    type: "none",
                    value: 0,
                },
            },
            active: {
                allowMultipleDataPointsSelection: false,
                filter: {
                    type: "none",
                    value: 0,
                },
            },
        },
        tooltip: {
            style: {
                fontSize: "12px",
            },
            y: {
                formatter: function (val) {
                    return "$" + val;
                },
            },
        },
        colors: [baseColor, secondaryColor],
        grid: {
            borderColor: borderColor,
            strokeDashArray: 4,
            yaxis: {
                lines: {
                    show: true,
                },
            },
        },
    };
};
</script>
