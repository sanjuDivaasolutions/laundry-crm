import { module } from "./servicesModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
        code: null,
        description: null,
        display_order: 0,
        is_active: true,
    };
};

const formFields = [
    {
        name: "name",
        label: "Service Name",
        field: "name",
        placeholder: "Enter service name (e.g., Wash, Dry Clean, Iron)",
        type: "text",
        required: true,
        column: "6",
    },
    {
        name: "display_order",
        label: "Display Order",
        field: "display_order",
        placeholder: "Enter display order",
        type: "number",
        required: false,
        column: "6",
    },
    {
        name: "is_active",
        label: "Active",
        field: "is_active",
        type: "switch",
        required: false,
        column: "6",
    },
    {
        name: "description",
        label: "Description",
        field: "description",
        placeholder: "Enter description (optional)",
        type: "textarea",
        required: false,
        column: "12",
        rows: 3,
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
        field: "code",
        label: "Code",
    },
    {
        field: "display_order",
        label: "Display Order",
    },
    {
        field: "is_active",
        label: "Status",
    },
    {
        field: "description",
        label: "Description",
    },
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
