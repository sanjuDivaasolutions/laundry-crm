<!--
  - /*
  -  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 14/10/24, 5:18 pm
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div id="kt_app_header" v-if="headerDisplay" class="app-header">
        <div
            class="app-container d-flex align-items-stretch justify-content-between"
            :class="{
                'container-fluid': headerWidthFluid,
                'container-xxl': !headerWidthFluid,
            }"
        >
            <div
                v-if="layout === 'light-header' || layout === 'dark-header'"
                class="d-flex align-items-center flex-grow-1 flex-lg-grow-0 me-lg-15"
            >
                <router-link to="/">
                    <img
                        v-if="
                            themeMode === 'light' && layout === 'light-header'
                        "
                        alt="Logo"
                        src="/images/logo/default.svg"
                        class="h-40px h-lg-40px app-sidebar-logo-default"
                    />
                    <img
                        v-if="
                            layout === 'dark-header' ||
                            (themeMode === 'dark' && layout === 'light-header')
                        "
                        alt="Logo"
                        src="/images/logo/default-dark.svg"
                        class="h-40px h-lg-40px app-sidebar-logo-default"
                    />
                </router-link>
            </div>
            <div
                class="d-flex align-items-center d-lg-none ms-n2 me-2"
                title="Show sidebar menu"
            >
                <div
                    class="btn btn-icon btn-active-color-primary w-35px h-35px"
                    id="kt_app_sidebar_mobile_toggle"
                >
                    <FormIcon icon="feather:menu" />
                </div>
            </div>
            <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                <router-link to="/" class="d-lg-none">
                    <img
                        alt="Logo"
                        src="/images/logo/default-small.svg"
                        class="h-30px"
                    />
                </router-link>
            </div>
            <div
                class="d-flex align-items-stretch justify-content-between flex-lg-grow-1"
                id="kt_app_header_wrapper"
            >
                <HeaderMenu />
                <HeaderNavbar />
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import HeaderMenu from "@/layouts/main-layout/header/menu/Menu.vue";
import HeaderNavbar from "@/layouts/main-layout/header/Navbar.vue";
import {
    headerDisplay,
    headerWidthFluid,
    layout,
    themeMode,
} from "@/core/helpers/config";

export default defineComponent({
    name: "layout-header",
    components: {
        HeaderMenu,
        HeaderNavbar,
    },
    setup() {
        return {
            layout,
            headerWidthFluid,
            headerDisplay,
            themeMode,
        };
    },
});
</script>
