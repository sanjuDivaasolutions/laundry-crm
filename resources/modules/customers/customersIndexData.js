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
        formType: "modal",
        formClickAction: ``,
        permission_prefix: `${module.snakeSlug}_`,
        query: { sort: "name", order: "asc", limit: 50, s: "" },
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
        },
        {
            title: `customer.fields.address`,
            field: "address",
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
