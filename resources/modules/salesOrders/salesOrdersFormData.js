/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 16/10/24, 5:03 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

import { module } from "./salesOrdersModule";

const defaultEntry = () => {
    return {
        id: null,
        company: null,
        department: null,
        date: null,
        estimated_shipment_date: null,
        so_number: null,
        buyer: null,
        payment_term: null,
        other_terms: null,
        sub_total: 0,
        tax_total: 0,
        tax_rate: 20,
        grand_total: 0,
        items: [],
        type: "p",
    };
};

const itemModule = "salesOrderItem";

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
        name: "estimated_shipment_date",
        label: `general.fields.estimated_shipment_date`,
        field: "estimated_shipment_date",
        placeholder: `Enter Date`,
        type: "date",
        required: true,
        column: "3",
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
        name: "warehouse",
        label: `Warehouse`,
        field: "warehouse",
        placeholder: "Select Warehouse",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "warehouses",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },

    {
        name: "payment_term",
        label: `general.fields.payment_term`,
        field: "payment_term",
        placeholder: "Select Payment Term",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "payment-terms",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    /*  {
        name: "shipment_mode",
        label: `general.fields.shipment_mode`,
        field: "shipment_mode",
        placeholder: "Select Shipment Mode",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "shipment-modes",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "3",
    }, */

    {
        name: "remark",
        label: `general.fields.remark`,
        field: "remarks",
        placeholder: `Enter Other Terms`,
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
            taxRate: "tax_rate",
            taxTotal: "tax_total",
            grandTotal: "grand_total",
        },
        entry: {
            id: null,
            product_id: null,
            sales_order_id: null,
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
