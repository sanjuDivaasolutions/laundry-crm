import { module } from "./tenantsModule";

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
        formClickAction: `init-${module.slug}-show-modal`,
        permission_prefix: `${module.snakeSlug}_`,
        query: { sort: "created_at", order: "desc", limit: 15, s: "" },
        saveState: true,
        tableRowClick: {
            enabled: true,
            type: "modal",
            action: `init-${module.slug}-show-modal`,
            actionPayloadField: "id",
        },
        import: {
            enabled: false,
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
            label: "Search",
            name: "search",
            field: "search",
            value: null,
        },
        {
            type: "select",
            label: "Status",
            name: "status",
            field: "status",
            value: null,
            options: [
                { label: "All", value: null },
                { label: "Active", value: "active" },
                { label: "Inactive", value: "inactive" },
                { label: "On Trial", value: "trial" },
                { label: "Expired Trial", value: "expired_trial" },
                { label: "Subscribed", value: "subscribed" },
                { label: "Grace Period", value: "grace_period" },
            ],
        },
    ],
    columns: [
        {
            title: `ID`,
            field: "id",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 60px;",
        },
        {
            title: `Company`,
            field: "name",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: `Subdomain`,
            field: "domain",
            thComp: "TranslatedHeader",
            tdComp: "TenantDomainCell",
            sortable: true,
            colStyle: "width: 180px;",
        },
        {
            title: `Status`,
            field: "status_label",
            thComp: "TranslatedHeader",
            tdComp: "TenantStatusBadge",
            sortable: false,
            colStyle: "width: 120px;",
        },
        {
            title: `Plan`,
            field: "current_plan",
            thComp: "TranslatedHeader",
            sortable: false,
            colStyle: "width: 100px;",
        },
        {
            title: `Users`,
            field: "users_count",
            thComp: "TranslatedHeader",
            sortable: true,
            align: "center",
            colStyle: "width: 80px;",
        },
        {
            title: `Trial Days`,
            field: "trial_days_remaining",
            thComp: "TranslatedHeader",
            tdComp: "TenantTrialDays",
            sortable: false,
            align: "center",
            colStyle: "width: 100px;",
        },
        {
            title: `Created`,
            field: "created_at",
            thComp: "TranslatedHeader",
            tdComp: "DatatableDate",
            sortable: true,
            colStyle: "width: 120px;",
        },
        {
            title: "Actions",
            field: "id",
            thComp: "TranslatedHeader",
            tdComp: "TenantActions",
            isActions: true,
            sortable: false,
            colStyle: "width: 140px;",
        },
    ],
};

const route = defaultIndexState.route;
const columns = defaultIndexState.columns;
const filters = defaultIndexState.filters;
const listPageConfigs = defaultIndexState.listPageConfigs;

export default defaultIndexState;

export { route, columns, filters, listPageConfigs };
