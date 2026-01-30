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
        formType: "modal",
        formClickAction: ``,
        permission_prefix: `${module.snakeSlug}_`,
        query: { sort: "order_number", order: "desc", limit: 50, s: "" },
        saveState: true,
        tableRowClick: {
            enabled: false,
            type: "link",
            action: ``,
            actionPayloadField: "id",
        },
    },
    listPageConfigs: {
        hasActionButtons: false,
        actionButtons: [],
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
        },
        {
            title: `order.fields.order_status`,
            field: "order_status",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: "general.fields.actions",
            field: "id",
            thComp: "TranslatedHeader",
            tdComp: "DatatableActions",
            isActions: true,
            sortable: false,
            colStyle: "width: 100px;",
        },
    ],
};

const route = defaultIndexState.route;
const columns = defaultIndexState.columns;
const filters = defaultIndexState.filters;
const listPageConfigs = defaultIndexState.listPageConfigs;

export default defaultIndexState;

export { route, columns, filters, listPageConfigs };
