const module = {
    id: "permissions",
    slug: "permission",
    singular: "Permission",
    plural: "Permissions",
};
const defaultEntry = () => {
    return {
        id: null,
        title: null,
    };
};

const formFields = [
    {
        name: "title",
        label: "Title",
        field: "title",
        placeholder: "Enter Title",
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
        field: "title",
        label: "Title",
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
