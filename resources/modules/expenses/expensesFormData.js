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
 *  *  Last modified: 29/01/25, 10:54 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./expensesModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
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
        column: "6",
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
        name: "invoice_number",
        label: `general.fields.invoice_number`,
        field: "invoice_number",
        placeholder: `Enter Invoice #`,
        type: "text",
        required: false,
        column: "3",
    },
    {
        name: "expense_type",
        label: `general.fields.expense_type`,
        field: "expense_type",
        placeholder: "Select Expense Type",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "expense-types",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    {
        name: "payment_mode",
        label: `general.fields.payment_mode`,
        field: "payment_mode",
        placeholder: "Select Payment Mode",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "payment-modes",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    {
        name: "sub_total",
        label: `general.fields.subTotal`,
        field: "sub_total",
        placeholder: `Enter Sub Total`,
        type: "number",
        step: "0.01",
        required: true,
        column: "4",
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
        column: "4",
    },
    {
        name: "is_taxable",
        label: `general.fields.is_taxable`,
        field: "is_taxable",
        type: "switch",
        updateEvent: "onTaxableChange",
        required: true,
        column: "4",
    },
    {
        name: "description",
        label: `general.fields.description`,
        field: "description",
        placeholder: `Enter Description`,
        type: "text",
        required: false,
        column: "12",
    },
    {
        name: "attachment",
        label: `general.fields.document`,
        field: "attachment",
        type: "file-drop",
        config: { collection: "expense_attachment", total_files_allowed: 1 },
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
        field: "date",
        label: `general.fields.date`,
    },
    {
        field: "description",
        label: `general.fields.description`,
    },
    {
        field: "company.name",
        label: `general.fields.company`,
    },
    {
        field: "invoice_number",
        label: `general.fields.invoice_number`,
    },
    {
        field: "expense_type.name",
        label: `general.fields.expense_type`,
    },
    {
        field: "payment_mode.name",
        label: `general.fields.payment_mode`,
    },
    {
        field: "sub_total",
        label: `general.fields.subTotal`,
    },
    {
        field: "tax_total",
        label: `Tax Total`,
    },
    {
        field: "state.name",
        label: `State`,
    },
    {
        field: "is_taxable_label",
        label: `general.fields.is_taxable`,
    },
    {
        field: "user.name",
        label: `general.fields.user`,
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
