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
 *  *  Last modified: 22/01/25, 6:07 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./companiesModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
        image: [],
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
        column: "12",
    },
    {
        name: "address_1",
        label: `general.fields.address_1`,
        field: "address_1",
        placeholder: `Enter Address 1`,
        type: "text",
        required: true,
        column: "6",
    },
    {
        name: "address_2",
        label: `general.fields.address_2`,
        field: "address_2",
        placeholder: `Enter Address 2`,
        type: "text",
        required: true,
        column: "6",
    },
    /*{
        name: "postal_code",
        label: `general.fields.postal_code`,
        field: "postal_code",
        placeholder: `Enter Postal Code`,
        type: "text",
        required: true,
        column: "6",
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
        required: true,
        column: "6",
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
        required: true,
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
        required: true,
        column: "6",
    },*/
    {
        name: "warehouse",
        label: `Default Warehouse`,
        field: "warehouse",
        placeholder: "Select Warehouse",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "warehouses",
        updateEvent: "onSelectWarehouse",
        mode: "single",
        hideSelected: true,
        required: false,
        column: "12",
    },
    {
        name: "image",
        label: `general.fields.image`,
        field: "image",
        type: "file-drop",
        config: { collection: "company_image", total_files_allowed: 1 },
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
