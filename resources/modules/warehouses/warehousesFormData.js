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
 *  *  Last modified: 17/12/24, 11:32 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

import { module } from "./warehousesModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
        code: null,
        address_1: null,
        address_2: null,
        city: null,
        state: null,
        country: null,
        postal_code: null,
        email: null,
    };
};

const formFields = [
    {
        name: "name",
        label: `general.fields.name`,
        field: "name",
        placeholder: `Warehouse Name`,
        type: "text",
        required: true,
        column: "12",
    },
    {
        name: "address_1",
        label: `general.fields.address_1`,
        field: "address_1",
        placeholder: `Address 1`,
        type: "text",
        required: false,
        column: "6",
    },
    {
        name: "address_2",
        label: `general.fields.address_2`,
        field: "address_2",
        placeholder: `Address 2`,
        type: "text",
        required: false,
        column: "6",
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
        name: "email",
        label: "Email",
        field: "email",
        placeholder: "Enter Email",
        type: "email",
        required: false,
        column: "6",
    },
    {
        name: "phone",
        label: "Phone",
        field: "phone",
        placeholder: "Enter Phone",
        type: "text",
        required: false,
        column: "6",
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
