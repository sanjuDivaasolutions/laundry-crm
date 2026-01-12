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
 *  *  Last modified: 16/10/24, 5:31 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

import { module } from "./purchaseInvoicesModule";

const defaultEntry = () => {
    return {
        id: null,
        date: null,
        invoice_number: null,
        supplier: null,
        purchase_order: null,
        payment_term: null,
        sub_total: 0,
        tax_total: 0,
        discount_total: 0,
        grand_total: 0,
        items: [],
        remark: null,
    };
};

const itemModule = "purchaseInvoiceItem";

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
        name: "purchase_order",
        label: `general.fields.purchase_order`,
        field: "purchase_order",
        placeholder: "Select Purchase Order",
        idValue: "id",
        labelValue: "po_number",
        type: "select-ajax",
        endpoint: "purchase_orders",
        updateEvent: "onPurchaseOrderChange",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "3",
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

    /* {
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
        column: "3",
    }, */
    /* {
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
                endpoint: "purchase_products",
                updateEvent: "onUpdateProducts",
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
