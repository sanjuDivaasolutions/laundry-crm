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
 *  *  Last modified: 15/01/25, 2:14 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./agentsModule";

const defaultEntry = () => {
    return {
        id: null,
        code: null,
        name: null,
        display_name: null,
        email: null,
        password: null,
        is_agent: true,
        billing_address: {
            id: null,
            name: null,
            address_1: null,
            address_2: null,
            city: null,
            state: null,
            country: null,
            postal_code: null,
            phone: null,
        },
        shipping_address: {
            id: null,
            name: null,
            address_1: null,
            address_2: null,
            city: null,
            state: null,
            country: null,
            postal_code: null,
            phone: null,
        },
        shipping_same_as_billing: false,
    };
};

const formFields = [
    {
        name: "name",
        label: `general.fields.name`,
        field: "name",
        placeholder: `Enter Name`,
        type: "text",
        required: true,
        column: "4",
    },
    {
        name: "display_name",
        label: `general.fields.display_name`,
        field: "display_name",
        placeholder: `Enter Display Name`,
        type: "text",
        required: true,
        column: "4",
    },
    {
        name: "email",
        label: "Email",
        field: "email",
        placeholder: "Enter Email",
        type: "email",
        required: false,
        column: "4",
    },
    {
        name: "phone",
        label: "Phone",
        field: "phone",
        placeholder: "Enter Phone",
        type: "text",
        required: false,
        column: "4",
    },
    {
        name: "currency",
        label: "Currency",
        field: "currency",
        placeholder: "Select Currency",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "currencies",
        mode: "single",
        hideSelected: true,
        required: false,
        column: "4",
    },
    {
        name: "payment-term",
        label: "Payment Term",
        field: "payment_term",
        placeholder: "Select Payment Term",
        idValue: "id",
        labelValue: "name",
        type: "select-ajax",
        endpoint: "paymentTerms",
        mode: "single",
        hideSelected: true,
        required: false,
        column: "4",
    },
    {
        name: "billing_address",
        label: `general.fields.billing_address`,
        field: "billing_address",
        type: "group",
        required: false,
        column: "12",
        subFields: [
            {
                name: "name",
                label: `general.fields.name`,
                field: "name",
                placeholder: `Enter Name`,
                type: "text",
                required: false,
                column: "4",
            },
            {
                name: "address_1",
                label: `general.fields.address_1`,
                field: "address_1",
                placeholder: `Enter Address Line 1`,
                type: "text",
                required: false,
                column: "4",
            },
            {
                name: "address_2",
                label: `general.fields.address_2`,
                field: "address_2",
                placeholder: `Enter Address Line 2`,
                type: "text",
                required: false,
                column: "4",
            },
            {
                name: "postal_code",
                label: `general.fields.postal_code`,
                field: "postal_code",
                placeholder: `Enter Postal Code`,
                type: "text",
                required: false,
                column: "4",
            },
            {
                name: "country",
                label: "Country",
                field: "country",
                placeholder: "Select Country",
                idValue: "id",
                labelValue: "name",
                type: "select-ajax",
                endpoint: "countries",
                mode: "single",
                hideSelected: true,
                required: false,
                column: "4",
            },
            {
                name: "state",
                label: "State",
                field: "state",
                placeholder: "Select State",
                idValue: "id",
                labelValue: "name",
                type: "select-ajax",
                endpoint: "states",
                mode: "single",
                hideSelected: true,
                required: false,
                column: "4",
            },
            {
                name: "city",
                label: "City",
                field: "city",
                placeholder: "Select City",
                idValue: "id",
                labelValue: "name",
                type: "select-ajax",
                endpoint: "cities",
                mode: "single",
                hideSelected: true,
                required: false,
                column: "4",
            },

            {
                name: "phone",
                label: "Phone",
                field: "phone",
                placeholder: "Enter Phone",
                type: "text",
                required: false,
                column: "4",
            },
        ],
    },
    {
        name: "shipping_address",
        label: `general.fields.shipping_address`,
        field: "shipping_address",
        type: "group",
        required: false,
        column: "12",
        hideWhen: { field: "shipping_same_as_billing", value: true },
        hideMessage: "Shipping address will reuse the billing address.",
        headerFields: [
            {
                name: "shipping_same_as_billing",
                field: "shipping_same_as_billing",
                label: "Same as billing address",
                type: "checkbox-inline",
                target: "main",
            },
        ],
        subFields: [
            {
                name: "name",
                label: `general.fields.name`,
                field: "name",
                placeholder: `Enter Name`,
                type: "text",
                required: false,
                column: "4",
                disabledIf: {
                    field: "shipping_same_as_billing",
                    value: true,
                },
            },
            {
                name: "address_1",
                label: `general.fields.address_1`,
                field: "address_1",
                placeholder: `Enter Address Line 1`,
                type: "text",
                required: false,
                column: "4",
                disabledIf: {
                    field: "shipping_same_as_billing",
                    value: true,
                },
            },
            {
                name: "address_2",
                label: `general.fields.address_2`,
                field: "address_2",
                placeholder: `Enter Address Line 2`,
                type: "text",
                required: false,
                column: "4",
                disabledIf: {
                    field: "shipping_same_as_billing",
                    value: true,
                },
            },
            {
                name: "postal_code",
                label: `general.fields.postal_code`,
                field: "postal_code",
                placeholder: `Enter Postal Code`,
                type: "text",
                required: false,
                column: "4",
                disabledIf: {
                    field: "shipping_same_as_billing",
                    value: true,
                },
            },
            {
                name: "country",
                label: "Country",
                field: "country",
                placeholder: "Select Country",
                idValue: "id",
                labelValue: "name",
                type: "select-ajax",
                endpoint: "countries",
                mode: "single",
                hideSelected: true,
                required: false,
                column: "4",
                disabledIf: {
                    field: "shipping_same_as_billing",
                    value: true,
                },
            },
            {
                name: "state",
                label: "State",
                field: "state",
                placeholder: "Select State",
                idValue: "id",
                labelValue: "name",
                type: "select-ajax",
                endpoint: "states",
                mode: "single",
                hideSelected: true,
                required: false,
                column: "4",
                disabledIf: {
                    field: "shipping_same_as_billing",
                    value: true,
                },
            },
            {
                name: "city",
                label: "City",
                field: "city",
                placeholder: "Select City",
                idValue: "id",
                labelValue: "name",
                type: "select-ajax",
                endpoint: "cities",
                mode: "single",
                hideSelected: true,
                required: false,
                column: "4",
                disabledIf: {
                    field: "shipping_same_as_billing",
                    value: true,
                },
            },
            {
                name: "phone",
                label: "Phone",
                field: "phone",
                placeholder: "Enter Phone",
                type: "text",
                required: false,
                column: "4",
                disabledIf: {
                    field: "shipping_same_as_billing",
                    value: true,
                },
            },
        ],
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
        field: "remarks",
        label: "general.fields.remarks",
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
    route: module.endpoint,
    module: module,
    entry: defaultEntry(),
    formFields: formFields,
    showFields: showFields,
    modals: modals,
};

export default defaultFormDataState;

export { defaultEntry };
