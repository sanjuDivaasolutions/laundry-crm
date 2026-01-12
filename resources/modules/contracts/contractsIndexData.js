/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 17/10/24, 6:56 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

import { module } from "./contractsModule";

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
        query: { sort: "date", order: "asc", limit: 100, s: "" },
        saveState: false,
        csvRoute: `${module.id}-csv`,
        //pdfRoute: `${module.id}-pdf`,
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
            type: "text",
            label: "Search",
            name: "s",
            field: "s",
            value: null,
        },
    ],
    columns: [
        /*{
            title: `general.fields.id`,
            field: "id",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 100px;",
        },*/
        {
            title: `general.fields.buyer`,
            field: "buyer.name",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: `general.fields.contract_type`,
            field: "contract_type.label",
            thComp: "TranslatedHeader",
            sortable: true,
            colStyle: "width: 100px;",
        },
        {
            title: `general.fields.code`,
            field: "code",
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
            title: `general.fields.start_date`,
            field: "start_date",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: `general.fields.end_date`,
            field: "end_date",
            thComp: "TranslatedHeader",
            sortable: true,
        },
        {
            title: `general.fields.amount`,
            field: "amount_label",
            thComp: "TranslatedHeader",
            sortable: true,
            align: "end",
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
