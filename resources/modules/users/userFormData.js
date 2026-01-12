const module = { id: "users", slug: "user", singular: "User", plural: "Users" };
const defaultEntry = () => {
    return {
        id: null,
        name: null,
        email: null,
        password: null,
        roles: [],
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
        name: "email",
        label: "Email",
        field: "email",
        placeholder: "Enter Email",
        type: "email",
        required: true,
        restrictEdit: true,
        column: "12",
    },
    {
        name: "password",
        label: "Password",
        field: "password",
        placeholder: "Enter Password",
        type: "password",
        required: true,
        optionalOnEdit: true,
        column: "12",
    },
    {
        name: "roles",
        label: "Roles",
        field: "roles",
        placeholder: "Select Role(s)",
        idValue: "id",
        labelValue: "title",
        type: "select-single",
        endpoint: "roles",
        mode: "tags",
        hideSelected: true,
        required: true,
        column: "12",
    },
    {
        name: "active",
        label: `general.fields.active`,
        field: "active",
        type: "switch",
        required: true,
        column: "12",
    },
];

const showFields = [
    {
        field: "id",
        label: "general.fields.id",
    },
    {
        field: "name",
        label: "general.fields.name",
    },
    {
        field: "email",
        label: "general.fields.email",
    },
    {
        field: "role_titles",
        label: "general.fields.roles",
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
