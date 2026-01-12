import { module } from "./shelvesModule";

const defaultEntry = () => {
    return {
        id: null,
        name: null,
        warehouse: null,
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
        name: "warehouse",
        label: "Warehouse",
        field: "warehouse",
        placeholder: "Select Warehouse",
        idValue: "id",
        labelValue: "name",
        type: "select-ajax",
        endpoint: "warehouses",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "12",
    },
    // active switch
    {
        name: "active",
        label: `general.fields.active`,
        field: "active",
        type: "switch",
        required: false,
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
        field: "warehouse.name",
        label: `general.fields.warehouse`,
    },
    {
        field: "active_label",
        label: `general.fields.active`,
    },
    {
        field: "product_summary.total_products",
        label: "Products on this shelf",
    },
    {
        field: "product_summary.total_quantity_text",
        label: "Total On-hand Quantity",
    },
    {
        field: "product_summary.type_breakdown",
        label: "Product Type Breakdown",
        type: "items",
        subFields: [
            {
                field: "type_label",
                label: `general.fields.type`,
            },
            {
                field: "unique_products",
                label: "Products",
                align: "right",
            },
            {
                field: "total_quantity_text",
                label: "Total On-hand Quantity",
                align: "right",
            },
        ],
    },
    {
        field: "products",
        label: "Products on Shelf",
        type: "items",
        scrollable: true,
        subFields: [
            {
                field: "name",
                label: `general.fields.product`,
            },
            {
                field: "sku",
                label: `general.fields.sku`,
            },
            {
                field: "code",
                label: `general.fields.code`,
            },
            {
                field: "type_label",
                label: `general.fields.type`,
            },
            {
                field: "category",
                label: `general.fields.category`,
            },
            {
                field: "on_hand_text",
                label: "On-hand Quantity",
                align: "right",
            },
            {
                field: "in_transit_text",
                label: "In Transit",
                align: "right",
            },
        ],
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
