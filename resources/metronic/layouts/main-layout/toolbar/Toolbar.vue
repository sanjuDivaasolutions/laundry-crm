<template>
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div
            id="kt_app_toolbar_container"
            class="app-container d-flex flex-stack"
            :class="{
                'container-fluid': toolbarWidthFluid,
                'container-xxl': !toolbarWidthFluid,
            }"
        >
            <KTPageTitle />
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <template
                    v-if="hasActionButtons"
                    v-for="(s, idx) in actionButtons"
                    :key="idx"
                >
                    <router-link
                        v-if="s.type === 'link'"
                        v-tooltip="s.label"
                        :to="s.action"
                        class="btn btn-sm fw-bold btn-primary"
                    >
                        {{ s.label }}
                    </router-link>
                    <a
                        v-if="s.type === 'event'"
                        v-tooltip="s.label"
                        @click.prevent="emitter.emit(s.action, s.actionPayload)"
                        class="btn btn-sm fw-bold btn-primary"
                        href="#"
                        >{{ s.label }}</a
                    >
                </template>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, computed } from "vue";
import { toolbarWidthFluid } from "@/core/helpers/config";
import KTPageTitle from "@/layouts/main-layout/toolbar/PageTitle.vue";
import { usePageStore } from "@/stores/page";
import emitter from "@/core/plugins/mitt";

export default defineComponent({
    name: "layout-toolbar",
    components: {
        KTPageTitle,
    },
    setup() {
        const page = usePageStore();
        const hasActionButtons = computed(() => {
            return page.getConfig("hasActionButtons");
        });

        const actionButtons = computed(() => {
            return page.getConfig("actionButtons");
        });

        return {
            toolbarWidthFluid,
            hasActionButtons,
            actionButtons,
            emitter,
        };
    },
});
</script>
