import { module } from "./categoriesModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
        display_order: 0,
        is_active: true,
    };
};

const formFields = [
    {
        name: "name",
        label: "Category Name",
        field: "name",
        placeholder: "Enter category name (e.g., Wash, Iron, Dry Clean)",
        type: "text",
        required: true,
        column: "12",
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
        field: "display_order",
        label: "Display Order",
    },
    {
        field: "is_active",
        label: "Status",
        type: "boolean",
    },
];

const modals = {
    form: {
        id: `${module.slug}-form-modal`,
        createTitle: `Create ${module.singular}`,
        editTitle: `Edit ${module.singular}`,
        size: "modal-md",
        centered: false,
        route: module.id,
    },
    show: {
        id: `${module.slug}-show-modal`,
        showTitle: `${module.singular} Details`,
        size: "modal-md",
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
