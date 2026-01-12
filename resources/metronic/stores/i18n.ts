import { ref } from "vue";
import { defineStore } from "pinia";
// @ts-ignore
import ApiService from "@/core/services/ApiService";
// @ts-ignore
import { $setHeadMeta } from "@/core/helpers/utility.js";

export const useI18nStore = defineStore("i18n", () => {
    const languages = ref([]);
    const messages = ref({});
    const isLoading = ref(false);

    const locale = localStorage.getItem("locale") || localStorage.getItem("lang") || "en";

    $setHeadMeta("app-locale", locale);

    const currentLocale = ref(locale);

    const fetchLanguages = () => {
        return ApiService.get("locales/languages").then((res) => {
            languages.value = res.data.data.languages;
            currentLocale.value = localStorage.locale ?? res.data.data.locale;
        });
    };

    const fetchLocaleMessages = () => {
        return ApiService.query("locales/messages", {
            params: { locale: currentLocale.value },
        }).then((res) => {
            messages.value = res.data.data.messages;
        });
    };

    const setLanguage = (locale: string) => {
        isLoading.value = true;

        // Update localStorage immediately
        localStorage.setItem("locale", locale);
        localStorage.setItem("lang", locale); // Backwards compatibility

        // Update current locale
        currentLocale.value = locale;

        // Save preference to server and reload
        return ApiService.post("user/preference", {
            key: "language",
            value: locale,
        })
            .then(() => {
                // Reload page to apply new language
                window.location.reload();
            })
            .catch((error) => {
                isLoading.value = false;
                console.error("Failed to update language preference:", error);
                // Rollback on error
                const previousLocale = languages.value.find(
                    (lang: any) => lang.locale !== locale
                )?.locale || "en";
                localStorage.setItem("locale", previousLocale);
                localStorage.setItem("lang", previousLocale);
                currentLocale.value = previousLocale;
                throw error;
            });
    };

    return {
        languages,
        messages,
        currentLocale,
        isLoading,
        fetchLanguages,
        fetchLocaleMessages,
        setLanguage,
    };
});
