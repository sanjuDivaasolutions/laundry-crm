import _ from "lodash";

export function $updateCurrency(
    entry,
    currency,
    updateRate = true,
    newRate = null
) {
    const updatedEntry = _.cloneDeep(entry);
    updatedEntry.currency = {
        sourceCode: "USD",
        sourceValue: 1,
        targetCode: currency.code,
        targetValue: updateRate
            ? newRate || currency.rate
            : entry.currency_rate,
    };
    if (updateRate) {
        updatedEntry.currency_rate = newRate || currency.rate;
    }
    return updatedEntry;
}

export function $updateCurrencyRate(entry, rate) {
    const updatedEntry = _.cloneDeep(entry);
    updatedEntry.currency.targetValue = rate;
    updatedEntry.currency_rate = rate;
    return updatedEntry;
}
