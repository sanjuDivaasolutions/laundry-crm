/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *  Last modified: 05/02/25, 4:00 pm
 *
 */

const systemRoutes = [
    {
        path: "/",
        redirect: "/dashboard",
        component: () => import("@/layouts/main-layout/MainLayout.vue"),
        meta: {
            middleware: "auth",
        },
        children: [
            {
                path: "dashboard",
                name: "dashboard",
                component: () => import("@/views/Dashboard.vue"),
                meta: {
                    pageTitle: "general.fields.dashboard",
                    breadcrumbs: [],
                },
            },
            {
                path: "roles",
                name: "roles.index",
                component: () => import("@modules@/roles/Index.vue"),
                meta: {
                    pageTitle: "role.title",
                    breadcrumbs: ["Roles"],
                },
            },
            {
                path: "users",
                name: "users",
                component: () => import("@modules@/users/Index.vue"),
                meta: {
                    pageTitle: "user.title",
                    breadcrumbs: ["Users"],
                },
            },
            {
                path: "permissions",
                name: "permissions",
                component: () => import("@modules@/permissions/Index.vue"),
                meta: {
                    pageTitle: "permission.title",
                    breadcrumbs: ["Permissions"],
                },
            },
            {
                path: "countries",
                name: "countries.index",
                component: () => import("@modules@/countries/Index.vue"),
                meta: {
                    pageTitle: "country.title",
                    breadcrumbs: ["Countries"],
                },
            },
            {
                path: "states",
                name: "states.index",
                component: () => import("@modules@/states/Index.vue"),
                meta: {
                    pageTitle: "state.title",
                    breadcrumbs: ["States"],
                },
            },
            {
                path: "cities",
                name: "cities.index",
                component: () => import("@modules@/cities/Index.vue"),
                meta: {
                    pageTitle: "city.title",
                    breadcrumbs: ["Cities"],
                },
            },
            {
                path: "languages",
                name: "languages.index",
                component: () => import("@modules@/languages/Index.vue"),
                meta: {
                    pageTitle: "language.title",
                    breadcrumbs: ["Languages"],
                },
            },
            {
                path: "languages/create",
                name: "languages.create",
                component: () => import("@modules@/languages/Create.vue"),
                meta: {
                    pageTitle: "language.title",
                    breadcrumbs: [
                        {
                            label: "Languages",
                            route: "languages.index",
                        },
                    ],
                },
            },
            {
                path: "languages/edit/:id",
                name: "languages.edit",
                component: () => import("@modules@/languages/Edit.vue"),
                meta: {
                    pageTitle: "language.title",
                    breadcrumbs: [
                        {
                            label: "Languages",
                            route: "languages.index",
                        },
                    ],
                },
            },
            {
                path: "languages/show/:id/:tab?",
                name: "languages.show",
                component: () => import("@modules@/languages/Show.vue"),
                meta: {
                    pageTitle: "language.title",
                    breadcrumbs: [
                        { label: "Languages", route: "languages.index" },
                    ],
                },
            },
            {
                path: "companies",
                name: "companies.index",
                component: () => import("@modules@/companies/Index.vue"),
                meta: {
                    pageTitle: "company.title",
                    breadcrumbs: ["Companies"],
                },
            },
            // Items
            {
                path: "items",
                name: "items.index",
                component: () => import("@modules@/items/Index.vue"),
                meta: {
                    pageTitle: "Items",
                    breadcrumbs: ["Items"],
                },
            },
            {
                path: "items/create",
                name: "items.create",
                component: () => import("@modules@/items/Create.vue"),
                meta: {
                    pageTitle: "Create Item",
                    breadcrumbs: [
                        { label: "Items", route: "items.index" },
                        "Create",
                    ],
                },
            },
            {
                path: "items/edit/:id",
                name: "items.edit",
                component: () => import("@modules@/items/Edit.vue"),
                meta: {
                    pageTitle: "Edit Item",
                    breadcrumbs: [
                        { label: "Items", route: "items.index" },
                        "Edit",
                    ],
                },
            },
            {
                path: "items/show/:id",
                name: "items.show",
                redirect: to => ({ name: "items.edit", params: { id: to.params.id } }),
            },
            // Services
            {
                path: "services",
                name: "services.index",
                component: () => import("@modules@/services/Index.vue"),
                meta: {
                    pageTitle: "Services",
                    breadcrumbs: ["Services"],
                },
            },
            // Customers
            {
                path: "customers",
                name: "customers.index",
                component: () => import("@modules@/customers/Index.vue"),
                meta: {
                    pageTitle: "customer.title",
                    breadcrumbs: ["Customers"],
                },
            },
            {
                path: "customers/create",
                name: "customers.create",
                component: () => import("@modules@/customers/Create.vue"),
                meta: {
                    pageTitle: "Create Customer",
                    breadcrumbs: [
                        { label: "Customers", route: "customers.index" },
                        "Create",
                    ],
                },
            },
            {
                path: "customers/edit/:id",
                name: "customers.edit",
                component: () => import("@modules@/customers/Edit.vue"),
                meta: {
                    pageTitle: "Edit Customer",
                    breadcrumbs: [
                        { label: "Customers", route: "customers.index" },
                        "Edit",
                    ],
                },
            },
            {
                path: "customers/show/:id/:tab?",
                name: "customers.show",
                component: () => import("@modules@/customers/Show.vue"),
                meta: {
                    pageTitle: "customer.title",
                    breadcrumbs: [
                        { label: "Customers", route: "customers.index" },
                        "Details",
                    ],
                },
            },
            // Orders
            {
                path: "orders",
                name: "orders.index",
                component: () => import("@modules@/orders/Index.vue"),
                meta: {
                    pageTitle: "order.title",
                    breadcrumbs: ["Orders"],
                },
            },
            {
                path: "orders/create",
                name: "orders.create",
                component: () => import("@modules@/orders/Create.vue"),
                meta: {
                    pageTitle: "Create Order",
                    breadcrumbs: [
                        { label: "Orders", route: "orders.index" },
                        "Create",
                    ],
                },
            },
            {
                path: "orders/edit/:id",
                name: "orders.edit",
                component: () => import("@modules@/orders/Edit.vue"),
                meta: {
                    pageTitle: "Edit Order",
                    breadcrumbs: [
                        { label: "Orders", route: "orders.index" },
                        "Edit",
                    ],
                },
            },
            {
                path: "orders/show/:id/:tab?",
                name: "orders.show",
                component: () => import("@modules@/orders/Show.vue"),
                meta: {
                    pageTitle: "order.title",
                    breadcrumbs: [
                        { label: "Orders", route: "orders.index" },
                        "Details",
                    ],
                },
            },
            // Deliveries
            {
                path: "deliveries",
                name: "deliveries.index",
                component: () => import("@modules@/deliveries/Index.vue"),
                meta: {
                    pageTitle: "Deliveries",
                    breadcrumbs: ["Deliveries"],
                },
            },
            {
                path: "deliveries/create",
                name: "deliveries.create",
                component: () => import("@modules@/deliveries/Create.vue"),
                meta: {
                    pageTitle: "Schedule Delivery",
                    breadcrumbs: [
                        { label: "Deliveries", route: "deliveries.index" },
                        "Schedule",
                    ],
                },
            },
            {
                path: "deliveries/edit/:id",
                name: "deliveries.edit",
                component: () => import("@modules@/deliveries/Edit.vue"),
                meta: {
                    pageTitle: "Edit Delivery",
                    breadcrumbs: [
                        { label: "Deliveries", route: "deliveries.index" },
                        "Edit",
                    ],
                },
            },
            // Reports
            {
                path: "reports",
                name: "reports.index",
                component: () => import("@modules@/reports/Index.vue"),
                meta: {
                    pageTitle: "Reports",
                    breadcrumbs: ["Reports"],
                },
            },
            {
                path: "reports/:type",
                name: "reports.show",
                component: () => import("@modules@/reports/Show.vue"),
                meta: {
                    pageTitle: "Report Details",
                    breadcrumbs: [
                        { label: "Reports", route: "reports.index" },
                        "Details",
                    ],
                },
            },
            // POS Board
            {
                path: "pos",
                name: "pos.board",
                component: () => import("@modules@/pos-board/Index.vue"),
                meta: {
                    pageTitle: "POS Board",
                    breadcrumbs: ["POS"],
                },
            },
            // Admin Tenant Management (Super Admin only)
            {
                path: "admin/tenants",
                name: "admin.tenants.index",
                component: () => import("@modules@/tenants/Index.vue"),
                meta: {
                    pageTitle: "Tenant Management",
                    breadcrumbs: ["Admin", "Tenants"],
                    permission: "manage-tenants",
                },
            },
        ],
    },
];

export { systemRoutes };
