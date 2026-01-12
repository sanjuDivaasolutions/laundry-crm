<template>
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div
            id="kt_app_sidebar_menu_wrapper"
            class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
            data-kt-scroll="true"
            data-kt-scroll-activate="true"
            data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu"
            data-kt-scroll-offset="5px"
            data-kt-scroll-save-state="true"
        >
            <div
                id="#kt_app_sidebar_menu"
                class="menu menu-column menu-rounded menu-sub-indention px-3"
                data-kt-menu="true"
            >
                <template v-for="(item, i) in MainMenuConfig" :key="i">
                    <div
                        v-if="item.heading && $can(item.route)"
                        class="menu-item pt-5"
                    >
                        <div class="menu-content">
                            <span
                                class="menu-heading fw-bold text-uppercase fs-7"
                            >
                                {{ $t(item.heading) }}
                            </span>
                        </div>
                    </div>
                    <template v-for="(menuItem, j) in item.pages" :key="j">
                        <template
                            v-if="menuItem.heading && $can(menuItem.gate)"
                        >
                            <div class="menu-item">
                                <router-link
                                    v-if="menuItem.route"
                                    class="menu-link"
                                    active-class="active"
                                    :to="menuItem.route"
                                >
                                    <span class="menu-icon">
                                        <FormIcon
                                            v-if="menuItem.icon"
                                            :icon="menuItem.icon"
                                            :no-margin="true"
                                        />
                                    </span>
                                    <span class="menu-title">{{
                                        $t(menuItem.heading)
                                    }}</span>
                                </router-link>
                            </div>
                        </template>
                        <div
                            v-if="
                                menuItem.sectionTitle &&
                                menuItem.route &&
                                $can(menuItem.gate)
                            "
                            :class="{ show: hasActiveChildren(menuItem) }"
                            class="menu-item menu-accordion"
                            data-kt-menu-sub="accordion"
                            data-kt-menu-trigger="click"
                        >
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <FormIcon
                                        v-if="menuItem.icon"
                                        :icon="menuItem.icon"
                                        :no-margin="true"
                                    />
                                </span>
                                <span class="menu-title">{{
                                    $t(menuItem.sectionTitle)
                                }}</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div
                                :class="{
                                    show: hasActiveChildren(menuItem),
                                }"
                                class="menu-sub menu-sub-accordion"
                            >
                                <template
                                    v-for="(item2, k) in menuItem.sub"
                                    :key="k"
                                >
                                    <div
                                        v-if="item2.heading && $can(item2.gate)"
                                        class="menu-item"
                                    >
                                        <router-link
                                            v-if="item2.route"
                                            class="menu-link"
                                            active-class="active"
                                            :to="item2.route"
                                        >
                                            <span class="menu-bullet">
                                                <span
                                                    class="bullet bullet-dot"
                                                ></span>
                                            </span>
                                            <span class="menu-title">{{
                                                $t(item2.heading)
                                            }}</span>
                                        </router-link>
                                    </div>
                                    <div
                                        v-if="
                                            item2.sectionTitle &&
                                            item2.route &&
                                            $can(item2.gate)
                                        "
                                        :class="{
                                            show: hasActiveChildren(item2),
                                        }"
                                        class="menu-item menu-accordion"
                                        data-kt-menu-sub="accordion"
                                        data-kt-menu-trigger="click"
                                    >
                                        <span class="menu-link">
                                            <span class="menu-bullet">
                                                <span
                                                    class="bullet bullet-dot"
                                                ></span>
                                            </span>
                                            <span class="menu-title">{{
                                                $t(item2.sectionTitle)
                                            }}</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div
                                            :class="{
                                                show: hasActiveChildren(item2),
                                            }"
                                            class="menu-sub menu-sub-accordion"
                                        >
                                            <template
                                                v-for="(item3, k) in item2.sub"
                                                :key="k"
                                            >
                                                <div
                                                    v-if="item3.heading"
                                                    class="menu-item"
                                                >
                                                    <router-link
                                                        v-if="
                                                            item3.route &&
                                                            $can(item3.gate)
                                                        "
                                                        class="menu-link"
                                                        active-class="active"
                                                        :to="item3.route"
                                                    >
                                                        <span
                                                            class="menu-bullet"
                                                        >
                                                            <span
                                                                class="bullet bullet-dot"
                                                            ></span>
                                                        </span>
                                                        <span
                                                            class="menu-title"
                                                            >{{
                                                                $t(
                                                                    item3.heading
                                                                )
                                                            }}</span
                                                        >
                                                    </router-link>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import MainMenuConfig from "@/core/config/MainMenuConfig";
import { sidebarMenuIcons } from "@/core/helpers/config";
import { useI18n } from "vue-i18n";

export default defineComponent({
    name: "sidebar-menu",
    components: {},
    setup() {
        const route = useRoute();
        const scrollElRef = ref<null | HTMLElement>(null);

        onMounted(() => {
            if (scrollElRef.value) {
                scrollElRef.value.scrollTop = 0;
            }
        });

        const hasActiveChildren = (item: object) => {
            let result = false;
            if (item.sub) {
                item.sub.forEach((subItem: any) => {
                    if (subItem.route) {
                        if (subItem.route === route.path) {
                            result = true;
                        }
                    }
                });
            }
            return result;
        };

        const hasAccess = (gate: string) => {
            const gates = gate.split(",");
            gates.forEach((gate) => {
                const has = $can(gate);
                if (has) {
                    return true;
                }
            });
            return false;
        };

        return {
            hasActiveChildren,
            MainMenuConfig,
            sidebarMenuIcons,
            hasAccess,
        };
    },
});
</script>
