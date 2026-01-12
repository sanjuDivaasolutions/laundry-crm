import { module } from "./agentsModule";

const apiRoute = module.endpoint || module.id;

const defaultIndexState = {
    route: apiRoute,
    module: {
        id: module.id,
        route: module.id,
        endpoint: apiRoute,
        slug: module.slug,
        singular: module.singular,
        plural: module.plural,
        showType: "link",
        formType: "link",
        formClickAction: `init-${module.slug}-form-modal`,
        permission_prefix: `${
            module.permissionPrefix || module.snakeSlug || module.slug
        }_`,
        query: { sort: "name", order: "asc", limit: 100, s: "" },
        saveState: true,
        csvRoute: `${apiRoute}-csv`,
        //pdfRoute: `${apiRoute}-pdf`,
        tableRowClick: {
            enabled: false,
            type: "link",
            action: `init-${module.slug}-show-modal`,
            actionPayloadField: "id",
        },
        import: {
            enabled: false,
            endpoint: `import/${apiRoute}`,
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
            title: `general.fields.id`,
            field: "id",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 100px;",
        },
        {
            title: `general.fields.name`,
            field: "name",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: "Actions",
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
