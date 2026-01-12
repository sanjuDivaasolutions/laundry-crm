import { defineStore } from "pinia";
import defaultFormState, { defaultEntry } from "./purchaseInvoicesFormData";
import { getEditData, getShowData } from "@common@/components/moduleHelper";
import ApiService from "@/core/services/ApiService";
import { $catchResponse } from "@/core/helpers/utility";
import emitter from "@/core/plugins/mitt";

export const useModuleFormStore = defineStore({
    id: "purchase-invoices-form-store",
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
                this.onCurrencyUpdate(this.entry.currency, false);
            });
        },
        async loadShowData(id) {
            await getShowData(this.route, id).then((res) => {
                this.entry = res.data.data;
            });
        },
        onCurrencyUpdate(currency, updateRate = true) {
            const entry = _.cloneDeep(this.entry);
            entry.currency = currency;
            entry.currency.sourceCode = "USD";
            entry.currency.sourceValue = 1;
            entry.currency.targetCode = currency.code;
            entry.currency.targetValue = entry.currency_rate;
            if (updateRate) {
                entry.currency.targetValue = currency.rate;
                entry.currency_rate = currency.rate;
            }
            this.entry = entry;
        },
        onCurrencyRateUpdate(rate) {
            const entry = _.cloneDeep(this.entry);
            entry.currency.targetValue = rate;
            entry.currency_rate = rate;
            this.entry = entry;
        },
        onPurchaseOrderChange(payload) {
            var purchaseOrderId = payload.value.id;
            ApiService.get("purchase-order-invoice" + "/" + purchaseOrderId)
                .then((response) => {
                    this.entry.items = response.data.items;
                    this.entry.supplier = response.data.supplier;
                    emitter.emit("calculate-total");
                })
                .catch((error) => {
                    $catchResponse(error);
                })
                .finally(() => {

                });
        },
    },
});
