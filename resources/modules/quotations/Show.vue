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
  -  *  Last modified: 21/01/25, 6:10 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="container">
        <div class="card mb-5 mb-xl-10">
            <div class="card-body pt-9 pb-0">
                <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                    <div class="flex-grow-1">
                        <div
                            class="d-flex justify-content-between align-items-start flex-wrap mb-2"
                        >
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center mb-2">
                                    <a
                                        href="javascript:void(0);"
                                        class="text-gray-800 text-hover-primary fs-2 fw-bold me-1"
                                        >{{ entry.order_no }}</a
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
                            </div>
                            <div class="d-flex align-items-center">
                                <!-- Convert to Sales Order Button -->
                                <button
                                    v-if="entry && entry.status && entry.status.status !== 'converted' && can('sales_order_create')"
                                    class="btn btn-sm btn-success me-3"
                                    @click="openConvertModal"
                                    :title="$t('Convert to Sales Order')"
                                >
                                    <FormIcon icon="feather:arrow-right" />
                                    {{ $t('Convert to Sales Order') }}
                                </button>
                                
                                <RouterLink
                                    title="Back to Quotations"
                                    :to="{ name: 'quotations.index' }"
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
        />
        
        <!-- Convert to Sales Order Modal -->
        <ConvertToSalesOrderModal
            v-if="showConvertModal"
            :quotation="entry"
            @close="showConvertModal = false"
            @converted="onConverted"
        />
    </div>
</template>

<script setup>
import { useModuleFormStore } from "@modules@/quotations/quotationsFormStore";
import Overview from "./ShowComponents/Overview.vue";
import ConvertToSalesOrderModal from "./components/ConvertToSalesOrderModal.vue";
import { computed, onBeforeMount, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAbility } from "@casl/vue";
import FormIcon from "@common@/components/form/FormIcon.vue";
import emitter from "@/core/plugins/mitt";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();
const { can } = useAbility();

const moduleFormStore = useModuleFormStore();
const entry = computed(() => moduleFormStore.entry);
const showFields = computed(() => moduleFormStore.showFields);

const tab = ref("overview");
const id = ref(null);
const showConvertModal = ref(false);

const switchTab = (tabName) => {
    tab.value = tabName;
};

const refreshShowData = () => {
    moduleFormStore.loadShowData(id.value);
};

const openConvertModal = () => {
    showConvertModal.value = true;
};

const onConverted = (salesOrder) => {
    showConvertModal.value = false;
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Quotation successfully converted to sales order',
        showConfirmButton: true,
        confirmButtonText: 'View Sales Order',
    }).then((result) => {
        if (result.isConfirmed) {
            router.push({
                name: 'sales-orders.show',
                params: { id: salesOrder.id }
            });
        } else {
            // Refresh quotation data to update status
            refreshShowData();
        }
    });
};

onBeforeMount(() => {
    id.value = route.params.id;
    moduleFormStore.loadShowData(id.value);
    const qsTab = route.params.tab;
    if (qsTab) {
        tab.value = qsTab;
    }
    emitter.on("refresh-show-data", refreshShowData);
});
</script>
