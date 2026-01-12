import { defineStore } from "pinia";
import defaultIndexState from "./categoriesIndexData";
import { performRemoveItem } from "@common@/components/moduleHelper";

export const useModuleIndexStore = defineStore({
    id: "categories-index-store",
    state: () => {
        return defaultIndexState;
    },
    actions: {
        async removeItem(id) {
            await performRemoveItem(this.route, id);
        },
    },
});
