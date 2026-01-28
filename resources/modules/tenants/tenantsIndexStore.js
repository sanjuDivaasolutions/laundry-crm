import { defineStore } from "pinia";
import defaultIndexState from "./tenantsIndexData";

export const useModuleIndexStore = defineStore({
    id: "tenants-index-store",
    state: () => {
        return defaultIndexState;
    },
    actions: {
        // Tenants are not deleted, only suspended
    },
});
