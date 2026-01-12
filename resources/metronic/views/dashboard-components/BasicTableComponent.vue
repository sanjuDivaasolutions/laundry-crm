<!--
  - /*
  -  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 12/12/24, 9:57 am
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <h1 class="fs-2 fw-bold mb-0">{{ moduleTitle }}</h1>
            </div>
        </div>
        <div class="col-12">
            <div class="card p-5">
                <div class="table-responsive">
                    <table class="table table-striped gy-2">
                        <thead>
                            <tr class="fw-bold fs-6 text-gray-800">
                                <th
                                    v-for="column in moduleColumns"
                                    :key="column.id"
                                    :class="[column.class, column.thClass]"
                                >
                                    {{ column.label }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="moduleLoading">
                                <td :colspan="moduleData.length">
                                    <div
                                        class="d-flex justify-content-center align-items-center"
                                    >
                                        <div
                                            class="spinner-border text-primary"
                                            role="status"
                                        >
                                            <span class="visually-hidden"
                                                >Loading...</span
                                            >
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-else-if="moduleData.length === 0">
                                <td :colspan="moduleColumns.length">
                                    <div
                                        class="d-flex justify-content-center align-items-center"
                                    >
                                        <span>No data available</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-else v-for="row in moduleData" :key="row.id">
                                <td
                                    v-for="column in moduleColumns"
                                    :key="column.value"
                                    :class="[column.class, column.tdClass]"
                                >
                                    {{ row[column.value] || "-" }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

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

const moduleColumns = computed(() => {
    return _.get(props.module, "data.columns", []);
});

const moduleData = computed(() => {
    return _.get(props.module, "data.data", []);
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
