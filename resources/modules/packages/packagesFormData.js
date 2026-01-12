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
 *  *  Last modified: 23/01/25, 4:56 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./packagesModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
        items: [],
    };
};

const formFields = [
    {
        name: "date",
        label: `general.fields.date`,
        field: "date",
        placeholder: `Enter Date`,
        type: "date",
        required: true,
        column: "3",
    },
    {
        name: "sales_invoice",
        label: `general.fields.sales_invoice`,
        field: "sales_invoice",
        placeholder: "Select Sales Invoice",
        idValue: "id",
        labelValue: "invoice_number",
        type: "select-single",
        endpoint: "package-sales-invoices",
        updateEvent: "onSalesInvoiceChange",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "3",
    },
    {
        name: "remarks",
        label: `general.fields.remark`,
        field: "remarks",
        placeholder: `Enter Remark`,
        type: "text",
        required: false,
        column: "12",
    },
    {
        name: "items",
        label: `Items`,
        field: "items",
        entry: {
            id: null,
            name: null,
            sku: null,
            boxes: null,
            quantity: null,
            unit: null,
            product: null,
            sales_invoice_item: null,
        },
        subFields: [
            {
                name: "product",
                label: `general.fields.product`,
                field: "product",
                placeholder: "Select Product",
                idValue: "id",
                labelValue: "name",
                type: "select-single",
                mode: "single",
                hideSelected: true,
                required: true,
                disabled: true,
                column: "3",
            },
            {
                name: "quantity",
                label: `general.fields.quantity`,
                field: "quantity",
                placeholder: `Enter Quantity`,
                type: "number",
                step: "0.01",
                required: true,
                disabled: true,
                column: "3",
            },
        ],
        type: "items",
        required: true,
        column: "12",
        allowNewItem: false,
    },
];

const showFields = [
    {
        field: "id",
        label: "ID",
    },
    {
        field: "name",
        label: "Name",
    },
];

const modals = {
    form: {
        id: `${module.slug}-form-modal`,
        createTitle: `Create ${module.singular}`,
        editTitle: `Edit ${module.singular}`,
        size: "modal-lg",
        route: module.id,
    },
    show: {
        id: `${module.slug}-show-modal`,
        showTitle: `${module.singular} Details`,
        size: "modal-lg",
        route: module.id,
    },
};

const defaultFormDataState = {
    route: module.id,
    module: module,
    entry: defaultEntry(),
    formFields: formFields,
    showFields: showFields,
    modals: modals,
};

export default defaultFormDataState;

export { defaultEntry };
