import { module } from "./deliveriesModule";

const defaultIndexState = {
    route: module.id,
    module: {
        id: module.id,
        route: module.id,
        endpoint: module.id,
        slug: module.slug,
        singular: module.singular,
        plural: module.plural,
        showType: "modal",
        formType: "link",
        permission_prefix: `${module.snakeSlug}_`,
        query: { sort: "scheduled_date", order: "desc", limit: 50, s: "" },
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
                label: `Schedule ${module.singular}`,
                action: { name: `${module.id}.create` },
            },
        ],
    },
    filters: [
        {
            outside: true,
            type: "text",
            label: "Search",
            name: "s",
            field: "s",
            value: null,
        },
        {
            type: "select",
            label: "Type",
            name: "type",
            field: "type",
            value: null,
            options: [
                { label: "All", value: null },
                { label: "Pickup", value: "pickup" },
                { label: "Delivery", value: "delivery" },
            ],
        },
        {
            type: "select",
            label: "Status",
            name: "status",
            field: "status",
            value: null,
            options: [
                { label: "All", value: null },
                { label: "Pending", value: "pending" },
                { label: "Confirmed", value: "confirmed" },
                { label: "In Transit", value: "in_transit" },
                { label: "Completed", value: "completed" },
                { label: "Cancelled", value: "cancelled" },
            ],
        },
    ],
    columns: [
        {
            title: "Order",
            field: "order.order_number",
            thComp: "TranslatedHeader",
            sortable: false,
            colStyle: "width: 120px;",
        },
        {
            title: "Customer",
            field: "customer.name",
            thComp: "TranslatedHeader",
            sortable: false,
        },
        {
            title: "Type",
            field: "type",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 100px;",
        },
        {
            title: "Date",
            field: "scheduled_date",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 110px;",
        },
        {
            title: "Time",
            field: "scheduled_time",
            thComp: "TranslatedHeader",
            sortable: false,
            colStyle: "width: 80px;",
        },
        {
            title: "Status",
            field: "status",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 110px;",
        },
        {
            title: "Actions",
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
