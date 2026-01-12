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
 *  *  Last modified: 21/01/25, 5:00 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { module } from "./quotationsModule";

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
        query: { sort: "date", order: "desc", limit: 100, s: "" },
        saveState: false,
        csvRoute: `${module.id}-csv`,
        //pdfRoute: `${module.id}-pdf`,
        //defaultsRoute: `${module.id}/create`,
        tableRowClick: {
            enabled: true,
            type: "link",
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
    ],
    columns: [],
};
const route = defaultIndexState.route;
const columns = defaultIndexState.listPageConfigs.columns;
const filters = defaultIndexState.listPageConfigs.filters;

const listPageConfigs = defaultIndexState.listPageConfigs;

export default defaultIndexState;

export { route, columns, filters, listPageConfigs };
