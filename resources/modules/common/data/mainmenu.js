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
 *  *  Last modified: 05/02/25, 4:01 pm
 *  *  Written by Chintan Bagdawala, 2025.
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
            {
                heading: "contract.title",
                route: "/contracts",
                gate: "contract_access",
                icon: "feather:file-text",
                topMenu: false,
            },
        ],
    },
    {
        topMenu: false,
        pages: [
            {
                heading: "POS System",
                route: "/pos",
                gate: "pos_access",
                icon: "feather:credit-card",
            },
            {
                sectionTitle: "inventory.title",
                route: "/inventory",
                icon: "feather:package",
                gate: "inventory_menu_access",
                sub: [
                    {
                        heading: "category.title",
                        route: "/categories",
                        gate: "category_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "product.title",
                        route: "/products",
                        gate: "product_access",
                        icon: "feather:shopping-bag",
                    },
                    {
                        heading: "inventoryAdjustment.title",
                        route: "/inventory-adjustments",
                        gate: "inventory_adjustment_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "service.title",
                        route: "/services",
                        gate: "service_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "warehouse.title",
                        route: "/warehouses",
                        gate: "warehouse_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "shelf.title",
                        route: "/shelves",
                        gate: "shelf_access",
                        icon: "feather:package",
                    },
                ],
            },
            {
                sectionTitle: "mnuContact.title",
                route: "/suppliers",
                icon: "feather:users",
                gate: "contact_menu_access",
                sub: [
                    {
                        heading: "supplier.title",
                        route: "/suppliers",
                        gate: "supplier_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "Agent",
                        route: "/agents",
                        gate: "supplier_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "buyer.title",
                        route: "/buyers",
                        gate: "buyer_access",
                        icon: "feather:package",
                    },
                ],
            },
            {
                sectionTitle: "mnuPurchase.title",
                route: "/purchases",
                icon: "feather:shopping-cart",
                gate: "purchase_menu_access",
                sub: [
                    {
                        heading: "inward.title",
                        route: "/inwards",
                        gate: "inward_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "expense.title",
                        route: "/expenses",
                        gate: "expense_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "purchaseOrder.title",
                        route: "/purchase-orders",
                        gate: "purchase_order_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "purchaseInvoice.title",
                        route: "/purchase-invoices",
                        gate: "purchase_invoice_access",
                        icon: "feather:package",
                    },
                ],
            },
            {
                sectionTitle: "mnuSale.title",
                route: "/sales",
                icon: "feather:shopping-cart",
                gate: "sales_menu_access",
                sub: [
                    {
                        heading: "quotation.title",
                        route: "/quotations",
                        gate: "quotation_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "salesOrder.title",
                        route: "/sales-orders",
                        gate: "sales_order_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "salesInvoice.title",
                        route: "/sales-invoices",
                        gate: "sales_invoice_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "serviceInvoice.title",
                        route: "/service-invoices",
                        gate: "service_invoice_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "package.title",
                        route: "/packages",
                        gate: "package_access",
                        icon: "feather:package",
                    },
                    /* {
                        heading: "package.title",
                        route: "/packages",
                        gate: "package_access",
                        icon: "feather:package",
                    }, */
                    {
                        heading: "shipment.title",
                        route: "/shipments",
                        gate: "shipment_access",
                        icon: "feather:package",
                    },

                    {
                        heading: "payment.title",
                        route: "/payments",
                        gate: "payment_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "salesReturn.title",
                        route: "/sales-returns",
                        gate: "sales_return_access",
                        icon: "feather:package",
                    },
                ],
            },
            {
                heading: "mnuReport.title",
                route: "/reports",
                icon: "feather:pie-chart",
                gate: "report_access",
            },
            {
                sectionTitle: "mnuNewsletter.title",
                route: "/messages",
                icon: "feather:pie-chart",
                gate: "newsletter_access",
                sub: [
                    {
                        heading: "message.title",
                        route: "/messages",
                        gate: "message_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "subscriber.title",
                        route: "/subscribers",
                        gate: "subscriber_access",
                        icon: "feather:package",
                    },
                ],
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
                        heading: "shipmentMode.title",
                        route: "/shipment-modes",
                        gate: "shipment_mode_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "packingType.title",
                        route: "/package-types",
                        gate: "packing_type_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "feature.title",
                        route: "/features",
                        gate: "feature_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "unit.title",
                        route: "/units",
                        gate: "unit_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "currency.title",
                        route: "/currencies",
                        gate: "currency_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "paymentMode.title",
                        route: "/payment-modes",
                        gate: "payment_mode_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "paymentTerm.title",
                        route: "/payment-terms",
                        gate: "payment_term_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "contractTerm.title",
                        route: "/contract-terms",
                        gate: "contract_term_access",
                        icon: "feather:package",
                    },
                    {
                        heading: "expenseType.title",
                        route: "/expense-types",
                        gate: "expense_type_access",
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
                /*gate: "user_access,permission_access",*/
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
