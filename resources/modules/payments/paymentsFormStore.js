import { defineStore } from "pinia";
import defaultFormState, { defaultEntry } from "./paymentsFormData";
import { getEditData, getShowData } from "@common@/components/moduleHelper";

export const useModuleFormStore = defineStore({
    id: "payments-form-store",
    state: () => {
        return defaultFormState;
    },
    actions: {
        resetEntry() {
            this.entry = defaultEntry();
        },
        async loadEditData(id) {
            await getEditData(this.route, id).then((res) => {
                this.entry = res.data.data;
            });
        },
        async loadShowData(id) {
            await getShowData(this.route, id).then((res) => {
                this.entry = res.data.data;
            });
        },
    },
});
