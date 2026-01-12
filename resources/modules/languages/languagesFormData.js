import { module } from "./languagesModule";

const translationModule = "translations";
const defaultEntry = () => {
    return {
        id: null,
        name: null,
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
        name: "translations",
        label: `general.fields.translations`,
        field: "translations",
        entry: {},
        subFields: [
            {
                name: "translation",
                label: `general.fields.translation`,
                field: "translation",
                placeholder: `Enter Translation`,
                type: "text",
                required: true,
                column: "12",
            },
        ],
        type: "items",
        required: true,
        allowNewItem: false,
        allowRemoveItem: false,
        showInMode: ["edit"],
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
