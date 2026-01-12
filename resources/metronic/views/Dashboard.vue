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
  -  *  Last modified: 22/01/25, 10:16 am
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <!--    only view system maintenance in development mode-->
    <SystemMaintenance
        v-if="isDevMode"
        :initialLoad="initialLoad"
        :isLoading="isLoading"
        class="mb-10"
    />
    <CardContainer v-if="isLoading" :hide-header="true" :hide-footer="true">
        <h4 class="mb-0">Please wait..</h4>
    </CardContainer>
    <CardContainer
        v-if="!hasModules && initialLoad"
        :hide-header="true"
        :hide-footer="true"
    >
        <h4 class="mb-0">Modules not found</h4>
    </CardContainer>
    <div class="row">
        <div v-for="m in modules" :class="`col-${m.columns} mb-10`">
            <component
                :is="components[m.component]"
                :module="m"
                :isLoading="isLoading || m.isLoading"
                @filter="handleFilterSubmit"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import ApiService from "@/core/services/ApiService";
import { $catchResponse } from "@/core/helpers/utility";

import SummaryComponent from "@/views/dashboard-components/SummaryComponent.vue";
import BasicTableComponent from "@/views/dashboard-components/BasicTableComponent.vue";
import BarChartComponent from "@/views/dashboard-components/BarChartComponent.vue";
import LineChartComponent from "@/views/dashboard-components/LineChartComponent.vue";
import CardContainer from "@common@/components/CardContainer.vue";
import SystemMaintenance from "@/views/SystemMaintenance.vue";

const components = {
    SummaryComponent,
    BasicTableComponent,
    BarChartComponent,
    LineChartComponent,
};

const dashboardModules = ref([]);
const initialLoad = ref(false);
const isLoading = ref(false);

const modules = computed(() => {
    return dashboardModules.value;
});

const hasModules = computed(
    () => dashboardModules.value.length > 0 && initialLoad.value
);

const fetchModules = () => {
    isLoading.value = true;
    ApiService.get("dashboard-modules")
        .then((response) => {
            dashboardModules.value = response.data;
            initialLoad.value = true;
        })
        .catch((error) => {
            $catchResponse(error);
        })
        .finally(() => {
            isLoading.value = false;
        });
};

const handleFilterSubmit = (params) => {
    const module = params.module;
    const query = params.query;
    const idx = dashboardModules.value.findIndex((m) => m.id === module.id);

    dashboardModules.value[idx].isLoading = true;
    const queryParams = {
        module: module.module,
        ...query,
    };
    ApiService.get("dashboard-data", { params: queryParams })
        .then((response) => {
            dashboardModules.value[idx].data = _.get(response, "data.data", []);
            dashboardModules.value[idx].query = query;
        })
        .catch((error) => {
            $catchResponse(error);
        })
        .finally(() => {
            dashboardModules.value[idx].isLoading = false;
        });
};

const isDevMode = computed(() => {
    return process.env.NODE_ENV === "development";
});

onMounted(() => {
    fetchModules();
});
</script>
