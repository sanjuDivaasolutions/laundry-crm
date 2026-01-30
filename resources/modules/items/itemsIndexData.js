import { module } from "./itemsModule";

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
        query: { sort: "display_order", order: "asc", limit: 50, s: "" },
        saveState: true,
        tableRowClick: {
            enabled: false,
        },
        import: {
            enabled: false,
            endpoint: `import/${module.id}`,
            label: `Import ${module.plural}`,
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
            label: "Search",
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
            title: `general.fields.id`,
            field: "id",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 80px;",
        },
        {
            title: `Code`,
            field: "code",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 120px;",
        },
        {
            title: `Name`,
            field: "name",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: `Price`,
            field: "price",
            thComp: "TranslatedHeader",
            tdComp: "DatatableCurrency",
            sortable: true,
            align: "right",
            colStyle: "width: 120px;",
        },
        {
            title: `Order`,
            field: "display_order",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 80px;",
        },
        {
            title: `Status`,
            field: "is_active",
            thComp: "TranslatedHeader",
            tdComp: "DatatableBoolean",
            sortable: true,
            colStyle: "width: 100px;",
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
