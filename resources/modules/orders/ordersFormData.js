import { module } from "./ordersModule";

const defaultEntry = () => {
    return {
        id: null,
        order_number: "",
        customer: { name: "" },
        order_date: "",
        total_amount: 0,
        payment_status: "",
        order_status: "",
        items: [],
    };
};

const formFields = [
    {
        name: "order_number",
        label: "order.fields.order_number",
        field: "order_number",
        type: "text",
        required: true,
        column: "6",
    },
    {
        name: "order_date",
        label: "order.fields.order_date",
        field: "order_date",
        type: "date",
        required: true,
        column: "6",
    },
];

const showFields = [
    { label: "order.fields.order_number", field: "order_number" },
    { label: "order.fields.order_date", field: "order_date" },
    { label: "order.fields.promised_date", field: "promised_date" },
    { label: "order.fields.total_amount", field: "total_amount" },
    { label: "order.fields.payment_status", field: "payment_status" },
    { label: "order.fields.order_status", field: "order_status" },
    { label: "order.fields.urgent", field: "urgent" },
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

const defaultFormState = {
    route: module.id,
    module: module,
    entry: defaultEntry(),
    formFields: formFields,
    showFields: showFields,
    modals: modals,
};

export default defaultFormState;
export { defaultEntry };
