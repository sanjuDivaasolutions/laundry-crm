const module = {
    id: "inwards-by-product",
    slug: "product",
    route: `reports/inwards-by-product`,
    tableTitle: "Total Inwards by Product",
    csvRoute: "reports/csv/inwards-by-product",
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
        title: `general.fields.sku`,
        field: "sku",
        sortable: true,
    },
    {
        title: `general.fields.name`,
        field: "name",
        sortable: true,
    },
    {
        title: `general.fields.quantity`,
        field: "quantity",
        align: "end",
        sortable: false,
    },
    {
        title: `general.fields.grand_total`,
        field: "total_label",
        align: "end",
        sortable: false,
    },
    {
        title: "Actions",
        field: "actions",
        sortable: false,
        align: "center",
        width: "80px",
        tdComp: "ProductInwardDetailsButton",
    },
];

module.filters = filters;
module.columns = columns;

export { module };