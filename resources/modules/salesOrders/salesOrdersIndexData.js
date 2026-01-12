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
 *  *  Last modified: 16/01/25, 10:48 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./salesOrdersModule";

const defaultIndexState = {
    route: module.id,
    module: {
        id: module.id,
        route: module.id,
        endpoint: module.id,
        slug: module.slug,
        singular: module.singular,
        plural: module.plural,
        showType: "link",
        formType: "link",
        formClickAction: `init-${module.slug}-form-modal`,
        permission_prefix: `${module.snakeSlug || module.slug}_`,
        query: { sort: "so_number", order: "asc", limit: 100, s: "" },
        saveState: true,
        csvRoute: `${module.id}-csv`,
        actions: [],
        ignoreActionSeperator: false,
        disableColumnChooser: false,
        tableRowClick: {
            enabled: true,
            type: "link",
            action: `init-${module.slug}-show-modal`,
            actionPayloadField: "id",
        },
    },
    listPageConfigs: {
        hasActionButtons: true,
        actionButtons: [
            {
                type: "link",
                label: `Create ${module.singular}`,
                action: { name: `${module.id}.create` },
                actionPayload: { id: null },
            },
        ],
    },
    filters: [
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
            value: null,
        },
        {
            outside: true,
            type: "select-ajax",
            label: "Buyers",
            name: "f_buyer_id",
            field: "f_buyer_id",
            endpoint: "buyers",
            idValue: "id",
            labelValue: "display_name",
            object: false,
            value: null,
        },
    ],
    columns: [
        {
            title: `general.fields.id`,
            field: "id",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 100px;",
        },
        {
            title: `general.fields.so_number`,
            field: "so_number",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: `general.fields.date`,
            field: "date",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: `general.fields.buyer`,
            field: "buyer.display_name",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: `general.fields.subTotal`,
            field: "sub_total_text",
            thComp: "TranslatedHeader",
            sortable: true,
            align: "end",
        },
        {
            title: `general.fields.taxTotal`,
            field: "tax_total_text",
            thComp: "TranslatedHeader",
            sortable: true,
            align: "end",
        },
        {
            title: `general.fields.grandTotal`,
            field: "grand_total_text",
            thComp: "TranslatedHeader",
            sortable: true,
            align: "end",
        },
        {
            title: `general.fields.user`,
            field: "user.name",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: "Actions",
            field: "title",
            thComp: "TranslatedHeader",
            tdComp: "DatatableActions",
            isActions: true,
            sortable: true,
        },
    ],
};
const route = defaultIndexState.route;
const columns = defaultIndexState.listPageConfigs.columns;
const filters = defaultIndexState.listPageConfigs.filters;

const listPageConfigs = defaultIndexState.listPageConfigs;

export default defaultIndexState;

export { route, columns, filters, listPageConfigs };
