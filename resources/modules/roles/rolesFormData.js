import { module } from "./rolesModule";
const defaultEntry = () => {
    return {
        id: null,
        title: null,
        permissions: [],
    };
};

const formFields = [
    {
        name: "title",
        label: `general.fields.title`,
        field: "title",
        placeholder: `Enter Title`,
        type: "text",
        required: true,
        column: "12",
    },
    {
        name: "permissions",
        label: `general.fields.permissions`,
        field: "permissions",
        idValue: "id",
        labelValue: "title",
        placeholder: `Enter Permissions`,
        type: "checkbox-group",
        endpoint: `permissions`,
        required: true,
        column: "12",
        options: [],
        multiple: true,
    },
];

const showFields = [
    {
        field: "id",
        label: "ID",
    },
    {
        field: "title",
        label: "Title",
    },
];

const modals = {
    form: {
        id: `${module.slug}-form-modal`,
        createTitle: `Create ${module.singular}`,
        editTitle: `Edit ${module.singular}`,
        size: "modal-xl",
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
