<template>
    <div
        v-if="pageTitleDisplay"
        :class="`page-title d-flex flex-${pageTitleDirection} justify-content-center flex-wrap me-3`"
    >
        <template v-if="pageTitle">
            <h1
                class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0"
            >
                {{ pageTitle }}
            </h1>

            <span
                v-if="
                    pageTitleDirection === 'row' && pageTitleBreadcrumbDisplay
                "
                class="h-20px border-gray-200 border-start mx-3"
            ></span>

            <ul
                v-if="breadcrumbs && pageTitleBreadcrumbDisplay"
                class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1"
            >
                <li class="breadcrumb-item text-muted">
                    <router-link
                        to="/dashboard"
                        class="text-muted text-hover-primary"
                        >Dashboard</router-link
                    >
                </li>
                <template v-for="(item, i) in breadcrumbs" :key="i">
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <template v-if="typeof item === 'string'">{{
                            item
                        }}</template>
                        <router-link
                            v-if="typeof item === 'object'"
                            class="text-muted text-hover-primary"
                            :to="{ name: item.route }"
                            >{{ item.label }}</router-link
                        >
                    </li>
                </template>
            </ul>
        </template>
    </div>
    <div v-else class="align-items-stretch"></div>
</template>

<script lang="ts">
import {
    defineComponent,
    computed,
    ref,
    onMounted,
    onBeforeUnmount,
} from "vue";
import {
    pageTitleDisplay,
    pageTitleBreadcrumbDisplay,
    pageTitleDirection,
} from "@/core/helpers/config";
import { useRoute } from "vue-router";
import i18n from "@/core/plugins/i18n";
import emitter from "@/core/plugins/mitt";

export default defineComponent({
    name: "layout-page-title",
    components: {},
    setup() {
        const pTitle = ref(null);

        const route = useRoute();

        const pageTitle = computed(() => {
            return pTitle.value || i18n.global.t(route.meta.pageTitle);
        });

        const breadcrumbs = computed(() => {
            return route.meta.breadcrumbs;
        });

        onMounted(() => {
            emitter.on("update-page-title", (title) => {
                pTitle.value = title;
            });
        });

        onBeforeUnmount(() => {
            emitter.off("update-page-title");
        });

        return {
            pageTitle,
            breadcrumbs,
            pageTitleDisplay,
            pageTitleBreadcrumbDisplay,
            pageTitleDirection,
        };
    },
});
</script>
