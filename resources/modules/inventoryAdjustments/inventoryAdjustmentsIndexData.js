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
 *  *  Last modified: 05/02/25, 6:49 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./inventoryAdjustmentsModule";

const defaultIndexState = {
    route: module.id,
    module: {
        id: module.id,
        route: module.id,
        endpoint: module.id,
        slug: module.slug,
        singular: module.singular,
        plural: module.plural,
        showType: "modal",
        formType: "modal",
        formClickAction: `init-${module.slug}-form-modal`,
        permission_prefix: `${module.snakeSlug || module.slug}_`,
        query: { sort: "date", order: "desc", limit: 100, s: "" },
        saveState: false,
        csvRoute: `${module.id}-csv`,
        //pdfRoute: `${module.id}-pdf`,
        //defaultsRoute: `${module.id}/create`,
        tableRowClick: {
            enabled: true,
            type: "modal",
            action: `init-${module.slug}-show-modal`,
            actionPayloadField: "id",
        },
        import: {
            enabled: false,
            endpoint: `import/${module.id}`,
            label: `Import ${module.plural}`,
        },
    },
    listPageConfigs: {
        hasActionButtons: true,
        actionButtons: [
            {
                type: "event",
                label: `Create ${module.singular}`,
                action: `init-${module.slug}-form-modal`,
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
            value: false,
        },
        {
            outside: true,
            type: "text",
            label: "Search",
            name: "s",
            field: "s",
            value: null,
        },
    ],
    columns: [
        {
            title: `general.fields.id`,
            field: "id",
            sortable: true,
            colStyle: "width: 100px;",
        },
        {
            title: `general.fields.date`,
            field: "date",
            sortable: true,
        },
        {
            title: `general.fields.code`,
            field: "code",
            sortable: true,
        },
        {
            title: `general.fields.product`,
            field: "product.name",
            sortable: true,
        },
        {
            title: `general.fields.shelf`,
            field: "shelf.name",
            sortable: true,
        },
        {
            title: `general.fields.reason`,
            field: "reason_label",
            sortable: true,
        },
        {
            title: `general.fields.user`,
            field: "user.name",
            sortable: true,
        },
        {
            title: `general.fields.remark`,
            field: "remark",
            sortable: true,
        },
        {
            title: `general.fields.adjusted_quantity`,
            field: "adjusted_quantity",
            sortable: true,
            align: "end",
        },
        {
            title: "Actions",
            field: "title",
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
