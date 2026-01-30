import { module } from "./customersModule";

const defaultEntry = () => {
    return {
        id: null,
        name: "",
        customer_code: "",
        phone: "",
        address: "",
        is_active: 1,
    };
};

const formFields = [
    {
        name: "name",
        label: "customer.fields.name",
        field: "name",
        type: "text",
        required: true,
        column: "6",
    },
    {
        name: "customer_code",
        label: "customer.fields.customer_code",
        field: "customer_code",
        type: "text",
        required: false,
        column: "6",
    },
    {
        name: "phone",
        label: "customer.fields.phone",
        field: "phone",
        type: "text",
        required: true,
        column: "6",
    },
    {
        name: "address",
        label: "customer.fields.address",
        field: "address",
        type: "textarea",
        required: false,
        column: "12",
        rows: 2,
    },
];

const showFields = [
    { label: "customer.fields.name", field: "name" },
    { label: "customer.fields.customer_code", field: "customer_code" },
    { label: "customer.fields.phone", field: "phone" },
    { label: "customer.fields.address", field: "address" },
];

const modals = {
    form: {
        id: `${module.slug}-form-modal`,
        createTitle: `Create ${module.singular}`,
        editTitle: `Edit ${module.singular}`,
        size: "modal-lg",
        centered: false,
        route: module.id,
    },
    show: {
        id: `${module.slug}-show-modal`,
        showTitle: `${module.singular} Details`,
        size: "modal-lg",
        route: module.id,
    },
};

const defaultFormState = {
    route: module.id,
    module: module,
    entry: defaultEntry(),
    formFields: formFields,
    showFields: showFields,
    modals: modals,
};

export default defaultFormState;
export { defaultEntry };
