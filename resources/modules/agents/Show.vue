<template>
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                <div class="me-7 mb-4">
                    <div
                        class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative"
                    >
                        <img src="/media/avatars/300-1.jpg" alt="image" />
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
                            <div class="d-flex align-items-center mb-2">
                                <a
                                    href="javascript:void(0);"
                                    class="text-gray-800 text-hover-primary fs-2 fw-bold me-1"
                                    >{{ "Heading" }}</a
                                >
                                <a
                                    href="#"
                                    title="Blue tick"
                                >
                                    <span
                                        class="svg-icon svg-icon-1 svg-icon-primary"
                                    >
                                        <inline-svg
                                            src="/media/icons/duotune/general/gen026.svg"
                                        />
                                    </span>
                                </a>

                                <a
                                    v-if="false"
                                    href="#"
                                    class="btn btn-sm btn-light-success fw-bold ms-2 fs-8 py-1 px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_upgrade_plan"
                                    >Upgrade to Pro</a
                                >
                            </div>

                            <div
                                class="d-flex flex-wrap fw-semobold fs-6 mb-4 pe-2"
                            >
                                <a
                                    href="#"
                                    class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
                                >
                                    <span class="svg-icon svg-icon-4 me-1">
                                        <inline-svg
                                            src="/media/icons/duotune/communication/com006.svg"
                                        />
                                    </span>
                                    {{ 'Sample Code' }}
                                </a>
                                <a
                                    href="#"
                                    class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2"
                                >
                                    <span class="svg-icon svg-icon-4 me-1">
                                        <inline-svg
                                            src="/media/icons/duotune/general/gen018.svg"
                                        />
                                    </span>
                                    {{ 'another string' }}
                                </a>
                                <a
                                    v-if="false"
                                    href="#"
                                    class="d-flex align-items-center text-gray-400 text-hover-primary mb-2"
                                >
                                    <span class="svg-icon svg-icon-4 me-1">
                                        <inline-svg
                                            src="/media/icons/duotune/communication/com011.svg"
                                        />
                                    </span>
                                    max@kt.com
                                </a>
                            </div>
                        </div>
                        <div class="d-flex my-4">
                            <RouterLink
                                title="Back to Agents"
                                :to="{ name: 'agents.index' }"
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

                    <div class="d-flex flex-wrap flex-stack">
                        <div class="d-flex flex-column flex-grow-1 pe-8">
                            <div class="d-flex flex-wrap">
                                <div
                                    class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
                                >
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="fs-2 fw-bold"
                                            data-kt-countup="true"
                                            data-kt-countup-value="4500"
                                            data-kt-countup-prefix="$"
                                        >
                                            ₹ 45,79,869
                                        </div>
                                    </div>

                                    <div class="fw-semobold fs-6 text-gray-400">
                                        Order Total
                                    </div>
                                </div>

                                <div
                                    v-if="false"
                                    class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
                                >
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="fs-2 fw-bold"
                                            data-kt-countup="true"
                                            data-kt-countup-value="75"
                                        >
                                            75
                                        </div>
                                    </div>

                                    <div class="fw-semobold fs-6 text-gray-400">
                                        Orders
                                    </div>
                                </div>

                                <div
                                    class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
                                >
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="fs-2 fw-bold"
                                            data-kt-countup="true"
                                            data-kt-countup-value="60"
                                            data-kt-countup-prefix="%"
                                        >
                                            {{ "-" }}
                                        </div>
                                    </div>

                                    <div class="fw-semobold fs-6 text-gray-400">
                                        Risk Score
                                    </div>
                                </div>
                                <div
                                    class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
                                >
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="fs-2 fw-bold"
                                            data-kt-countup="true"
                                            data-kt-countup-value="60"
                                            data-kt-countup-prefix="%"
                                        >
                                            {{ "-" }}
                                        </div>
                                    </div>

                                    <div class="fw-semobold fs-6 text-gray-400">
                                        Agreement Date
                                    </div>
                                </div>
                                <div
                                    class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3"
                                >
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="fs-2 fw-bold"
                                            data-kt-countup="true"
                                            data-kt-countup-value="60"
                                            data-kt-countup-prefix="%"
                                        >
                                            {{ entry.assessment_at || "-" }}
                                        </div>
                                    </div>

                                    <div class="fw-semobold fs-6 text-gray-400">
                                        Assessment Date
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="false"
                            class="d-flex align-items-center w-200px w-sm-300px flex-column mt-3"
                        >
                            <div
                                class="d-flex justify-content-between w-100 mt-auto mb-2"
                            >
                                <span class="fw-semobold fs-6 text-gray-400"
                                    >Profile Completion</span
                                >
                                <span class="fw-bold fs-6">50%</span>
                            </div>

                            <div class="h-5px mx-3 w-100 bg-light mb-3">
                                <div
                                    class="bg-success rounded h-5px"
                                    role="progressbar"
                                    style="width: 50%"
                                    aria-valuenow="50"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                ></div>
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
                                name: 'agents.show',
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
</template>

<script setup>
import { useModuleFormStore } from "@modules@/agents/agentsFormStore";
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
