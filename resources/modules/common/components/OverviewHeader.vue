<template>
    <div class="card mb-3 mb-xl-3">
        <div class="card-body pt-5 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap mb-4">
                <div class="flex-grow-1">
                    <div
                        class="d-flex justify-content-between align-items-start flex-wrap"
                    >
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <a
                                    class="text-gray-800 text-hover-primary fs-2 fw-bold me-1"
                                    href="javascript:void(0);"
                                    >{{ $getDisplayValue(entry, nameField) }}</a
                                >
                            </div>

                            <div class="d-flex flex-wrap fw-semobold fs-6 pe-2">
                                <a
                                    class="d-flex align-items-center text-gray-400 text-hover-primary me-5"
                                    href="#"
                                >
                                    <span>{{
                                        $getDisplayValue(entry, codeField)
                                    }}</span>
                                </a>
                            </div>
                        </div>
                        <div class="d-flex">
                            <RouterLink
                                v-if="backRoute"
                                :title="backRouteTitle"
                                :to="backRoute"
                                class="btn btn-sm btn-primary me-3"
                            >
                                <FormIcon icon="feather:arrow-left" />
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex overflow-auto h-40px">
                <ul
                    class="nav nav-stretch nav-tabs border-transparent fs-5 fw-bold flex-nowrap"
                >
                    <li v-for="t in tabs" class="nav-item">
                        <router-link
                            :class="activeTab === t.id ? 'active' : ''"
                            :to="{
                                name: t.route,
                                params: { id: id, tab: t.id },
                            }"
                            class="nav-link text-active-primary me-6"
                            @click.prevent="switchTab(t.id)"
                        >
                            {{ t.label }}
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { RouterLink, useRoute } from "vue-router";
import FormIcon from "@common@/components/form/FormIcon.vue";
import { computed, onMounted } from "vue";
import { $getDisplayValue } from "../../../metronic/core/helpers/utility";

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
    nameField: {
        type: String,
        default: "name",
    },
    codeField: {
        type: String,
        default: "code",
    },
    backRoute: {
        type: String,
        default: null,
    },
    backRouteTitle: {
        type: String,
        default: null,
    },
    id: {
        type: [String, Number],
        required: true,
    },
    tabs: {
        type: Array,
        default: () => [],
    },
    tab: {
        type: String,
        required: true,
    },
});

const route = useRoute();

const activeTab = computed(() => {
    return props.tab;
});

const emit = defineEmits(["update:tab"]);

const switchTab = (tabName) => {
    emit("update:tab", tabName);
};

onMounted(() => {
    const qsTab = route.params.tab || props.tab;
    if (qsTab) {
        switchTab(qsTab);
    }
});
</script>
<style scoped>
.nav-item .nav-link {
    margin-right: 10px !important;
    border: 1px solid var(--bs-gray-300) !important;
    border-bottom: 0 !important;
}

.nav-item .router-link-active,
.nav-item .nav-link.active {
    background-color: var(--bs-primary) !important;
    color: var(--bs-white) !important;
}
</style>
