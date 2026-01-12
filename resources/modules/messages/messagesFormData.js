import { module } from "./messagesModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
    };
};

const formFields = [
    {
        name: "schedule_at",
        label: `general.fields.schedule_at`,
        field: "schedule_at",
        placeholder: `Select Date`,
        type: "date",
        required: true,
        column: "12",
    },
    {
        name: "subject",
        label: `general.fields.subject`,
        field: "subject",
        placeholder: `Enter Subject`,
        type: "text",
        required: true,
        column: "12",
    },
    {
        name: "message",
        label: `general.fields.message`,
        field: "message",
        placeholder: `Message`,
        type: "ckeditor",
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
