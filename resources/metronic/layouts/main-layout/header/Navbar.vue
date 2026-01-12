<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 07/01/25, 5:39 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div class="app-navbar flex-shrink-0">
        <div
            v-if="false"
            class="app-navbar-item align-items-stretch ms-1 ms-lg-3"
        >
            <Search />
        </div>
        <div class="app-navbar-item ms-1 ms-lg-3">
            <a
                href="#"
                class="btn btn-icon btn-active-light-primary btn-custom w-30px h-30px w-md-40px h-md-40px border rounded"
                data-kt-menu-trigger="{default:'click', lg: 'hover'}"
                data-kt-menu-attach="parent"
                data-kt-menu-placement="bottom-end"
            >
                <FormIcon
                    v-if="!isLanguageLoading"
                    icon="feather:globe"
                />
                <span
                    v-else
                    class="spinner-border spinner-border-sm"
                    role="status"
                >
                    <span class="visually-hidden">Loading...</span>
                </span>
            </a>
            <LanguageSwitcher />
        </div>
        <div class="app-navbar-item ms-1 ms-lg-3">
            <CompanySelection :enabled="true" />
        </div>
        <div class="app-navbar-item ms-1 ms-lg-3">
            <a
                href="#"
                class="btn btn-icon btn-active-light-primary btn-custom w-30px h-30px w-md-40px h-md-40px border rounded"
                data-kt-menu-trigger="{default:'click', lg: 'hover'}"
                data-kt-menu-attach="parent"
                data-kt-menu-placement="bottom-end"
            >
                <FormIcon
                    :icon="
                        themeMode === 'light' ? 'feather:sun' : 'feather:moon'
                    "
                />
            </a>
            <ThemeModeSwitcher />
        </div>
        <div
            class="app-navbar-item ms-1 ms-lg-3"
            id="kt_header_user_menu_toggle"
        >
            <div
                class="cursor-pointer symbol symbol-35px symbol-md-40px"
                data-kt-menu-trigger="click"
                data-kt-menu-attach="parent"
                data-kt-menu-placement="bottom-end"
            >
                <img
                    :src="`https://ui-avatars.com/api/?name=${user.name}`"
                    alt="user"
                />
            </div>
            <UserMenu />
        </div>
        <div
            class="app-navbar-item d-lg-none ms-2 me-n3"
            title="Show header menu"
        >
            <div
                class="btn btn-icon btn-active-color-primary w-35px h-35px"
                id="kt_app_header_menu_toggle"
            >
                <FormIcon icon="feather:menu" />
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { computed, defineComponent } from "vue";
import Search from "@/layouts/main-layout/search/Search.vue";
import NotificationMenu from "@/layouts/main-layout/menus/NotificationsMenu.vue";
import QuickLinksMenu from "@/layouts/main-layout/menus/QuickLinksMenu.vue";
import UserMenu from "@/layouts/main-layout/menus/UserAccountMenu.vue";
import ThemeModeSwitcher from "@/layouts/main-layout/theme-mode/ThemeModeSwitcher.vue";
import LanguageSwitcher from "@/layouts/main-layout/language-switcher/LanguageSwitcher.vue";
import { useThemeStore } from "@/stores/theme";
import { useAuthStore } from "@/stores/auth";
import { useI18nStore } from "@/stores/i18n";
import CompanySelection from "@/layouts/main-layout/header/CompanySelection.vue";

export default defineComponent({
    name: "header-navbar",
    components: {
        CompanySelection,
        Search,
        NotificationMenu,
        QuickLinksMenu,
        UserMenu,
        ThemeModeSwitcher,
        LanguageSwitcher,
    },
    setup() {
        const store = useThemeStore();
        const authStore = useAuthStore();
        const i18nStore = useI18nStore();

        const themeMode = computed(() => {
            return store.mode;
        });

        const user = computed(() => {
            return authStore.user;
        });

        const isLanguageLoading = computed(() => {
            return i18nStore.isLoading;
        });

        return {
            themeMode,
            user,
            isLanguageLoading,
        };
    },
});
</script>
