const module = {
    id: "sales-commission",
    slug: "commission",
    route: `reports/sales-commission`,
    tableTitle: "Sales Commission Report",
    csvRoute: "reports/csv/sales-commission",
    query: {
        sort: "name",
        order: "asc",
        limit: 500,
        s: "",
    },
    showType: "modal",
    tableRowClick: {
        enabled: false,
    },
    supportsDetails: true,
};

const filters = [
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
        type: "date-range",
        label: "Date Range",
        name: "f_date_range",
        field: "f_date_range",
        value: $thisYearDateRange(),
    },
];
const columns = [
    {
        title: `general.fields.agent`,
        field: "agent_name",
        sortable: true,
    },
    {
        title: `general.fields.total_commission`,
        field: "total_commission_label",
        align: "end",
        sortable: false,
    },
    {
        title: "Actions",
        field: "actions",
        sortable: false,
        align: "center",
        width: "80px",
        tdComp: "AgentCommissionDetailsButton",
    },
];

module.filters = filters;
module.columns = columns;

export { module };