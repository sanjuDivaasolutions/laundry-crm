import { module } from "./shelvesModule";

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
        query: { sort: "name", order: "asc", limit: 100, s: "" },
        saveState: true,
        restrictDelete: true,
        tableRowClick: {
            enabled: true,
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
            type: "checkbox-inline",
            label: "Current Company Only",
            name: "f_current_company_only",
            field: "f_current_company_only",
            value: true,
        },
        {
            outside: true,
            type: "text",
            label: "Search",
            name: "s",
            field: "s",
            value: null,
        },
        {
            outside: true,
            type: "select-single",
            label: `general.fields.warehouse`,
            name: "warehouse_id",
            field: "f_warehouse_id",
            idValue: "id",
            labelValue: "name",
            placeholder: "Select Warehouse",
            endpoint: "warehouses",
            mode: "single",
            hideSelected: true,
            value: null,
        },
        {
            outside: true,
            type: "checkbox-inline",
            label: "Active Only",
            name: "f_active_only",
            field: "f_active_only",
            value: true,
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
            title: `general.fields.name`,
            field: "name",
            sortable: true,
        },
        {
            title: `general.fields.warehouse`,
            field: "warehouse.name",
            sortable: true,
        },
        {
            title: `Stock`,
            field: "on_hand",
            sortable: false,
        },
        {
            title: `general.fields.active`,
            field: "active_label",
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
