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
 *  *  Last modified: 22/01/25, 5:12 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { defineStore } from "pinia";
import defaultFormState, { defaultEntry } from "./salesInvoicesFormData";
import { getEditData, getShowData } from "@common@/components/moduleHelper";
import ApiService from "@/core/services/ApiService";
import { $catchResponse } from "@/core/helpers/utility";
import emitter from "@/core/plugins/mitt";

export const useModuleFormStore = defineStore({
    id: "sales-invoices-form-store",
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
                this.refreshItemShelves();
            });
        },
        async loadShowData(id) {
            await getShowData(this.route, id).then((res) => {
                this.entry = res.data.data;
            });
        },
        onSalesOrderChange(payload) {
            var soId = payload.value.id;
            ApiService.get("sales-order-invoice" + "/" + soId)
                .then((response) => {
                    this.entry.items = response.data.items;
                    this.entry.buyer = response.data.buyer;
                    emitter.emit("calculate-total");
                    this.refreshItemShelves();
                })
                .catch((error) => {
                    $catchResponse(error);
                })
                .finally(() => {});
        },
        onProductChange(payload) {
            const productId = _.get(payload.value, "id", null);

            //if productId is null then reset all the values
            if (!productId) {
                this.entry.items[payload.index].rate = null;
                this.entry.items[payload.index].unit = null;
                this.entry.items[payload.index].quantity = null;
                this.entry.items[payload.index].shelf = null;
                return;
            }

            const index = payload.index;
            const warehouseId = _.get(this.entry, "warehouse.id", null);

            if (!warehouseId) {
                alert("Warehouse needs to be selected first");
                this.entry.items[index].product = null;
                return;
            }

            //set Rate
            const firstPrice = _.get(payload.value, "prices[0]", null);
            if (firstPrice) {
                this.entry.items[index].rate = firstPrice.sale_price;
            }

            //set Unit
            const unit1 = _.get(payload.value, "unit_01", null);
            if (unit1) {
                this.entry.items[index].unit = unit1;
            }

            const params = {
                endpoint: "product-warehouse-shelves",
                params: {
                    pid: productId,
                    wid: warehouseId,
                },
            };
            emitter.emit(`refresh-options-${index}-shelf`, params);
        },
        refreshItemShelves() {
            const warehouseId = _.get(this.entry, "warehouse.id", null);
            if (!warehouseId || !Array.isArray(this.entry.items)) {
                return;
            }

            this.entry.items.forEach((item, index) => {
                const productId = _.get(item, "product.id", null);
                if (!productId) {
                    return;
                }
                emitter.emit(`refresh-options-${index}-shelf`, {
                    endpoint: "product-warehouse-shelves",
                    params: {
                        pid: productId,
                        wid: warehouseId,
                    },
                });
            });
        },
        onCommissionChange(payload) {
            const rate = Number(payload?.value || 0);
            this.entry.commission = Number.isNaN(rate) ? 0 : rate;
            emitter.emit("calculate-total");
        },
    },
});
