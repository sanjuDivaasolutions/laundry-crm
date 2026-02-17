import { module } from "./deliveriesModule";

const defaultEntry = () => {
    return {
        id: null,
        order_id: null,
        customer_id: null,
        type: "delivery",
        scheduled_date: "",
        scheduled_time: "",
        address: "",
        notes: "",
        status: "pending",
    };
};

const formFields = [
    {
        name: "order_id",
        label: "Order",
        field: "order_id",
        type: "select-single",
        required: false,
        column: "6",
    },
    {
        name: "customer_id",
        label: "Customer",
        field: "customer_id",
        type: "select-single",
        required: false,
        column: "6",
    },
    {
        name: "type",
        label: "Type",
        field: "type",
        type: "select-single",
        required: true,
        column: "4",
    },
    {
        name: "scheduled_date",
        label: "Scheduled Date",
        field: "scheduled_date",
        type: "date",
        required: true,
        column: "4",
    },
    {
        name: "scheduled_time",
        label: "Scheduled Time",
        field: "scheduled_time",
        type: "text",
        required: false,
        column: "4",
    },
    {
        name: "address",
        label: "Address",
        field: "address",
        type: "textarea",
        required: false,
        column: "12",
        rows: 2,
    },
    {
        name: "notes",
        label: "Notes",
        field: "notes",
        type: "textarea",
        required: false,
        column: "12",
        rows: 2,
    },
];

const showFields = [
    { label: "Order", field: "order.order_number" },
    { label: "Customer", field: "customer.name" },
    { label: "Type", field: "type" },
    { label: "Scheduled Date", field: "scheduled_date" },
    { label: "Scheduled Time", field: "scheduled_time" },
    { label: "Address", field: "address" },
    { label: "Status", field: "status" },
    { label: "Notes", field: "notes" },
];

const modals = {
    form: {
        id: `${module.slug}-form-modal`,
        createTitle: `Schedule ${module.singular}`,
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
