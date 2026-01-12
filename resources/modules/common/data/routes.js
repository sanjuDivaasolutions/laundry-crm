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
                    pageTitle: "Dashboard",
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
        ],
    },
];

export { systemRoutes };
