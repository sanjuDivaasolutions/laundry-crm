import { defineStore } from "pinia";
import { ref } from "vue";
import ApiService from "@/core/services/ApiService";

export const useTaxStore = defineStore("taxes", () => {
    const taxRates = ref([]);
    const isTaxable = ref(true);

    const setupTaxes = async () => {
        ApiService.get("options/tax-rates").then((response) => {
            taxRates.value = response.data.data;
        });
    };

    const getTax = (stateId, subTotal, entryTaxes) => {
        const currentTaxes = taxRates.value.filter(
            (tax) => tax.state_id === stateId
        );
        const outputTaxes = [];
        if (isTaxable.value) {
            currentTaxes.forEach((tax) => {
                const rate = Number(_.get(tax, "rate", 0));
                const priority = _.get(tax, "priority", 0);
                let taxObj = entryTaxes.find((entryTax) => {
                    return entryTax.tax_rate_id === tax.id;
                });
                if (!taxObj) {
                    taxObj = {
                        id: null,
                        tax_rate_id: tax.id,
                        priority: priority,
                        rate: rate,
                        amount: 0,
                    };
                }
                taxObj.name = tax.name + " (" + rate + "%)";
                taxObj.amount = (subTotal * rate) / 100;
                outputTaxes.push(taxObj);
                /*outputTaxes.push({

                    tax_rate_id: tax.id,
                    name: tax.name + " (" + rate + "%)",
                    rate: rate,
                    amount: (subTotal * rate) / 100,
                });*/
            });
        }
        return outputTaxes;
    };

    const setTaxable = (value) => {
        isTaxable.value = value;
    };

    return {
        taxRates,
        setupTaxes,
        getTax,
        setTaxable,
    };
});
