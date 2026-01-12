const module = {
    id: "summary-stock",
    slug: "summary-stock",
    route: `reports/summary-stock`,
    tableTitle: "Stock Summary",
    query: { sort: "name", order: "asc", limit: 500, s: "" },
    showType: "modal",
    csvRoute: "reports/csv/summary-stock",
    //pdfRoute: "reports/pdf/summary-stock",
    tableRowClick: {
        enabled: false,
    },
    badgeClass: {
        total_stock: "badge-danger",
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
        type: "select-ajax",
        label: "Product",
        name: "product",
        field: "f_product_id",
        endpoint: "active-products",
        idValue: "id",
        labelValue: "name",
        value: null,
    },
    {
        outside: true,
        type: "select-single",
        label: "Shelf",
        name: "shelf",
        field: "f_shelf_id",
        endpoint: "active-shelves",
        idValue: "id",
        labelValue: "name",
        value: null,
    },
];
const columns = [
    {
        title: `general.fields.product`,
        field: "name",
        sortable: true,
    },
    {
        title: `general.fields.shelf_stock`,
        field: "shelf_stock",
        tdComp: "DatatableBadgeList",
    },
    {
        title: `general.fields.total_stock`,
        field: "total_stock",
        align: "end",
        tdComp: "DatatableBadge",
    },
];

module.filters = filters;
module.columns = columns;

export { module };
