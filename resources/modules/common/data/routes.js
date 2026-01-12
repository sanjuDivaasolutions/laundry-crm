/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 05/02/25, 4:00 pm
 *  *  Written by Chintan Bagdawala, 2025.
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
            {
                path: "units",
                name: "units.index",
                component: () => import("@modules@/units/Index.vue"),
                meta: {
                    pageTitle: "unit.title",
                    breadcrumbs: ["Units"],
                },
            },
            {
                path: "warehouses",
                name: "warehouses.index",
                component: () => import("@modules@/warehouses/Index.vue"),
                meta: {
                    pageTitle: "warehouse.title",
                    breadcrumbs: ["Warehouses"],
                },
            },
            {
                path: "shelves",
                name: "shelves.index",
                component: () => import("@modules@/shelves/Index.vue"),
                meta: {
                    pageTitle: "shelf.title",
                    breadcrumbs: ["Shelves"],
                },
            },
            {
                path: "currencies",
                name: "currencies.index",
                component: () => import("@modules@/currencies/Index.vue"),
                meta: {
                    pageTitle: "currency.title",
                    breadcrumbs: ["Currencies"],
                },
            },
            {
                path: "payment-terms",
                name: "payment-terms.index",
                component: () => import("@modules@/paymentTerms/Index.vue"),
                meta: {
                    pageTitle: "paymentTerm.title",
                    breadcrumbs: ["Payment Terms"],
                },
            },

            {
                path: "buyers",
                name: "buyers.index",
                component: () => import("@modules@/buyers/Index.vue"),
                meta: {
                    pageTitle: "buyer.title",
                    breadcrumbs: ["Buyers"],
                },
            },
            {
                path: "buyers/show/:id/:tab?",
                name: "buyers.show",
                component: () => import("@modules@/buyers/Show.vue"),
                meta: {
                    pageTitle: "buyer.title",
                    breadcrumbs: [{ label: "Buyers", route: "buyers.index" }],
                },
            },
            {
                path: "buyers/create/:type?/:id?",
                name: "buyers.create",
                component: () => import("@modules@/buyers/Create.vue"),
                meta: {
                    pageTitle: "buyer.title",
                    breadcrumbs: [{ label: "Buyers", route: "buyers.index" }],
                },
            },
            {
                path: "buyers/edit/:id",
                name: "buyers.edit",
                component: () => import("@modules@/buyers/Edit.vue"),
                meta: {
                    pageTitle: "buyer.title",
                    breadcrumbs: [{ label: "Buyers", route: "buyers.index" }],
                },
            },
            {
                path: "suppliers",
                name: "suppliers.index",
                component: () => import("@modules@/suppliers/Index.vue"),
                meta: {
                    pageTitle: "supplier.title",
                    breadcrumbs: ["Suppliers"],
                },
            },
            {
                path: "suppliers/show/:id/:tab?",
                name: "suppliers.show",
                component: () => import("@modules@/suppliers/Show.vue"),
                meta: {
                    pageTitle: "supplier.title",
                    breadcrumbs: [
                        { label: "Suppliers", route: "suppliers.index" },
                    ],
                },
            },
            {
                path: "suppliers/create/:type?/:id?",
                name: "suppliers.create",
                component: () => import("@modules@/suppliers/Create.vue"),
                meta: {
                    pageTitle: "supplier.title",
                    breadcrumbs: [
                        { label: "Suppliers", route: "suppliers.index" },
                    ],
                },
            },
            {
                path: "suppliers/edit/:id",
                name: "suppliers.edit",
                component: () => import("@modules@/suppliers/Edit.vue"),
                meta: {
                    pageTitle: "supplier.title",
                    breadcrumbs: [
                        { label: "Suppliers", route: "suppliers.index" },
                    ],
                },
            },
            {
                path: "agents",
                name: "agents.index",
                component: () => import("@modules@/agents/Index.vue"),
                meta: {
                    pageTitle: "Agent",
                    breadcrumbs: ["Agents"],
                },
            },
            {
                path: "agents/show/:id/:tab?",
                name: "agents.show",
                component: () => import("@modules@/agents/Show.vue"),
                meta: {
                    pageTitle: "Agent",
                    breadcrumbs: [
                        { label: "Agents", route: "agents.index" },
                    ],
                },
            },
            {
                path: "agents/create/:type?/:id?",
                name: "agents.create",
                component: () => import("@modules@/agents/Create.vue"),
                meta: {
                    pageTitle: "Agent",
                    breadcrumbs: [
                        { label: "Agents", route: "agents.index" },
                    ],
                },
            },
            {
                path: "agents/edit/:id",
                name: "agents.edit",
                component: () => import("@modules@/agents/Edit.vue"),
                meta: {
                    pageTitle: "Agent",
                    breadcrumbs: [
                        { label: "Agents", route: "agents.index" },
                    ],
                },
            },
            {
                path: "pos",
                name: "pos.index",
                component: () => import("@modules@/pos/Index.vue"),
                meta: {
                    pageTitle: "POS System",
                    breadcrumbs: ["POS"],
                },
            },
            {
                path: "products",
                name: "products.index",
                component: () => import("@modules@/products/Index.vue"),
                meta: {
                    pageTitle: "product.title",
                    breadcrumbs: ["Products"],
                },
            },
            {
                path: "products/show/:id/:tab?",
                name: "products.show",
                component: () => import("@modules@/products/Show.vue"),
                meta: {
                    pageTitle: "product.title",
                    breadcrumbs: [
                        { label: "Products", route: "products.index" },
                    ],
                },
            },
            {
                path: "products/create/:type?/:id?",
                name: "products.create",
                component: () => import("@modules@/products/Create.vue"),
                meta: {
                    pageTitle: "product.title",
                    breadcrumbs: [
                        { label: "Products", route: "products.index" },
                    ],
                },
            },
            {
                path: "products/edit/:id",
                name: "products.edit",
                component: () => import("@modules@/products/Edit.vue"),
                meta: {
                    pageTitle: "product.title",
                    breadcrumbs: [
                        { label: "Products", route: "products.index" },
                    ],
                },
            },
            {
                path: "inventory-adjustments",
                name: "inventory-adjustments.index",
                component: () =>
                    import("@modules@/inventoryAdjustments/Index.vue"),
                meta: {
                    pageTitle: "inventoryAdjustment.title",
                    breadcrumbs: ["Adjustments"],
                },
            },
            {
                path: "services",
                name: "services.index",
                component: () => import("@modules@/services/Index.vue"),
                meta: {
                    pageTitle: "service.title",
                    breadcrumbs: ["Services"],
                },
            },
            {
                path: "categories",
                name: "categories.index",
                component: () => import("@modules@/categories/Index.vue"),
                meta: {
                    pageTitle: "category.title",
                    breadcrumbs: ["Categories"],
                },
            },
            {
                path: "features",
                name: "features.index",
                component: () => import("@modules@/features/Index.vue"),
                meta: {
                    pageTitle: "feature.title",
                    breadcrumbs: ["Features"],
                },
            },
            {
                path: "purchase-orders",
                name: "purchase-orders.index",
                component: () => import("@modules@/purchaseOrders/Index.vue"),
                meta: {
                    pageTitle: "purchaseOrder.title",
                    breadcrumbs: ["Purchase Orders"],
                },
            },
            {
                path: "purchase-orders/show/:id/:tab?",
                name: "purchase-orders.show",
                component: () => import("@modules@/purchaseOrders/Show.vue"),
                meta: {
                    pageTitle: "purchaseOrder.title",
                    breadcrumbs: [
                        {
                            label: "Purchase Orders",
                            route: "purchase-orders.index",
                        },
                    ],
                },
            },
            {
                path: "purchase-orders/create/:type?/:id?",
                name: "purchase-orders.create",
                component: () => import("@modules@/purchaseOrders/Create.vue"),
                meta: {
                    pageTitle: "purchaseOrder.title",
                    breadcrumbs: [
                        {
                            label: "Purchase Orders",
                            route: "purchase-orders.index",
                        },
                    ],
                },
            },
            {
                path: "purchase-orders/edit/:id",
                name: "purchase-orders.edit",
                component: () => import("@modules@/purchaseOrders/Edit.vue"),
                meta: {
                    pageTitle: "purchaseOrder.title",
                    breadcrumbs: [
                        {
                            label: "Purchase Orders",
                            route: "purchase-orders.index",
                        },
                    ],
                },
            },
            {
                path: "inwards",
                name: "inwards.index",
                component: () => import("@modules@/inwards/Index.vue"),
                meta: {
                    pageTitle: "inward.title",
                    breadcrumbs: ["Inwards"],
                },
            },
            {
                path: "inwards/create",
                name: "inwards.create",
                component: () => import("@modules@/inwards/Create.vue"),
                meta: {
                    pageTitle: "inward.title",
                    breadcrumbs: [
                        {
                            label: "Inwards",
                            route: "inwards.index",
                        },
                    ],
                },
            },
            {
                path: "inwards/edit/:id",
                name: "inwards.edit",
                component: () => import("@modules@/inwards/Edit.vue"),
                meta: {
                    pageTitle: "inward.title",
                    breadcrumbs: [
                        {
                            label: "Inwards",
                            route: "inwards.index",
                        },
                    ],
                },
            },
            {
                path: "inwards/show/:id/:tab?",
                name: "inwards.show",
                component: () => import("@modules@/inwards/Show.vue"),
                meta: {
                    pageTitle: "inward.title",
                    breadcrumbs: [{ label: "Inwards", route: "inwards.index" }],
                },
            },
            {
                path: "quotations",
                name: "quotations.index",
                component: () => import("@modules@/quotations/Index.vue"),
                meta: {
                    pageTitle: "quotation.title",
                    breadcrumbs: ["Quotations"],
                },
            },
            {
                path: "quotations/show/:id/:tab?",
                name: "quotations.show",
                component: () => import("@modules@/quotations/Show.vue"),
                meta: {
                    pageTitle: "quotation.title",
                    breadcrumbs: [
                        { label: "Quotations", route: "quotations.index" },
                    ],
                },
            },
            {
                path: "quotations/create",
                name: "quotations.create",
                component: () => import("@modules@/quotations/Create.vue"),
                meta: {
                    pageTitle: "quotation.title",
                    breadcrumbs: [
                        {
                            label: "Quotations",
                            route: "quotations.index",
                        },
                    ],
                },
            },
            {
                path: "quotations/edit/:id",
                name: "quotations.edit",
                component: () => import("@modules@/quotations/Edit.vue"),
                meta: {
                    pageTitle: "quotation.title",
                    breadcrumbs: [
                        {
                            label: "Quotations",
                            route: "quotations.index",
                        },
                    ],
                },
            },
            {
                path: "sales-orders",
                name: "sales-orders.index",
                component: () => import("@modules@/salesOrders/Index.vue"),
                meta: {
                    pageTitle: "salesOrder.title",
                    breadcrumbs: ["Sales Orders"],
                },
            },
            {
                path: "sales-orders/show/:id/:tab?",
                name: "sales-orders.show",
                component: () => import("@modules@/salesOrders/Show.vue"),
                meta: {
                    pageTitle: "salesOrder.title",
                    breadcrumbs: [
                        { label: "Sale Orders", route: "sales-orders.index" },
                    ],
                },
            },
            {
                path: "sales-orders/create/:type?/:id?",
                name: "sales-orders.create",
                component: () => import("@modules@/salesOrders/Create.vue"),
                meta: {
                    pageTitle: "salesOrder.title",
                    breadcrumbs: [
                        {
                            label: "Sales Orders",
                            route: "sales-orders.index",
                        },
                    ],
                },
            },
            {
                path: "sales-orders/edit/:id",
                name: "sales-orders.edit",
                component: () => import("@modules@/salesOrders/Edit.vue"),
                meta: {
                    pageTitle: "salesOrder.title",
                    breadcrumbs: [
                        {
                            label: "Sales Orders",
                            route: "sales-orders.index",
                        },
                    ],
                },
            },
            {
                path: "sales-invoices",
                name: "sales-invoices.index",
                component: () => import("@modules@/salesInvoices/Index.vue"),
                meta: {
                    pageTitle: "salesInvoice.title",
                    breadcrumbs: ["Sales Invoices"],
                },
            },
            {
                path: "sales-invoices/create/:type?/:id?",
                name: "sales-invoices.create",
                component: () => import("@modules@/salesInvoices/Create.vue"),
                meta: {
                    pageTitle: "salesInvoice.title",
                    breadcrumbs: [
                        {
                            label: "Sales Invoices",
                            route: "sales-invoices.index",
                        },
                    ],
                },
            },
            {
                path: "sales-invoices/edit/:id",
                name: "sales-invoices.edit",
                component: () => import("@modules@/salesInvoices/Edit.vue"),
                meta: {
                    pageTitle: "salesInvoice.title",
                    breadcrumbs: [
                        {
                            label: "Sales Invoices",
                            route: "sales-invoices.index",
                        },
                    ],
                },
            },
            {
                path: "sales-invoices/show/:id/:tab?",
                name: "sales-invoices.show",
                component: () => import("@modules@/salesInvoices/Show.vue"),
                meta: {
                    pageTitle: "salesInvoice.title",
                    breadcrumbs: [
                        {
                            label: "Sales Invoices",
                            route: "sales-invoices.index",
                        },
                    ],
                },
            },
            {
                path: "packages",
                name: "packages.index",
                component: () => import("@modules@/packages/Index.vue"),
                meta: {
                    pageTitle: "package.title",
                    breadcrumbs: ["Packages"],
                },
            },
            {
                path: "packages/show/:id/:tab?",
                name: "packages.show",
                component: () => import("@modules@/packages/Show.vue"),
                meta: {
                    pageTitle: "package.title",
                    breadcrumbs: [
                        { label: "Packages", route: "packages.index" },
                    ],
                },
            },
            {
                path: "service-invoices",
                name: "service-invoices.index",
                component: () => import("@modules@/serviceInvoices/Index.vue"),
                meta: {
                    pageTitle: "serviceInvoice.title",
                    breadcrumbs: ["Service Invoices"],
                },
            },
            {
                path: "service-invoices/create/:type?/:id?",
                name: "service-invoices.create",
                component: () => import("@modules@/serviceInvoices/Create.vue"),
                meta: {
                    pageTitle: "serviceInvoice.title",
                    breadcrumbs: [
                        {
                            label: "Service Invoices",
                            route: "service-invoices.index",
                        },
                    ],
                },
            },
            {
                path: "service-invoices/edit/:id",
                name: "service-invoices.edit",
                component: () => import("@modules@/serviceInvoices/Edit.vue"),
                meta: {
                    pageTitle: "serviceInvoice.title",
                    breadcrumbs: [
                        {
                            label: "Service Invoices",
                            route: "service-invoices.index",
                        },
                    ],
                },
            },
            {
                path: "service-invoices/show/:id/:tab?",
                name: "service-invoices.show",
                component: () => import("@modules@/serviceInvoices/Show.vue"),
                meta: {
                    pageTitle: "service-invoice.title",
                    breadcrumbs: [
                        {
                            label: "Service Invoices",
                            route: "service-invoices.index",
                        },
                    ],
                },
            },
            {
                path: "purchase-invoices",
                name: "purchase-invoices.index",
                component: () => import("@modules@/purchaseInvoices/Index.vue"),
                meta: {
                    pageTitle: "purchaseInvoice.title",
                    breadcrumbs: ["Purchase Invoices"],
                },
            },
            {
                path: "purchase-invoices/create/:type?/:id?",
                name: "purchase-invoices.create",
                component: () =>
                    import("@modules@/purchaseInvoices/Create.vue"),
                meta: {
                    pageTitle: "purchaseInvoice.title",
                    breadcrumbs: [
                        {
                            label: "Purchase Invoices",
                            route: "purchase-invoices.index",
                        },
                    ],
                },
            },
            {
                path: "purchase-invoices/edit/:id",
                name: "purchase-invoices.edit",
                component: () => import("@modules@/purchaseInvoices/Edit.vue"),
                meta: {
                    pageTitle: "purchaseInvoice.title",
                    breadcrumbs: [
                        {
                            label: "Purchase Invoices",
                            route: "purchase-invoices.index",
                        },
                    ],
                },
            },
            {
                path: "purchase-invoices/show/:id/:tab?",
                name: "purchase-invoices.show",
                component: () => import("@modules@/purchaseInvoices/Show.vue"),
                meta: {
                    pageTitle: "purchaseInvoice.title",
                    breadcrumbs: [
                        {
                            label: "Purchase Invoices",
                            route: "purchase-invoices.index",
                        },
                    ],
                },
            },
            {
                path: "packages",
                name: "packages.index",
                component: () => import("@modules@/packages/Index.vue"),
                meta: {
                    pageTitle: "package.title",
                    breadcrumbs: ["Packages"],
                },
            },
            {
                path: "packages/show/:id/:tab?",
                name: "packages.show",
                component: () => import("@modules@/packages/Show.vue"),
                meta: {
                    pageTitle: "package.title",
                    breadcrumbs: [
                        { label: "Packages", route: "packages.index" },
                    ],
                },
            },
            {
                path: "packages/create/:type?/:id?",
                name: "packages.create",
                component: () => import("@modules@/packages/Create.vue"),
                meta: {
                    pageTitle: "package.title",
                    breadcrumbs: [
                        {
                            label: "Packages",
                            route: "packages.index",
                        },
                    ],
                },
            },
            {
                path: "packages/edit/:id",
                name: "packages.edit",
                component: () => import("@modules@/packages/Edit.vue"),
                meta: {
                    pageTitle: "package.title",
                    breadcrumbs: [
                        {
                            label: "Packages",
                            route: "packages.index",
                        },
                    ],
                },
            },
            {
                path: "shipments",
                name: "shipments.index",
                component: () => import("@modules@/shipments/Index.vue"),
                meta: {
                    pageTitle: "shipment.title",
                    breadcrumbs: ["Shipments"],
                },
            },
            {
                path: "shipments/show/:id/:tab?",
                name: "shipments.show",
                component: () => import("@modules@/shipments/Show.vue"),
                meta: {
                    pageTitle: "shipment.title",
                    breadcrumbs: [
                        { label: "Shipments", route: "shipments.index" },
                    ],
                },
            },
            {
                path: "payments",
                name: "payments.index",
                component: () => import("@modules@/payments/Index.vue"),
                meta: {
                    pageTitle: "payment.title",
                    breadcrumbs: ["Payments"],
                },
            },
            {
                path: "payments/show/:id/:tab?",
                name: "payments.show",
                component: () => import("@modules@/payments/Show.vue"),
                meta: {
                    pageTitle: "payment.title",
                    breadcrumbs: [
                        { label: "Payments", route: "payments.index" },
                    ],
                },
            },
            {
                path: "reports",
                name: "reports.index",
                component: () => import("@modules@/reports/Index.vue"),
                meta: {
                    pageTitle: "report.title",
                    breadcrumbs: ["Reports"],
                },
            },
            {
                path: "reports/show/:type",
                name: "reports.show",
                component: () => import("@modules@/reports/Show.vue"),
                meta: {
                    pageTitle: "report.title",
                    breadcrumbs: [
                        {
                            label: "Reports",
                            route: "reports.index",
                        },
                    ],
                },
            },
            {
                path: "subscribers",
                name: "subscribers.index",
                component: () => import("@modules@/subscribers/Index.vue"),
                meta: {
                    pageTitle: "subscriber.title",
                    breadcrumbs: ["Subscribers"],
                },
            },
            {
                path: "messages",
                name: "messages.index",
                component: () => import("@modules@/messages/Index.vue"),
                meta: {
                    pageTitle: "message.title",
                    breadcrumbs: ["Messages"],
                },
            },
            {
                path: "sales-returns",
                name: "sales-returns.index",
                component: () => import("@modules@/salesReturns/Index.vue"),
                meta: {
                    pageTitle: "salesReturn.title",
                    breadcrumbs: ["Sales Returns"],
                },
            },
            {
                path: "sales-returns/show/:id/:tab?",
                name: "sales-returns.show",
                component: () => import("@modules@/salesReturns/Show.vue"),
                meta: {
                    pageTitle: "sales-return.title",
                    breadcrumbs: [
                        {
                            label: "Sales Returns",
                            route: "sales-returns.index",
                        },
                    ],
                },
            },
            {
                path: "package-types",
                name: "package-types.index",
                component: () => import("@modules@/packageTypes/Index.vue"),
                meta: {
                    pageTitle: "packingType.title",
                    breadcrumbs: ["Package Types"],
                },
            },
            {
                path: "shipment-modes",
                name: "shipment-modes.index",
                component: () => import("@modules@/shipmentModes/Index.vue"),
                meta: {
                    pageTitle: "shipmentMode.title",
                    breadcrumbs: ["Shipment Modes"],
                },
            },
            {
                path: "contract-terms",
                name: "contract-terms.index",
                component: () => import("@modules@/contractTerms/Index.vue"),
                meta: {
                    pageTitle: "contractTerm.title",
                    breadcrumbs: ["Contract Terms"],
                },
            },
            {
                path: "contracts",
                name: "contracts.index",
                component: () => import("@modules@/contracts/Index.vue"),
                meta: {
                    pageTitle: "contract.title",
                    breadcrumbs: ["Contracts"],
                },
            },
            {
                path: "contracts/show/:id/:tab?",
                name: "contracts.show",
                component: () => import("@modules@/contracts/Show.vue"),
                meta: {
                    pageTitle: "contract.title",
                    breadcrumbs: [
                        { label: "Contracts", route: "contracts.index" },
                    ],
                },
            },
            {
                path: "contracts/create/:type?/:id?",
                name: "contracts.create",
                component: () => import("@modules@/contracts/Create.vue"),
                meta: {
                    pageTitle: "contract.title",
                    breadcrumbs: [
                        {
                            label: "Contracts",
                            route: "contracts.index",
                        },
                    ],
                },
            },
            {
                path: "contracts/edit/:id",
                name: "contracts.edit",
                component: () => import("@modules@/contracts/Edit.vue"),
                meta: {
                    pageTitle: "invoice.title",
                    breadcrumbs: [
                        {
                            label: "Contracts",
                            route: "contracts.index",
                        },
                    ],
                },
            },
            {
                path: "payment-modes",
                name: "payment-modes.index",
                component: () => import("@modules@/paymentModes/Index.vue"),
                meta: {
                    pageTitle: "paymentMode.title",
                    breadcrumbs: ["Payment Modes"],
                },
            },
            {
                path: "expense-types",
                name: "expense-types.index",
                component: () => import("@modules@/expenseTypes/Index.vue"),
                meta: {
                    pageTitle: "expenseType.title",
                    breadcrumbs: ["Expense Types"],
                },
            },
            {
                path: "expenses",
                name: "expenses.index",
                component: () => import("@modules@/expenses/Index.vue"),
                meta: {
                    pageTitle: "expense.title",
                    breadcrumbs: ["Expenses"],
                },
            },
            {
                path: "expenses/show/:id/:tab?",
                name: "expenses.show",
                component: () => import("@modules@/expenses/Show.vue"),
                meta: {
                    pageTitle: "expense.title",
                    breadcrumbs: [
                        { label: "Expenses", route: "expenses.index" },
                    ],
                },
            },
        ],
    },
    // Full-page POS route (no layout)
    {
        path: "/pos-fullscreen",
        name: "pos.fullscreen",
        component: () => import("@modules@/pos/FullPagePOS.vue"),
        meta: {
            pageTitle: "POS System",
            middleware: "auth",
        },
    },
    {
        path: "/",
        component: () => import("@/layouts/AuthLayout.vue"),
        children: [
            {
                path: "/sign-in",
                name: "sign-in",
                component: () => import("@/views/auth/SignIn.vue"),
                meta: {
                    pageTitle: "Sign In",
                },
            },
            {
                path: "/sign-up",
                name: "sign-up",
                component: () => import("@/views/auth/SignUp.vue"),
                meta: {
                    pageTitle: "Sign Up",
                },
            },
            {
                path: "/password-reset",
                name: "password-reset",
                component: () => import("@/views/auth/PasswordReset.vue"),
                meta: {
                    pageTitle: "Password reset",
                },
            },
            {
                path: "/reset-password",
                name: "reset-password",
                component: () => import("@/views/auth/ResetPassword.vue"),
                meta: {
                    pageTitle: "Reset Password",
                },
            },
        ],
    },
    {
        path: "/",
        component: () => import("@/layouts/SystemLayout.vue"),
        children: [
            {
                // the 404 route, when none of the above matches
                path: "/404",
                name: "404",
                component: () => import("@/views/auth/Error404.vue"),
                meta: {
                    pageTitle: "Error 404",
                },
            },
            {
                path: "/500",
                name: "500",
                component: () => import("@/views/auth/Error500.vue"),
                meta: {
                    pageTitle: "Error 500",
                },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: "/404",
    },
];

export { systemRoutes };
