/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *  Last modified: 05/02/25, 4:00 pm
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
            {
                path: "services",
                name: "services.index",
                component: () => import("@modules@/services/Index.vue"),
                meta: {
                    pageTitle: "Services",
                    breadcrumbs: ["Services"],
                },
            },
            {
                path: "customers",
                name: "customers.index",
                component: () => import("@modules@/customers/Index.vue"),
                meta: {
                    pageTitle: "customer.title",
                    breadcrumbs: ["customer.title"],
                },
            },
            {
                path: "customers/show/:id/:tab?",
                name: "customers.show",
                component: () => import("@modules@/customers/Show.vue"),
                meta: {
                    pageTitle: "customer.title",
                    breadcrumbs: [
                        { label: "customer.title", route: "customers.index" },
                        "general.fields.details",
                    ],
                },
            },
            {
                path: "orders",
                name: "orders.index",
                component: () => import("@modules@/orders/Index.vue"),
                meta: {
                    pageTitle: "order.title",
                    breadcrumbs: ["order.title"],
                },
            },
            {
                path: "orders/show/:id/:tab?",
                name: "orders.show",
                component: () => import("@modules@/orders/Show.vue"),
                meta: {
                    pageTitle: "order.title",
                    breadcrumbs: [
                        { label: "order.title", route: "orders.index" },
                        "general.fields.details",
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
