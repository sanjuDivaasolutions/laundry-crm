const module = {
    id: "summary-profit-loss",
    slug: "summary-profit-loss",
    route: `reports/summary-profit-loss`,
    title: "Profit & Loss",
    subTitle: "Summary",
    query: { sort: "date", order: "asc", limit: 500, s: "" },
    showType: "modal",
    tableRowClick: {
        enabled: false,
    },
};

const headers = [];

const filters = [
    {
        outside: true,
        type: "date-range",
        label: "Date Range",
        name: "f_date_range",
        field: "f_date_range",
        value:
            moment()
                .subtract(12, "months")
                .format($headMeta("moment_date_format")) +
            " to " +
            moment().format($headMeta("moment_date_format")),
    },
    {
        outside: true,
        type: "select-single",
        label: "Company",
        name: "company",
        field: "f_company_id",
        endpoint: "companies",
        value: null,
    },
];

module.headers = headers;
module.filters = filters;

export { module };
