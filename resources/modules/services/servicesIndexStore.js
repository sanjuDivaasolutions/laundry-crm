import { defineStore } from "pinia";
import defaultIndexState from "./servicesIndexData";
import { performRemoveItem } from "@common@/components/moduleHelper";

export const useModuleIndexStore = defineStore({
    id: "services-index-store",
    state: () => {
        return defaultIndexState;
    },
    actions: {
        async removeItem(id) {
            await performRemoveItem(this.route, id);
        },
    },
});
