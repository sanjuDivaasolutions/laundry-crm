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
 *  *  Last modified: 21/01/25, 5:36 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./quotationsModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
        items: [],
    };
};

const formFields = [
    {
        name: "company",
        label: `general.fields.company`,
        field: "company",
        placeholder: "Select Company",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "companies",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "3",
    },
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
        name: "expected_delivery_date",
        label: `general.fields.expected_delivery_date`,
        field: "expected_delivery_date",
        placeholder: `Enter Expected Delivery Date`,
        type: "date",
        required: false,
        column: "3",
    },
    {
        name: "buyer",
        label: `general.fields.buyer`,
        field: "buyer",
        placeholder: "Select Buyer",
        idValue: "id",
        labelValue: "display_name",
        type: "select-ajax",
        endpoint: "buyers",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "3",
    },
    {
        name: "reference_no",
        label: `general.fields.reference_no`,
        field: "reference_no",
        placeholder: `Enter Reference #`,
        type: "text",
        required: false,
        column: "3",
    },
    {
        name: "remark",
        label: `general.fields.remark`,
        field: "remark",
        placeholder: `Enter Remarks`,
        type: "text",
        required: false,
        column: "9",
    },
    {
        name: "items",
        label: `general.fields.items`,
        field: "items",
        totalFields: {
            subTotal: "sub_total",
            taxes: null, //'taxes'
            discounts: null, //'discounts'
            taxTotal: "tax_total",
            discountTotal: "discount_total",
            grandTotal: "grand_total",
        },
        entry: {
            id: null,
            product_id: null,
            unit: {},
            rate: null,
            quantity: 1,
            amount: 0,
        },
        subFields: [
            {
                name: "product",
                label: `general.fields.product`,
                field: "product",
                placeholder: "Search product...",
                idValue: "id",
                labelValue: "name",
                type: "select-ajax",
                endpoint: "sales-products",
                updateEvent: "onProductChange",
                mode: "single",
                hideSelected: true,
                required: true,
                column: "3",
            },

            {
                name: "unit",
                label: `general.fields.unit`,
                field: "unit",
                placeholder: "Select Unit",
                idValue: "id",
                labelValue: "name",
                type: "select-single",
                endpoint: "units",
                mode: "single",
                hideSelected: true,
                required: true,
                column: "3",
            },
            {
                name: "rate",
                label: `general.fields.rate`,
                field: "rate",
                source: {
                    control: "product",
                    field: "rate",
                },
                placeholder: `Enter Rate`,
                type: "number",
                step: "0.0001",
                required: true,
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
                column: "3",
            },
            {
                name: "amount",
                label: `general.fields.amount`,
                columnClass: "text-end",
                field: "amount",
                placeholder: ``,
                type: "amount",
                readonly: true,
                required: true,
                column: "3",
            },
        ],
        type: "invoice-items",
        required: true,
        column: "12",
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
