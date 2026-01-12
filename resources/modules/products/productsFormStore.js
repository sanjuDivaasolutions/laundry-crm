/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 13/01/25, 6:53 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { defineStore } from "pinia";
import defaultFormState, { defaultEntry } from "./productsFormData";
import { getEditData, getShowData } from "@common@/components/moduleHelper";
import { useOptionStore } from "@common@/components/optionStore";

const optionStore = useOptionStore();

export const useModuleFormStore = defineStore({
    id: "products-form-store",
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
        onUnitChange(payload) {
            this.entry.unit_02 = payload.value;
            this.onUpdatePrices();
            this.updateWareHouseStock();
        },
        getFilteredSelectedUnits() {
            var selectedUnits = [];
            if (this.entry.unit_01) {
                selectedUnits.push(this.entry.unit_01);
            }
            if (this.entry.unit_02) {
                if (selectedUnits.indexOf(this.entry.unit_02) === -1) {
                    selectedUnits.push(this.entry.unit_02);
                }
            }

            return selectedUnits;
        },
        onUpdatePrices() {
            var self = this;
            var selectedUnits = this.getFilteredSelectedUnits();
            if (selectedUnits.length) {
                this.clearPrices(selectedUnits);
                selectedUnits.forEach((unit) => {
                    const price = {
                        id: null,
                        product_id: null,
                        unit: unit,
                        purchase_price: null,
                        sale_price: null,
                        lowest_sale_price: null,
                    };
                    if (self.entry.prices.length) {
                        var index = self.entry.prices.findIndex(function (row) {
                            return row.unit === unit;
                        });
                        if (index === -1) {
                            self.entry.prices.push(price);
                        }
                    } else {
                        self.entry.prices.push(price);
                    }
                });
            }
        },
        clearPrices(newUnits) {
            var self = this;
            if (self.entry.prices.length) {
                const prices = _.cloneDeep(this.entry.prices);
                prices.forEach((price, index) => {
                    if (newUnits.indexOf(price.unit) === -1) {
                        self.entry.prices.splice(index, 1);
                    }
                });
            }
        },
        updateWareHouseStock() {
            var self = this;
            var warehouses = optionStore.getOption("warehouses");
            if (warehouses.length) {
                warehouses.forEach(function (wh) {
                    var warehouseStock = {
                        id: null,
                        product_id: null,
                        warehouse: wh,
                        opening_stock: null,
                        opening_stock_value: null,
                    };
                    if (self.entry.warehouse_stocks.length) {
                        var index = self.entry.warehouse_stocks.findIndex(
                            function (row) {
                                return row.warehouse === wh;
                            }
                        );
                        if (index === -1) {
                            self.entry.warehouse_stocks.push(warehouseStock);
                        }
                    } else {
                        self.entry.warehouse_stocks.push(warehouseStock);
                    }
                });
            }
        },
        updateOpeningStockValues() {
            //loop through warehouses and get shelf stock and sum it to get opening stock
            this.entry.opening.forEach((warehouse) => {
                warehouse.opening_stock = 0;
                const shelves = warehouse.shelves;
                shelves.forEach((shelf) => {
                    warehouse.opening_stock += Number(shelf.quantity);
                });
            });
        },
    },
});
