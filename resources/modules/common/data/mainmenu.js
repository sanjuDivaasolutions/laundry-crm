/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *  Last modified: 13/02/26, 12:00 pm
 *
 */

const mainMenuPages = [
    {
        topMenu: true,
        pages: [
            {
                heading: "general.fields.dashboard",
                route: "/dashboard",
                gate: "dashboard",
                icon: "feather:home",
                topMenu: false,
            },
        ],
    },
    {
        topMenu: false,
        heading: "Operations",
        route: "/operations",
        gate: "customer_access",
        pages: [
            {
                heading: "POS",
                route: "/pos",
                gate: "order_access",
                icon: "feather:monitor",
            },
            {
                sectionTitle: "Sales",
                route: "/sales",
                icon: "feather:shopping-bag",
                gate: "order_access",
                sub: [
                    {
                        heading: "customer.title",
                        route: "/customers",
                        gate: "customer_access",
                        icon: "feather:users",
                    },
                    {
                        heading: "order.title",
                        route: "/orders",
                        gate: "order_access",
                        icon: "feather:shopping-cart",
                    },
                ],
            },
            {
                heading: "Deliveries",
                route: "/deliveries",
                gate: "delivery_access",
                icon: "feather:truck",
            },
        ],
    },
    {
        topMenu: false,
        heading: "Master Data",
        route: "/master",
        gate: "item_access",
        pages: [
            {
                sectionTitle: "Catalog",
                route: "/catalog",
                icon: "feather:box",
                gate: "item_access",
                sub: [
                    {
                        heading: "Items",
                        route: "/items",
                        gate: "item_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "Services",
                        route: "/services",
                        gate: "service_access",
                        icon: "feather:settings",
                    },
                ],
            },
        ],
    },
    {
        topMenu: false,
        heading: "Analytics",
        route: "/analytics",
        gate: "report_access",
        pages: [
            {
                heading: "Reports",
                route: "/reports",
                gate: "report_access",
                icon: "feather:bar-chart-2",
            },
        ],
    },
    {
        topMenu: false,
        heading: "general.fields.configuration",
        route: "/configuration",
        gate: "module_menu_access",
        pages: [
            {
                sectionTitle: "general.fields.modules",
                route: "/modules",
                icon: "feather:settings",
                gate: "module_menu_access",
                sub: [
                    {
                        heading: "company.title",
                        route: "/companies",
                        gate: "company_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "currency.title",
                        route: "/currencies",
                        gate: "currency_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "country.title",
                        route: "/countries",
                        gate: "country_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "state.title",
                        route: "/states",
                        gate: "state_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "city.title",
                        route: "/cities",
                        gate: "city_access",
                        icon: "feather:package",
                    },
                ],
            },
            {
                sectionTitle: "user.title",
                route: "/users",
                icon: "feather:users",
                gate: "user_menu_access",
                sub: [
                    {
                        heading: `user.title`,
                        route: "/users",
                        gate: "user_access",
                        icon: "feather:users",
                    },
                    {
                        heading: "permission.title",
                        route: "/permissions",
                        gate: "permission_access",
                        icon: "feather:users",
                    },
                    {
                        heading: "role.title",
                        route: "/roles",
                        gate: "role_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "language.title",
                        route: "/languages",
                        gate: "language_access",
                        icon: "feather:package",
                    },
                ],
            },
        ],
    },
];

export { mainMenuPages };
