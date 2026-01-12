<template>
    <div v-if="footerDisplay" id="kt_app_footer" class="app-footer">
        <div
            class="app-container d-flex flex-column flex-md-row flex-center flex-md-stack py-3"
            :class="{
                'container-fluid': footerWidthFluid,
                'container-xxl': !footerWidthFluid,
            }"
        >
            <div class="text-dark order-2 order-md-1">
                <span class="text-muted fw-semibold me-1"
                    >{{ copyright.year }} ©
                </span>
                <a
                    :href="copyright.url"
                    target="_blank"
                    class="text-gray-800 text-hover-primary"
                    >{{ copyright.label }}</a
                >
            </div>
            <ul
                class="menu menu-gray-600 menu-hover-primary fw-semibold order-1"
            >
                <li
                    class="menu-item"
                    v-for="(l, idx) in footerLinks"
                    :key="idx"
                >
                    <a :href="l.url" target="_blank" class="menu-link px-2">{{
                        l.label
                    }}</a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { footerWidthFluid, footerDisplay } from "@/core/helpers/config";
import { copyright, footerLinks } from "@common@/data/footer";

export default defineComponent({
    name: "theme-footer",
    components: {},
    setup() {
        return {
            copyright,
            footerLinks,
            footerWidthFluid,
            footerDisplay,
        };
    },
});
</script>
