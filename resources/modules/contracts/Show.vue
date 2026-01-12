<template>
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                <div class="me-7 mb-4">
                    <div
                        class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative"
                    >
                        <div
                            v-if="false"
                            class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-white h-20px w-20px"
                        ></div>
                    </div>
                </div>

                <div class="flex-grow-1">
                    <div
                        class="d-flex justify-content-between align-items-start flex-wrap mb-2"
                    >
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-0">
                                <a
                                    v-if="false"
                                    href="#"
                                    class="btn btn-sm btn-light-success fw-bold ms-0 fs-0 py-1 px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_upgrade_plan"
                                    >Upgrade to Pro</a
                                >
                            </div>
                        </div>
                        <div class="d-flex my-4">
                            <RouterLink
                                title="Back to Contracts"
                                :to="{ name: 'contracts.index' }"
                                class="btn btn-sm btn-primary me-3"
                                ><FormIcon icon="feather:arrow-left"
                            /></RouterLink>
                        </div>
                        <div v-if="false" class="d-flex my-4">
                            <a
                                href="#"
                                class="btn btn-sm btn-light me-2"
                                id="kt_user_follow_button"
                            >
                                <span class="svg-icon svg-icon-3 d-none">
                                    <inline-svg
                                        src="/media/icons/duotune/arrows/arr012.svg"
                                    />
                                </span>
                                Follow
                            </a>

                            <a
                                href="#"
                                class="btn btn-sm btn-primary me-3"
                                data-bs-toggle="modal"
                                data-bs-target="#kt_modal_offer_a_deal"
                                >Hire Me</a
                            >

                            <div class="me-0">
                                <button
                                    class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary"
                                    data-kt-menu-trigger="click"
                                    data-kt-menu-placement="bottom-end"
                                    data-kt-menu-flip="top-end"
                                >
                                    <i class="bi bi-three-dots fs-3"></i>
                                </button>
                                <Dropdown3></Dropdown3>
                            </div>
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
                                name: 'contracts.show',
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
                                name: 'contracts.show',
                                params: { id: id, tab: 'invoices' },
                            }"
                            @click.prevent="switchTab('invoices')"
                            class="nav-link text-active-primary me-6"
                            :class="tab === 'invoices' ? 'active' : ''"
                        >
                            Invoices
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
    <Invoices v-if="tab === 'invoices'" :id="id" :entry="entry" />
</template>

<script setup>
import { useModuleFormStore } from "@modules@/contracts/contractsFormStore";
import Overview from "./ShowComponents/Overview.vue";
import Invoices from "@modules@/contracts/ShowComponents/Invoices.vue";
import { computed, onBeforeMount, onBeforeUnmount, ref } from "vue";
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
    id.value = _.get(route, "params.id", null);
    moduleFormStore.loadShowData(id.value);
    const qsTab = route.params.tab;
    if (qsTab) {
        tab.value = qsTab;
    }
});

onBeforeUnmount(() => {
    moduleFormStore.resetEntry();
});
</script>
