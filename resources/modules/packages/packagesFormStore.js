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
 *  *  Last modified: 23/01/25, 4:42 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { defineStore } from "pinia";
import defaultFormState, { defaultEntry } from "./packagesFormData";
import { getEditData, getShowData } from "@common@/components/moduleHelper";
import ApiService from "@/core/services/ApiService";
import { $catchResponse } from "@/core/helpers/utility";

export const useModuleFormStore = defineStore({
    id: "packages-form-store",
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
        onSalesInvoiceChange(payload) {
            const salesInvoiceId = payload.value.id;
            ApiService.get("sales-invoice-items" + "/" + salesInvoiceId)
                .then((response) => {
                    this.entry.items = response.data.items;
                })
                .catch((error) => {
                    $catchResponse(error);
                })
                .finally(() => {});
        },
    },
});
