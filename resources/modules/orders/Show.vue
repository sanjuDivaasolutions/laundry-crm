<template>
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                <div class="me-7 mb-4">
                    <div
                        class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative"
                    >
                        <span class="symbol-label bg-light-info text-info fs-1 fw-bold">
                           #
                        </span>
                    </div>
                </div>

                <div class="flex-grow-1">
                    <div
                        class="d-flex justify-content-between align-items-start flex-wrap mb-2"
                    >
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <a
                                    href="javascript:void(0);"
                                    class="text-gray-800 text-hover-primary fs-2 fw-bold me-1"
                                    >{{ entry.order_number }}</a
                                >
                            </div>

                            <div
                                class="d-flex flex-wrap fw-semobold fs-6 mb-4 pe-2"
                            >
                                <a
                                    href="#"
                                    class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
                                >
                                    {{ entry.customer ? entry.customer.name : '' }}
                                </a>
                                <a
                                    href="#"
                                    class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
                                >
                                    {{ entry.order_date }}
                                </a>
                            </div>
                        </div>
                        <div class="d-flex my-4">
                            <RouterLink
                                title="Back to Orders"
                                :to="{ name: 'orders.index' }"
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
                                name: 'orders.show',
                                params: { id: id, tab: 'overview' },
                            }"
                            @click.prevent="switchTab('overview')"
                            class="nav-link text-active-primary me-6"
                            :class="tab === 'overview' ? 'active' : ''"
                        >
                            Overview
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link
                            :to="{
                                name: 'orders.show',
                                params: { id: id, tab: 'items' },
                            }"
                            @click.prevent="switchTab('items')"
                            class="nav-link text-active-primary me-6"
                            :class="tab === 'items' ? 'active' : ''"
                        >
                            Items
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link
                            :to="{
                                name: 'orders.show',
                                params: { id: id, tab: 'payments' },
                            }"
                            @click.prevent="switchTab('payments')"
                            class="nav-link text-active-primary me-6"
                            :class="tab === 'payments' ? 'active' : ''"
                        >
                            Payments
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link
                            :to="{
                                name: 'orders.show',
                                params: { id: id, tab: 'history' },
                            }"
                            @click.prevent="switchTab('history')"
                            class="nav-link text-active-primary me-6"
                            :class="tab === 'history' ? 'active' : ''"
                        >
                            History
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
    <ItemsList
        v-if="tab === 'items'"
        :items="entry.items || []"
    />
    <PaymentsList
        v-if="tab === 'payments'"
        :payments="entry.payments || []"
    />
    <StatusHistory
        v-if="tab === 'history'"
        :history="entry.history || []"
    />
</template>

<script setup>
import { useModuleFormStore } from "@modules@/orders/ordersFormStore";
import Overview from "./ShowComponents/Overview.vue";
import ItemsList from "./ShowComponents/ItemsList.vue";
import PaymentsList from "./ShowComponents/PaymentsList.vue";
import StatusHistory from "./ShowComponents/StatusHistory.vue";
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
