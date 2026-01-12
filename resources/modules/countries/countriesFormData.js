const module = {
    id: "countries",
    slug: "country",
    singular: "Country",
    plural: "Countries",
};
const defaultEntry = () => {
    return {
        id: null,
        name: null,
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
