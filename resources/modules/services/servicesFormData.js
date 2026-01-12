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
 *  *  Last modified: 07/01/25, 4:41 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./servicesModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
        type: "product",
        sku: null,
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
        mode: "single",
        hideSelected: true,
        required: true,
        column: "12",
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
        name: "description",
        label: `general.fields.description`,
        field: "description",
        placeholder: `Enter Description`,
        type: "textarea",
        required: false,
        column: "12",
    },
    {
        name: "sale-price",
        label: `general.fields.sale_price`,
        field: "sale_price",
        placeholder: `Enter Sale Price`,
        type: "number",
        step: "0.01",
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
    {
        field: "sku",
        label: "Sku",
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
    {
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
    },
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
                field: "purchase_price",
                label: "Purchase Price",
                align: "right",
            },
            {
                field: "lowest_sale_price",
                label: "Lowest Sale Price",
                align: "right",
            },
            {
                field: "sale_price",
                label: "Sale Price",
                align: "right",
            },
        ],
    },
    {
        field: "warehouse_stocks",
        label: "Stocks",
        type: "items",
        subFields: [
            {
                field: "warehouse.name",
                label: "Warehouse",
            },
            {
                field: "quantity",
                label: "Quantity",
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
