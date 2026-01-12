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
 *  *  Last modified: 22/01/25, 6:00 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { defineStore } from "pinia";
import defaultFormState, { defaultEntry } from "./inwardsFormData";
import { getEditData, getShowData } from "@common@/components/moduleHelper";

export const useModuleFormStore = defineStore({
    id: "inwards-form-store",
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
        onUpdateProducts(payload) {
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

            //set Rate
            this.entry.items[index].rate = payload.value.rate;

            //set Unit
            this.entry.items[index].unit = payload.value.unit_01;
        },
    },
});
