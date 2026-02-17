import { module } from "./customersModule";

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
        query: { sort: "name", order: "asc", limit: 50, s: "" },
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
            label: "Status",
            name: "is_active",
            field: "is_active",
            value: null,
            options: [
                { label: "All", value: null },
                { label: "Active", value: 1 },
                { label: "Inactive", value: 0 },
            ],
        },
    ],
    columns: [
        {
            title: `customer.fields.customer_code`,
            field: "customer_code",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 120px;",
        },
        {
            title: `customer.fields.name`,
            field: "name",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: `customer.fields.phone`,
            field: "phone",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 140px;",
        },
        {
            title: `Loyalty`,
            field: "loyalty_tier",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 100px;",
        },
        {
            title: `Total Spent`,
            field: "total_spent",
            thComp: "TranslatedHeader",
            tdComp: "DatatableCurrency",
            sortable: true,
            align: "right",
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
