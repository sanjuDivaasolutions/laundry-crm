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
  -  *  Last modified: 23/01/25, 6:24 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="container">
        <div class="card mb-5 mb-xl-10">
            <div class="card-body pt-5 pb-0">
                <div class="d-flex flex-wrap flex-sm-nowrap">
                    <div class="flex-grow-1">
                        <div
                            class="d-flex justify-content-between align-items-start flex-wrap"
                        >
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center">
                                    <a
                                        href="javascript:void(0);"
                                        class="text-gray-800 text-hover-primary fs-2 fw-bold me-1"
                                        >{{ entry.code }}</a
                                    >
                                    <a href="#" title="Blue tick">
                                        <span
                                            class="svg-icon svg-icon-1 svg-icon-primary"
                                        >
                                            <inline-svg
                                                src="/media/icons/duotune/general/gen026.svg"
                                            />
                                        </span>
                                    </a>
                                </div>

                                <div
                                    class="d-flex flex-wrap fw-semobold fs-6 mb-4 pe-2"
                                >
                                    <a
                                        href="#"
                                        class="d-flex align-items-center text-gray-400 text-hover-primary me-5"
                                    >
                                        <span class="svg-icon svg-icon-4 me-1">
                                            <inline-svg
                                                src="/media/icons/duotune/communication/com006.svg"
                                            />
                                        </span>
                                        <span>{{ entry.date }}</span>
                                    </a>
                                </div>
                            </div>
                            <div class="d-flex my-4">
                                <RouterLink
                                    title="Back to Packages"
                                    :to="{ name: 'packages.index' }"
                                    class="btn btn-sm btn-primary me-3"
                                    ><FormIcon icon="feather:arrow-left"
                                /></RouterLink>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex overflow-auto h-55px">
                    <ul
                        class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold flex-nowrap"
                    >
                        <li class="nav-item">
                            <router-link
                                :to="{
                                    name: 'sales-invoices.show',
                                    params: { id: id, tab: 'overview' },
                                }"
                                @click.prevent="switchTab('overview')"
                                class="nav-link text-active-primary me-6"
                                :class="tab === 'overview' ? 'active' : ''"
                            >
                                Overview
                            </router-link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <Overview
            v-if="tab === 'overview'"
            :id="id"
            :entry="entry"
            :fields="showFields"
            class="mb-10"
        />
    </div>
</template>

<script setup>
import { useModuleFormStore } from "@modules@/packages/packagesFormStore";
import Overview from "./ShowComponents/Overview.vue";
import { computed, onBeforeMount, ref } from "vue";
import { useRoute } from "vue-router";
import FormIcon from "@common@/components/form/FormIcon.vue";

const route = useRoute();

const moduleFormStore = useModuleFormStore();
const entry = computed(() => moduleFormStore.entry);
const showFields = computed(() => moduleFormStore.showFields);

const tab = ref("overview");
const id = ref(null);

const switchTab = (tabName) => {
    tab.value = tabName;
};

onBeforeMount(() => {
    id.value = route.params.id;
    moduleFormStore.loadShowData(id.value);
    const qsTab = route.params.tab;
    if (qsTab) {
        tab.value = qsTab;
    }
});
</script>
