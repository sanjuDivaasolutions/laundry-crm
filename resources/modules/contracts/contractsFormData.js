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
 *  *  Last modified: 05/02/25, 7:27 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./contractsModule";

const defaultEntry = () => {
    return {
        id: null,
        contract_type: null,
        name: null,
        items: [],
        term: [],
        client: null,
        date: null,
        revision: {
            limited_installment: false,
            start_date: null,
            end_date: null,
            tax_rate: 5,
            sub_total: 0,
            tax_total: 0,
            charges_total: 0,
            discount_total: 0,
            grand_total: 0,
        },
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
        column: "4",
    },
    {
        name: "date",
        label: `general.fields.date`,
        field: "date",
        placeholder: `Enter Date`,
        type: "date",
        required: true,
        column: "4",
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
        column: "4",
    },
    {
        name: "term",
        label: `general.fields.contract_term`,
        field: "term",
        placeholder: `Select Contract Term`,
        type: "select-single",
        endpoint: "contract-terms",
        mode: "tags",
        required: true,
        column: "12",
    },
    {
        name: "other_terms",
        label: `general.fields.other_terms`,
        field: "other_terms",
        placeholder: `Enter Other Terms`,
        type: "textarea",
        required: false,
        column: "12",
    },
    {
        name: "remark",
        label: `general.fields.contract_remark`,
        field: "remark",
        placeholder: `Enter Remark`,
        type: "textarea",
        required: false,
        column: "12",
    },
    {
        name: "contract-term-details",
        label: `Contract Term Details`, //`general.fields.user_detail`,
        field: "revision",
        type: "group",
        required: true,
        column: "12",
        subFields: [
            {
                name: "limited_installment",
                label: `general.fields.limited_installment`,
                field: "limited_installment",
                type: "switch",
                required: false,
                column: "12",
            },
            {
                name: "contract_type",
                label: `general.fields.contract_type`,
                field: "contract_type",
                placeholder: `Select Contract Term`,
                type: "select-single",
                idValue: "value",
                labelValue: "label",
                endpoint: "contract-types",
                required: true,
                column: "3",
            },
            {
                name: "start-date",
                label: `general.fields.start_date`,
                field: "start_date",
                placeholder: `Enter Start Date`,
                type: "date",
                required: true,
                column: "3",
            },
            {
                name: "installment_count",
                label: `general.fields.installment_count`,
                field: "installment_count",
                placeholder: `Enter Number of Installments`,
                type: "number",
                visibleIf: { field: "limited_installment", value: true },
                required: false,
                column: "3",
            },
        ],
    },
    {
        name: "items",
        label: `general.fields.items`,
        field: "items",
        totalFields: {
            subTotal: "revision.sub_total",
            taxes: null, //'taxes'
            discounts: null, //'discounts'
            taxRate: "revision.tax_rate",
            taxTotal: "revision.tax_total",
            discountTotal: "revision.discount_total",
            grandTotal: "revision.grand_total",
        },
        entry: {
            id: null,
            service_id: null,
            contract_id: null,
            amount: 0,
        },
        subFields: [
            {
                name: "product",
                label: `general.fields.service`,
                field: "product",
                placeholder: "Search service...",
                idValue: "id",
                labelValue: "name",
                type: "select-ajax",
                endpoint: "services",
                mode: "single",
                hideSelected: true,
                required: true,
                column: "3",
            },
            {
                name: "description",
                label: `general.fields.description`,
                field: "description",
                placeholder: `Enter Description`,
                type: "textarea",
                required: true,
                column: "3",
            },
            {
                name: "amount",
                label: `general.fields.amount`,
                columnClass: "text-end",
                field: "amount",
                placeholder: ``,
                type: "number",
                step: "0.01",
                required: true,
                readonly: false,
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
