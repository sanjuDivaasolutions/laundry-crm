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
 *  *  Last modified: 05/02/25, 5:58 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./inventoryAdjustmentsModule";

const defaultEntry = () => {
    return {
        id: null,
        date: "",
        reason: null,
        product: null,
        shelf: null,
        target_shelf: null,
        adjusted_quantity: null,
    };
};

const formFields = [
    /*
  'date',
  'reason',
        'remark',
        'product_id',
        'adjusted_quantity'
   */
    {
        name: "date",
        label: `general.fields.date`,
        field: "date",
        placeholder: `Select Date`,
        type: "date",
        required: true,
        column: "6",
    },
    {
        name: "reason",
        label: `general.fields.reason`,
        field: "reason",
        placeholder: "Select Reason",
        idValue: "value",
        labelValue: "label",
        type: "select-single",
        endpoint: "inventory-adjustment-reasons",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    {
        name: "product",
        label: `general.fields.product`,
        field: "product",
        placeholder: "Search product...",
        idValue: "id",
        labelValue: "name",
        type: "select-ajax",
        endpoint: "active-products",
        updateEvent: "onProductChange",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "12",
    },
    {
        name: "shelf",
        label: `general.fields.shelf`,
        field: "shelf",
        placeholder: "Search shelf...",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "active-product-shelves",
        dependentField: "product",
        endpointFilterParams: [{ field: "product.id", parent: "pid" }],
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    {
        name: "target_shelf",
        label: `general.fields.target_shelf`,
        field: "target_shelf",
        placeholder: "Search shelf...",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "active-shelves",
        /*dependentField: "product",
        endpointFilterParams: [
            { field: "product.id", parent: "pid" },
            { field: "shelf.id", parent: "sid" },
        ],*/
        visibleIf: { field: "reason", key: "value", value: "move" },
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    {
        name: "adjusted_quantity",
        label: `general.fields.adjusted_quantity_form`,
        field: "adjusted_quantity",
        placeholder: `Enter Adjusted Quantity`,
        type: "number",
        required: true,
        column: "12",
    },
    {
        name: "remark",
        label: `general.fields.remark`,
        field: "remark",
        placeholder: `Enter Remark`,
        type: "text",
        required: false,
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
