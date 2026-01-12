<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 07/01/25, 5:42 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fs-2 fw-bold mb-0">{{ moduleTitle }}</h1>
                <div class="d-flex justify-content-end">
                    <GlobalFilter
                        id="overview-filters"
                        v-if="moduleFilters.length > 0"
                        :loading="moduleLoading"
                        :query="moduleQuery"
                        :defaultQuery="moduleDefaultQuery"
                        :filters="moduleGlobalFilters"
                        @filter-submit="onFilterSubmit"
                    />
                </div>
            </div>
        </div>
        <div class="col-2" v-for="m in moduleData">
            <SummaryBoxSingle
                :key="m.id"
                :label="m.label"
                :value="m.value"
                :variant="m.variant"
                :isLoading="moduleLoading"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import SummaryBoxSingle from "@common@/components/SummaryBoxSingle.vue";
import GlobalFilter from "@/components/magic-datatable/filter-plugin/GlobalFilter.vue";

const emit = defineEmits(["filter"]);

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
});

const moduleLoading = computed(() => {
    return _.get(props.module, "isLoading", false);
});

const moduleTitle = computed(() => {
    return _.get(props.module, "title", "");
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
</script>

<style scoped></style>
