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
  -  *  Last modified: 17/10/24, 9:34 am
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <router-link to="/" class="d-flex align-items-center">
            <img
                v-if="
                    layout === 'dark-sidebar' ||
                    (themeMode === 'dark' && layout === 'light-sidebar')
                "
                alt="Logo"
                :src="$url(`/images/logo/default-dark.svg`)"
                class="h-40px app-sidebar-logo-default"
            />
            <img
                v-if="themeMode === 'light' && layout === 'light-sidebar'"
                alt="Logo"
                :src="$url(`/images/logo/default.svg`)"
                class="h-40px app-sidebar-logo-default"
            />
            <img
                alt="Logo"
                :src="$url(`/images/logo/default-small.svg`)"
                class="h-40px app-sidebar-logo-minimize"
            />
        </router-link>
        <div
            v-if="sidebarToggleDisplay"
            id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-active-color-primary body-bg h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
            data-kt-toggle="true"
            data-kt-toggle-state="active"
            data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize"
        >
            <FormIcon icon="feather:chevrons-left" class="rotate-180" />
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { layout, sidebarToggleDisplay, themeMode } from "@/core/helpers/config";

export default defineComponent({
    name: "sidebar-logo",
    components: {},
    setup() {
        return {
            layout,
            themeMode,
            sidebarToggleDisplay,
        };
    },
});
</script>
