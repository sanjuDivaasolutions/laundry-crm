<template>
    <div
        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semobold py-4 fs-6 w-auto"
        data-kt-menu="true"
    >
        <div class="menu-item px-3">
            <div class="menu-content d-flex align-items-center px-3">
                <div class="symbol symbol-50px me-5">
                    <img
                        alt="Logo"
                        :src="`https://ui-avatars.com/api/?name=${user.name}`"
                    />
                </div>

                <div class="d-flex flex-column">
                    <div class="fw-bold d-flex align-items-center fs-5">
                        {{ user.name }}
                    </div>
                    <a
                        href="#"
                        class="fw-semobold text-muted text-hover-primary fs-7"
                        >{{ user.email }}</a
                    >
                </div>
            </div>
        </div>
        <div v-if="false" class="separator my-2"></div>
        <div v-if="false"
            class="menu-item px-5"
            data-kt-menu-trigger="hover"
            data-kt-menu-placement="left-start"
            data-kt-menu-flip="center, top"
        >
            <router-link to="/pages/profile/overview" class="menu-link px-5">
                <span class="menu-title position-relative">
                    Language
                    <span
                        class="fs-8 rounded bg-light px-3 py-2 position-absolute translate-middle-y top-50 end-0"
                    >
                        {{ currentLanguageLocale.name }}
                        <img
                            class="w-15px h-15px rounded-1 ms-2"
                            :src="currentLanguageLocale.flag"
                            alt="metronic"
                        />
                    </span>
                </span>
            </router-link>

            <div v-if="false" class="menu-sub menu-sub-dropdown w-175px py-4">
                <div v-for="l in languages" class="menu-item px-3">
                    <a
                        @click="setLang(l.locale)"
                        href="#"
                        class="menu-link d-flex px-5"
                        :class="{ active: currentLanguage === l.locale }"
                    >
                        <span class="symbol symbol-20px me-4">
                            <img
                                class="rounded-1"
                                :src="l.flag"
                                :alt="l.name"
                            />
                        </span>
                        {{ l.name }}
                    </a>
                </div>
            </div>
        </div>

        <div class="separator my-2"></div>

        <div class="menu-item px-5 my-1">
            <a
                href="#"
                @click.prevent="handleClearCache"
                class="menu-link px-5"
            >
                Clear cache
            </a>
        </div>

        <div class="menu-item px-5">
            <a @click="signOut()" class="menu-link px-5"> Sign Out </a>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, computed } from "vue";
// @ts-ignore
import i18n from "@/core/plugins/i18n";
// @ts-ignore
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";
// @ts-ignore
import ApiService from "@/core/services/ApiService";
// @ts-ignore
import { $catchResponse } from "@/core/helpers/utility";
// @ts-ignore
import { useConfigStore } from "@/stores/config";

export default defineComponent({
    name: "kt-user-menu",
    components: {},
    setup() {
        const router = useRouter();
        const store = useAuthStore();

        i18n.global.locale = localStorage.getItem("lang")
            ? (localStorage.getItem("lang") as string)
            : "en";

        const languages = {
            en: {
                flag: "/media/flags/united-states.svg",
                name: "English",
                locale: "en",
            },
            zh: {
                flag: "/media/flags/china.svg",
                name: "Chinese",
                locale: "zh",
            },
            /*es: {
                flag: "/media/flags/spain.svg",
                name: "Spanish",
            },
            de: {
                flag: "/media/flags/germany.svg",
                name: "German",
            },
            ja: {
                flag: "/media/flags/japan.svg",
                name: "Japanese",
            },
            fr: {
                flag: "/media/flags/france.svg",
                name: "French",
            },*/
        };

        const storeConfig = useConfigStore();
        const signOut = () => {
            store.logout();
            router.push({ name: "sign-in" });
        };

        const setLang = (lang: string) => {
            /*localStorage.setItem("lang", lang);
            localStorage.setItem("locale", lang);
            i18n.locale.value = lang;*/

            const userId = store.user.id;
            ApiService.post(`user/language/${userId}`, { locale: lang })
                .then((response) => {
                    localStorage.setItem("lang", lang);
                    localStorage.setItem("locale", lang);
                    window.location.reload();
                })
                .catch((error) => {
                    $catchResponse(error);
                });
        };

        const currentLanguage = computed(() => {
            return i18n.global.locale;
        });

        const currentLanguageLocale = computed(() => {
            return languages[i18n.global.locale as keyof typeof languages];
        });

        const user = computed(() => {
            return store.user;
        });

        const handleClearCache = async () => {
            await clearColumnsCache();
            await clearQueryCache();
            await clearLayoutCache();
            window.location.reload();
        };

        const clearLayoutCache = () => {
            return new Promise((resolve) => {
                const lsKey = storeConfig.localStorageConfigKey;
                localStorage.removeItem(lsKey);
                resolve(true);
            });
        };

        const clearColumnsCache = () => {
            return new Promise((resolve) => {
                const matchingKeys = getLocalStorageKeys("mdt-columns-");
                matchingKeys.forEach((key) => localStorage.removeItem(key));
                resolve(true);
            });
        };

        const clearQueryCache = () => {
            return new Promise((resolve) => {
                const matchingKeys = getLocalStorageKeys("gf-query-");
                matchingKeys.forEach((key) => localStorage.removeItem(key));
                resolve(true);
            });
        };

        const getLocalStorageKeys = (pattern) => {
            return Object.keys(localStorage).filter((key) =>
                key.startsWith(pattern)
            );
        };

        return {
            signOut,
            setLang,
            currentLanguage,
            currentLanguageLocale,
            languages,
            user,
            handleClearCache,
        };
    },
});
</script>
