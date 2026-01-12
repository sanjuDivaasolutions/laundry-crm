const module = {
    id: "sales-by-month",
    slug: "month",
    route: `reports/sales-by-month`,
    tableTitle: "Total Monthly Sales",
    query: {
        sort: "date",
        order: "asc",
        limit: 500,
        s: "",
    },
    showType: "modal",
    tableRowClick: {
        enabled: false,
    },
};

const filters = [
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
        type: "date-range",
        label: "Date Range",
        name: "f_date_range",
        field: "f_date_range",
        value: $thisYearDateRange(),
    },
];
const columns = [
    {
        title: `general.fields.month`,
        field: "month",
        thComp: "TranslatedHeader",
        sortable: false,
    },
    {
        title: `general.fields.total_sales`,
        field: "total_label",
        align: "end",
        sortable: false,
    },
];

module.filters = filters;
module.columns = columns;

export { module };
