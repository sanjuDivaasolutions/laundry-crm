import { module } from "./shipmentsModule";
const defaultEntry = () => {
    return {
        id: null,
        name: null,
    };
};

const formFields = [
    {
        name: "shipment_date",
        label: `general.fields.shipment_date`,
        field: "shipment_date",
        placeholder: `Enter Shipment Date`,
        type: "date",
        required: true,
        column: "6",
    },
    {
        name: "delivery_date",
        label: `general.fields.delivery_date`,
        field: "delivery_date",
        placeholder: `Enter Delivery Date`,
        type: "date",
        required: true,
        column: "6",
    },
    {
        name: "package",
        label: `general.fields.package`,
        field: "package",
        placeholder: "Select Package",
        idValue: "id",
        labelValue: "code",
        type: "select-single",
        endpoint: "packages",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    {
        name: "shipment-mode",
        label: `general.fields.shipment_mode`,
        field: "shipment_mode",
        placeholder: "Select Carrier",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "shipment_modes",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "6",
    },
    {
        name: "remark",
        label: `general.fields.remark`,
        field: "remarks",
        placeholder: `Enter Other Terms`,
        type: "text",
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
