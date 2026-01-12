<template>
    <!--begin::Menu-->
    <div
        class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-muted menu-active-bg menu-state-primary fw-semibold py-4 fs-base w-200px"
        data-kt-menu="true"
        data-kt-element="language-menu"
    >
        <template v-for="language in activeLanguages" :key="language.id">
            <!--begin::Menu item-->
            <div class="menu-item px-3 my-0">
                <router-link
                    :to="path"
                    :class="{ active: isCurrentLanguage(language.locale) }"
                    class="menu-link px-3 py-2"
                    @click="switchLanguage(language.locale)"
                >
                    <span class="menu-icon" data-kt-element="icon">
                        <img
                            :src="`/images/flags/${language.locale}.svg`"
                            :alt="language.name"
                            class="w-20px h-15px rounded"
                        />
                    </span>
                    <span class="menu-title">{{ language.name }}</span>
                </router-link>
            </div>
            <!--end::Menu item-->
        </template>
    </div>
    <!--end::Menu-->
</template>

<script lang="ts">
import { defineComponent, computed, onMounted } from "vue";
import { useI18nStore } from "@/stores/i18n";
import { useRoute } from "vue-router";

export default defineComponent({
    name: "language-switcher",
    components: {},
    setup() {
        const i18nStore = useI18nStore();
        const route = useRoute();

        onMounted(() => {
            // Fetch available languages on component mount
            i18nStore.fetchLanguages();
        });

        const activeLanguages = computed(() => {
            return i18nStore.languages.filter((lang: any) => lang.active);
        });

        const currentLocale = computed(() => {
            return i18nStore.currentLocale;
        });

        const path = computed(() => route.path);

        const isCurrentLanguage = (locale: string) => {
            return currentLocale.value === locale;
        };

        const switchLanguage = (locale: string) => {
            if (locale !== currentLocale.value) {
                i18nStore.setLanguage(locale);
            }
        };

        return {
            activeLanguages,
            currentLocale,
            path,
            isCurrentLanguage,
            switchLanguage,
        };
    },
});
</script>
