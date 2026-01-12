import { module } from "./paymentTermsModule";
const defaultEntry = () => {
    return {
        id: null,
        name: null,
        days: null,
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
        name: "days",
        label: `general.fields.days`,
        field: "days",
        placeholder: `Enter Number of Days`,
        type: "number",
        required: true,
        column: "12",
    },
];

const showFields = [
    {
        field: "id",
        label: `general.fields.id`,
    },
    {
        field: "name",
        label: `general.fields.name`,
    },
    {
        field: "days",
        label: `general.fields.days`,
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
