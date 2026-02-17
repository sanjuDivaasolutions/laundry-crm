import { module } from "./ordersModule";

const defaultIndexState = {
    route: module.id,
    module: {
        id: module.id,
        route: module.id,
        endpoint: module.id,
        slug: module.slug,
        singular: module.singular,
        plural: module.plural,
        showType: "link",
        formType: "link",
        permission_prefix: `${module.snakeSlug}_`,
        query: { sort: "id", order: "desc", limit: 50, s: "" },
        saveState: true,
        tableRowClick: {
            enabled: false,
        },
    },
    listPageConfigs: {
        hasActionButtons: true,
        actionButtons: [
            {
                type: "link",
                label: `Create ${module.singular}`,
                action: { name: `${module.id}.create` },
            },
        ],
    },
    filters: [
        {
            outside: true,
            type: "text",
            label: "general.fields.search",
            name: "s",
            field: "s",
            value: null,
        },
        {
            type: "select",
            label: "Payment Status",
            name: "payment_status",
            field: "payment_status",
            value: null,
            options: [
                { label: "All", value: null },
                { label: "Unpaid", value: "unpaid" },
                { label: "Partial", value: "partial" },
                { label: "Paid", value: "paid" },
            ],
        },
        {
            type: "select",
            label: "Urgent",
            name: "urgent",
            field: "urgent",
            value: null,
            options: [
                { label: "All", value: null },
                { label: "Yes", value: 1 },
                { label: "No", value: 0 },
            ],
        },
    ],
    columns: [
        {
            title: `order.fields.order_number`,
            field: "order_number",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 120px;",
        },
        {
            title: `order.fields.order_date`,
            field: "order_date",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 110px;",
        },
        {
            title: `customer.title_singular`,
            field: "customer.name",
            thComp: "TranslatedHeader",
            sortable: false,
        },
        {
            title: `order.fields.total_amount`,
            field: "total_amount",
            thComp: "TranslatedHeader",
            tdComp: "DatatableCurrency",
            sortable: true,
            align: "right",
            colStyle: "width: 120px;",
        },
        {
            title: `Payment`,
            field: "payment_status",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 100px;",
        },
        {
            title: `order.fields.order_status`,
            field: "order_status",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 120px;",
        },
        {
            title: "general.fields.actions",
            field: "id",
            thComp: "TranslatedHeader",
            tdComp: "DatatableActions",
            isActions: true,
            sortable: false,
            colStyle: "width: 120px;",
        },
    ],
};

const route = defaultIndexState.route;
const columns = defaultIndexState.columns;
const filters = defaultIndexState.filters;
const listPageConfigs = defaultIndexState.listPageConfigs;

export default defaultIndexState;

export { route, columns, filters, listPageConfigs };
