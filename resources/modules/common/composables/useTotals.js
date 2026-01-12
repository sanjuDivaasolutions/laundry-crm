export function useTotals(params) {
    const taxStore = params.taxStore;
    const entry = params.entry;

    const calculateTotal = () => {
        let subTotal = 0;
        entry.value.items.forEach((item) => {
            if (!item.quantity || !item.rate) return;
            const total = item.quantity * item.rate;
            item.amount = total;
            subTotal += total;
        });
        const stateId = _.get(entry.value, "state.id");
        let taxes = [];
        let taxTotal = 0;
        if (stateId) {
            const entryTaxes = _.get(entry.value, "taxes", []);
            taxes = taxStore.getTax(stateId, subTotal, entryTaxes);
            entry.value.taxes = taxes;
            taxTotal = taxes.reduce((acc, tax) => {
                return acc + tax.amount;
            }, 0);
        }
        const commissionRate = Number(entry.value.commission || 0);
        let commissionTotal = 0;

        if (!Number.isNaN(commissionRate) && commissionRate > 0) {
            const baseAmount = subTotal / (1 + commissionRate / 100);
            commissionTotal = subTotal - baseAmount;
        }

        const grandTotal = Number(subTotal) + Number(taxTotal);

        entry.value.sub_total = Number(subTotal).toFixed(2);
        entry.value.tax_total = Number(taxTotal).toFixed(2);
        entry.value.commission_total = Number(commissionTotal).toFixed(2);
        entry.value.grand_total = Number(grandTotal).toFixed(2);
    };

    return {
        calculateTotal,
    };
}
