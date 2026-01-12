import { module } from "./currencyModule";

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
        formType: "modal",
        formClickAction: `init-${module.slug}-form-modal`,
        permission_prefix: `${module.slug}_`,
        query: { sort: "name", order: "asc", limit: 100, s: "" },
        tableRowClick: {
            enabled: true,
            type: "event",
            action: `init-${module.slug}-show-modal`,
            actionPayloadField: "id",
        },
    },
    listPageConfigs: {
        hasActionButtons: true,
        actionButtons: [
            {
                type: "event",
                label: `Create ${module.singular}`,
                action: `init-${module.slug}-form-modal`,
                actionPayload: { id: null },
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
    ],
    columns: [
        {
            title: `general.fields.code`,
            field: "code",
            thComp: "TranslatedHeader",
            /*tdComp: "DatatableLink",*/
            sortable: true,
        },
        {
            title: `general.fields.name`,
            field: "name",
            thComp: "TranslatedHeader",
            /*tdComp: "DatatableLink",*/
            sortable: true,
        },
        {
            title: `general.fields.symbol`,
            field: "symbol",
            thComp: "TranslatedHeader",
            /*tdComp: "DatatableLink",*/
            sortable: true,
        },
        {
            title: `general.fields.rate`,
            field: "rate",
            thComp: "TranslatedHeader",
            /*tdComp: "DatatableLink",*/
            sortable: true,
        },
        {
            title: "general.fields.actions",
            field: "title",
            thComp: "TranslatedHeader",
            tdComp: "DatatableActions",
            isActions: true,
            sortable: true,
        },
    ],
};
const route = defaultIndexState.route;
const columns = defaultIndexState.listPageConfigs.columns;
const filters = defaultIndexState.listPageConfigs.filters;

const listPageConfigs = defaultIndexState.listPageConfigs;

export default defaultIndexState;

export { route, columns, filters, listPageConfigs };
