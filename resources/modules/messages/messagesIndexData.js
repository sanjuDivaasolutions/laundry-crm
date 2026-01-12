import { module } from "./messagesModule";

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
        permission_prefix: `${module.snakeSlug || module.slug}_`,
        query: { sort: "schedule_at", order: "desc", limit: 100, s: "" },
        saveState: true,
        csvRoute: `${module.id}-csv`,
        //pdfRoute: `${module.id}-pdf`,
        //defaultsRoute: `${module.id}/create`,
        tableRowClick: {
            enabled: false,
            type: "modal",
            action: `init-${module.slug}-show-modal`,
            actionPayloadField: "id",
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
            title: `general.fields.id`,
            field: "id",
            sortable: true,
            colStyle: "width: 100px;",
        },
        {
            title: `general.fields.subject`,
            field: "subject",
            sortable: true,
        },
        {
            title: `general.fields.schedule_at`,
            field: "schedule_at",
            sortable: true,
        },
        {
            title: `general.fields.status`,
            field: "status_label",
            sortable: true,
        },
        {
            title: "Actions",
            field: "title",
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
