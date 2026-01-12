import { defineStore } from "pinia";
import defaultIndexState from "./rolesIndexData";
import { performRemoveItem } from "@common@/components/moduleHelper";

export const useModuleIndexStore = defineStore({
    id: "roles-index-store",
    state: () => {
        return defaultIndexState;
    },
    actions: {
        async removeItem(id) {
            await performRemoveItem(this.route, id);
        },
    },
});
