import { module } from "./currencyModule";
const defaultEntry = () => {
    return {
        id: null,
        code: null,
        name: null,
        symbol: null,
        rate: 1,
        active: 1,
    };
};

const formFields = [
    {
        name: "name",
        label: "Name",
        field: "name",
        placeholder: "Enter Name",
        type: "text",
        required: true,
        column: "12",
    },
    {
        name: "code",
        label: "Currency Code",
        field: "code",
        placeholder: "Enter Code",
        type: "text",
        required: true,
        column: "6",
    },
    {
        name: "symbol",
        label: "Symbol",
        field: "symbol",
        placeholder: "Enter Symbol",
        type: "text",
        required: true,
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
