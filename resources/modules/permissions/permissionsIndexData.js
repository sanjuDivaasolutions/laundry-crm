const module = {
    id: "permissions",
    slug: "permission",
    singular: "Permission",
    plural: "Permissions",
};
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
        query: { sort: "title", order: "asc", limit: 100, s: "" },
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
        {
            name: "permission_groups",
            label: `general.fields.permission_group`,
            field: "f_permission_group",
            placeholder: "Select Group",
            idValue: "id",
            labelValue: "name",
            type: "select-single",
            endpoint: "permission-groups",
            mode: "single",
            hideSelected: true,
            required: true,
            column: "12",
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
            title: `general.fields.title`,
            field: "title",
            thComp: "TranslatedHeader",
            /*tdComp: "DatatableLink",*/
            sortable: true,
        },
        {
            title: `general.fields.permission_group`,
            field: "group_name",
            thComp: "TranslatedHeader",
            /*tdComp: "DatatableDeep",*/
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
