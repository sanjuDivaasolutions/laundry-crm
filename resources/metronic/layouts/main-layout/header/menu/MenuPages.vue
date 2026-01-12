<template>
    <template v-for="(item, i) in MainMenuConfig" :key="i">
        <template v-if="!item.heading && item.topMenu">
            <template v-for="(menuItem, j) in item.pages" :key="j">
                <div
                    v-if="menuItem.heading && $can(menuItem.gate)"
                    class="menu-item me-lg-1"
                >
                    <router-link
                        v-if="menuItem.route"
                        class="menu-link"
                        :to="menuItem.route"
                        active-class="active"
                    >
                        <span class="menu-title">{{
                            $t(menuItem.heading)
                        }}</span>
                    </router-link>
                </div>
            </template>
        </template>
        <div
            v-if="item.heading && item.topMenu && $can(item.route)"
            data-kt-menu-trigger="click"
            data-kt-menu-placement="bottom-start"
            class="menu-item menu-lg-down-accordion me-lg-1"
        >
            <span
                v-if="item.route"
                class="menu-link py-3"
                :class="{ active: hasActiveChildren(item.route) }"
            >
                <span class="menu-title">{{ $t(item.heading) }}</span>
                <span class="menu-arrow d-lg-none"></span>
            </span>
            <div
                class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px"
            >
                <template v-for="(menuItem, j) in item.pages" :key="j">
                    <div
                        v-if="menuItem.sectionTitle"
                        data-kt-menu-trigger="{default:'click', lg: 'hover'}"
                        data-kt-menu-placement="right-start"
                        class="menu-item menu-lg-down-accordion"
                    >
                        <span
                            v-if="menuItem.route"
                            class="menu-link py-3"
                            :class="{
                                active: hasActiveChildren(menuItem.route),
                            }"
                        >
                            <span class="menu-icon">
                                <i
                                    v-if="headerMenuIcons === 'font'"
                                    :class="menuItem.fontIcon"
                                    class="bi fs-3"
                                ></i>
                                <span
                                    v-if="headerMenuIcons === 'svg'"
                                    class="svg-icon svg-icon-2"
                                >
                                    <inline-svg :src="menuItem.svgIcon" />
                                </span>
                            </span>
                            <span class="menu-title">{{
                                $t(menuItem.sectionTitle)
                            }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div
                            class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg py-lg-4 w-lg-225px"
                        >
                            <template
                                v-for="(menuItem1, k) in menuItem.sub"
                                :key="k"
                            >
                                <div
                                    v-if="menuItem1.sectionTitle"
                                    data-kt-menu-trigger="{default:'click', lg: 'hover'}"
                                    data-kt-menu-placement="right-start"
                                    class="menu-item menu-lg-down-accordion"
                                >
                                    <span
                                        v-if="menuItem1.route"
                                        class="menu-link py-3"
                                        :class="{
                                            active: hasActiveChildren(
                                                menuItem1.route
                                            ),
                                        }"
                                    >
                                        <span class="menu-bullet">
                                            <span
                                                class="bullet bullet-dot"
                                            ></span>
                                        </span>
                                        <span class="menu-title">{{
                                            $t(menuItem1.sectionTitle)
                                        }}</span>
                                        <span class="menu-arrow"></span>
                                    </span>
                                    <div
                                        class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg py-lg-4 w-lg-225px"
                                    >
                                        <template
                                            v-for="(
                                                menuItem2, l
                                            ) in menuItem1.sub"
                                            :key="l"
                                        >
                                            <div class="menu-item">
                                                <router-link
                                                    v-if="
                                                        menuItem2.route &&
                                                        menuItem2.heading
                                                    "
                                                    class="menu-link py-3"
                                                    active-class="active"
                                                    :to="menuItem2.route"
                                                >
                                                    <span class="menu-bullet">
                                                        <span
                                                            class="bullet bullet-dot"
                                                        ></span>
                                                    </span>
                                                    <span class="menu-title">{{
                                                        $t(menuItem2.heading)
                                                    }}</span>
                                                </router-link>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div v-if="menuItem1.heading" class="menu-item">
                                    <router-link
                                        v-if="menuItem1.route"
                                        class="menu-link"
                                        active-class="active"
                                        :to="menuItem1.route"
                                    >
                                        <span class="menu-bullet">
                                            <span
                                                class="bullet bullet-dot"
                                            ></span>
                                        </span>
                                        <span class="menu-title">{{
                                            $t(menuItem1.heading)
                                        }}</span>
                                    </router-link>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div v-if="menuItem.heading" class="menu-item">
                        <router-link
                            v-if="menuItem.route && menuItem.route"
                            class="menu-link"
                            active-class="active"
                            :to="menuItem.route"
                        >
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <inline-svg
                                        src="/media/icons/duotune/layouts/lay009.svg"
                                    />
                                </span>
                            </span>
                            <span class="menu-title">{{
                                $t(menuItem.heading)
                            }}</span>
                        </router-link>
                    </div>
                </template>
            </div>
        </div>
    </template>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { useRoute } from "vue-router";
import MainMenuConfig from "@/core/config/MainMenuConfig";
import { headerMenuIcons } from "@/core/helpers/config";

export default defineComponent({
    name: "KTMenu",
    components: {},
    setup() {
        const route = useRoute();

        const hasActiveChildren = (match: string) => {
            return route.path.indexOf(match) !== -1;
        };

        return {
            hasActiveChildren,
            headerMenuIcons,
            MainMenuConfig,
        };
    },
});
</script>
