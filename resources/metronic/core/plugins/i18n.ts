// Import necessary dependencies
import { createI18n } from "vue-i18n";

// Create i18n instance
const i18n = createI18n({
    locale: "en", // Set default locale
    legacy: false,
    messages: {
        // Define your fallback translations here
        en: {},
        fr: {},
    },
    globalInjection: true,
});

export default i18n; // Export i18n instance
