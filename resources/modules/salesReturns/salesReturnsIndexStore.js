import { defineStore } from "pinia";
import defaultIndexState from "./salesReturnsIndexData";
import { performRemoveItem } from "@common@/components/moduleHelper";

export const useModuleIndexStore = defineStore({
    id: "sales-returns-index-store",
    state: () => {
        return defaultIndexState;
    },
    actions: {
        async removeItem(id) {
            await performRemoveItem(this.route, id);
        },
    },
});
