import { defineStore } from "pinia";
import defaultIndexState from "./salesOrdersIndexData";
import { performRemoveItem } from "@common@/components/moduleHelper";

export const useModuleIndexStore = defineStore({
    id: "sales-orders-index-store",
    state: () => {
        return defaultIndexState;
    },
    actions: {
        async removeItem(id) {
            await performRemoveItem(this.route, id);
        },
    },
});
