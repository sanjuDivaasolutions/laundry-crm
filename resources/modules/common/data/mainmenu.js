/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *  Last modified: 05/02/25, 4:01 pm
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
