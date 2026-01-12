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
 *  *  Last modified: 09/01/25, 6:25 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./inwardsModule";

const defaultEntry = () => {
    return {
        id: null,
        date: null,
        invoice_number: null,
        reference_no: null,
        company: null,
        supplier: null,
        warehouse: null,
        tax_rate: null,
        sub_total: 0,
        tax_total: 0,
        discount_total: 0,
        grand_total: 0,
        items: [],
        remark: null,
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
        column: "2",
    },
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
        name: "warehouse",
        label: `Warehouse`,
        field: "warehouse",
        placeholder: "Select Warehouse",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "warehouses",
        updateEvent: "onSelectWarehouse",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "2",
    },
    {
        name: "supplier",
        label: `general.fields.supplier`,
        field: "supplier",
        placeholder: "Select Supplier",
        idValue: "id",
        labelValue: "display_name",
        type: "select-ajax",
        endpoint: "suppliers",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "3",
    },
    {
        name: "state",
        label: `State`,
        field: "state",
        placeholder: "Select State",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "states",
        updateEvent: "onStateChange",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "2",
    },
    {
        name: "remark",
        label: `general.fields.remark`,
        field: "remark",
        placeholder: `Enter Remarks`,
        type: "text",
        required: false,
        column: "12",
    },
    {
        name: "items",
        label: `general.fields.items`,
        field: "items",
        totalFields: {
            subTotal: "sub_total",
            taxes: null, //'taxes'
            discounts: null, //'discounts'
            multipleTaxField: "taxes",
            discountTotal: "discount_total",
            grandTotal: "grand_total",
        },
        entry: {
            id: null,
            product_id: null,
            unit: {},
            shelf: {},
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
                endpoint: "purchase_products",
                updateEvent: "onUpdateProducts",
                mode: "single",
                hideSelected: true,
                required: true,
                column: "3",
            },
            {
                name: "shelf",
                label: `general.fields.shelf`,
                field: "shelf",
                placeholder: "Search shelf...",
                idValue: "id",
                labelValue: "name",
                type: "select-single",
                endpoint: "warehouse-shelves",
                endpointFilterParams: [
                    { field: "warehouse.id", parent: "wid" },
                ],
                dependentField: "warehouse",
                dependentFieldLevel: "parent",
                updateEvent: "onUpdateShelf",
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
        size: "modal-xl",
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
