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
 *  *  Last modified: 13/01/25, 7:55 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./productsModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
        type: "product",
        sku: null,
        barcode: null,
        barcode_type: "code128",
        description: null,
        manufacturer: null,
        features: [],
        unit_01: null,
        unit_02: null,
        category: null,
        supplier: null,
        prices: [],
        warehouse_stocks: [],
    };
};

const formFields = [
    {
        name: "company",
        label: "Company",
        field: "company",
        placeholder: "Select Company",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "companies",
        updateEvent: "onCompanyChange",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    {
        name: "category",
        label: "Category",
        field: "category",
        placeholder: "Select Category",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "categories",
        mode: "single",
        hideSelected: true,
        required: false,
        column: "6",
    },
    {
        name: "name",
        label: `general.fields.name`,
        field: "name",
        placeholder: `Enter Name`,
        type: "text",
        required: true,
        column: "6",
    },
    {
        name: "sku",
        label: `general.fields.sku`,
        field: "sku",
        placeholder: `Enter SKU`,
        type: "text",
        required: true,
        column: "6",
    },
    {
        name: "barcode",
        label: "Barcode",
        field: "barcode",
        placeholder: "Enter or Generate Barcode",
        type: "barcode",
        required: false,
        column: "12",
        barcodeTypeField: "barcode_type",
    },
    {
        name: "barcode_type",
        label: "Barcode Type",
        field: "barcode_type",
        placeholder: "Select Barcode Type",
        type: "select-single",
        options: [
            { value: "code128", label: "Code 128" },
            { value: "code39", label: "Code 39" },
            { value: "ean13", label: "EAN-13" }
        ],
        mode: "single",
        hideSelected: true,
        required: false,
        column: "7",
    },
    {
        name: "description",
        label: `general.fields.description`,
        field: "description",
        placeholder: `Enter Description`,
        type: "textarea",
        required: false,
        column: "12",
    },
    {
        name: "unit_01",
        label: "Unit 01",
        field: "unit_01",
        placeholder: "Select Unit 01",
        idValue: "id",
        labelValue: "name",
        updateEvent: "onUnitChange",
        type: "select-single",
        endpoint: "units",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    {
        name: "unit_02",
        label: `general.fields.unit_02`,
        field: "unit_02",
        placeholder: "Select Unit 02",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        updateEvent: "onUpdatePrices",
        endpoint: "units",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    /*{
        name: "supplier",
        label: "Preferred Supplier",
        field: "supplier",
        placeholder: "Select Supplier",
        idValue: "id",
        labelValue: "name",
        type: "select-ajax",
        endpoint: "suppliers",
        mode: "single",
        hideSelected: true,
        required: false,
        column: "6",
    },*/
    {
        name: "prices",
        label: `Prices`,
        field: "prices",
        entry: {
            id: null,
            product_id: null,
            unit: null,
            purchase_price: null,
            sale_price: null,
            lowest_sale_price: null,
        },
        subFields: [
            {
                name: "unit",
                label: "Unit",
                field: "unit",
                placeholder: "Select Unit",
                idValue: "id",
                labelValue: "name",
                type: "select-single",
                endpoint: "units",
                mode: "single",
                hideSelected: true,
                disabled: true,
            },
            {
                name: "purchase-price",
                label: `general.fields.purchase_price`,
                field: "purchase_price",
                placeholder: `Enter Purchase Price`,
                type: "number",
                step: "0.01",
                required: true,
            },
            {
                name: "sale-price",
                label: `general.fields.sale_price`,
                field: "sale_price",
                placeholder: `Enter Sale Price`,
                type: "number",
                step: "0.01",
                required: true,
            },
            {
                name: "lowest-sale-price",
                label: `general.fields.lowest_sale_price`,
                field: "lowest_sale_price",
                placeholder: `Enter lowest sale Price`,
                type: "number",
                step: "0.01",
                required: true,
            },
        ],
        type: "items",
        required: true,
        column: "12",
        allowNewItem: false,
    },
    {
        name: "opening-stock",
        label: `Opening Stock`,
        field: "opening",
        entry: {
            id: null,
            product_id: null,
            warehouse: null,
            date: null,
            order_no: null,
            reason: null,
            quantity: 0,
            amount: 0,
            batch: null,
            shelf: null,
            rate: 0,
        },
        subFields: [
            {
                name: "warehouse",
                label: "Warehouse",
                field: "warehouse",
                placeholder: "Select Warehouse",
                idValue: "id",
                labelValue: "name",
                type: "select-single",
                endpoint: "warehouses",
                mode: "single",
                hideSelected: true,
                disabled: true,
            },
            {
                name: "opening_stock",
                label: `Opening Stock`,
                field: "opening_stock",
                placeholder: `Enter Opening Stock`,
                type: "number",
                step: "0.01",
                required: true,
                disabled: true,
            },
            {
                name: "opening_stock_value",
                label: `Opening Stock Value`,
                field: "opening_stock_value",
                placeholder: `Enter Opening Stock Value`,
                type: "number",
                step: "0.01",
                required: true,
            },
            {
                name: "sub-items",
                label: `Shelves`,
                field: "shelves",
                parentField: "product_opening_id",
                entry: {
                    id: null,
                    product_opening_id: null,
                    shelf: null,
                    quantity: null,
                },
                listFields: [
                    { field: "shelf.name", label: "Shelf" },
                    { field: "quantity", label: "Quantity" },
                ],
                subFields: [
                    {
                        name: "shelf",
                        label: "Shelf",
                        field: "shelf",
                        placeholder: "Select Shelf",
                        idValue: "id",
                        labelValue: "name",
                        type: "select-single",
                        endpoint: "shelves",
                        endpointFilterParams: [
                            { field: "warehouse.id", parent: "wid" },
                        ],
                        dependentField: "warehouse",
                        dependentFieldLevel: "same",
                        mode: "single",
                        hideSelected: true,
                        disabled: false,
                    },
                    {
                        name: "quantity",
                        label: `general.fields.quantity`,
                        field: "quantity",
                        placeholder: `Enter Quantity`,
                        type: "number",
                        step: "0.01",
                        required: false,
                        column: "12",
                    },
                ],
                updateEvent: "onOpeningStockChange",
                type: "sub-items",
                required: true,
                column: "12",
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
    {
        field: "sku",
        label: "Sku",
    },
    {
        field: "barcode",
        label: "Barcode",
    },
    {
        field: "barcode_type",
        label: "Barcode Type",
    },
    {
        field: "category.name",
        label: "Category",
    },
    {
        field: "description",
        label: "Description",
    },
    {
        field: "manufacturer",
        label: "Manufacturer",
    },
    {
        field: "supplier.name",
        label: "Supplier",
    },
    /*{
        field: "features",
        label: "Features",
        type: "items",
        subFields: [
            {
                field: "feature.name",
                label: "Feature",
            },
            {
                field: "name",
                label: "Name",
                align: "right",
            },
        ],
    },*/
    {
        field: "prices",
        label: "Prices",
        type: "items",
        subFields: [
            {
                field: "unit.name",
                label: "Unit",
            },
            {
                field: "purchase_price_text",
                label: "Purchase Price",
                align: "right",
            },
            {
                field: "lowest_sale_price_text",
                label: "Lowest Sale Price",
                align: "right",
            },
            {
                field: "sale_price_text",
                label: "Sale Price",
                align: "right",
            },
        ],
    },
    {
        field: "stock",
        label: "Stocks",
        type: "items",
        subFields: [
            {
                field: "warehouse.name",
                label: "Warehouse",
                subFields: [
                    {
                        field: "shelves",
                        label: null,
                        type: "items",
                        subFields: [
                            {
                                field: "shelf.name",
                                label: "Shelf",
                            },
                            {
                                field: "in_transit",
                                label: "In-Transit",
                                align: "right",
                            },
                            {
                                field: "on_hand",
                                label: "On-Hand",
                                align: "right",
                            },
                        ],
                    },
                ],
            },
            {
                field: "in_transit",
                label: "In-Transit",
                align: "right",
            },
            {
                field: "on_hand",
                label: "On-Hand",
                align: "right",
            },
        ],
        summaryFields: [
            { field: null, label: null },
            {
                field: "in_transit_total",
                label: "In-Transit",
                align: "right",
            },
            {
                field: "on_hand_total",
                label: "On-Hand",
                align: "right",
            },
        ],
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
