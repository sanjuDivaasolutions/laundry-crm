<!--
  - /*
  -  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 17/12/24, 11:42 am
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div
        class="table-responsive"
        :class="{
            'sticky-table': stickyHeader,
            'table-loading': loading && initialLoad,
        }"
    >
        <div class="table-overlay-bg" v-if="loading && initialLoad"></div>
        <div
            v-if="loading && initialLoad"
            class="table-loading-message text-primary"
        >
            Please wait...
        </div>
        <table
            class="table align-middle table-row-bordered table-row-solid mb-0 gy-1 gs-9"
            :class="{ 'table-hover': hasRowClick && !loading }"
        >
            <thead class="border-gray-200 fs-5 fw-bold">
                <tr>
                    <th v-if="bulkSelection" class="w-15px">
                        <div class="form-check">
                            <input
                                class="form-check-input form-check-sm"
                                type="checkbox"
                                @click="selectAllToggle"
                            />
                        </div>
                    </th>
                    <th
                        v-for="c in filteredColumns"
                        :class="[
                            c.isActions ? 'action-column' : '',
                            c.align ? 'text-' + c.align : '',
                        ]"
                    >
                        <span v-if="!c.thComp">{{ $t(c.title) }}</span>
                        <component
                            v-if="c.thComp"
                            :is="c.thComp"
                            :title="c.title"
                        />
                        <i
                            v-if="c.sortable && !c.isActions"
                            class="cursor-pointer ms-2"
                            :class="getSortIcon(c)"
                            @click="sortColumn(c.field)"
                        ></i>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-if="data.length > 0"
                    v-for="s in data"
                    :class="{ 'cursor-pointer': hasRowClick && !loading }"
                >
                    <th v-if="bulkSelection">
                        <div class="form-check">
                            <input
                                class="form-check-input form-check-sm"
                                type="checkbox"
                                :value="s.id"
                                :checked="selection.indexOf(s.id) !== -1"
                                @click="singleSelectionChange(s.id)"
                            />
                        </div>
                    </th>
                    <td
                        v-for="c in filteredColumns"
                        :class="[
                            c.isActions ? 'action-column' : '',
                            c.align ? 'text-' + c.align : '',
                        ]"
                        @click="rowClick(s, c.isActions)"
                        :style="c.colStyle || {}"
                    >
                        <span v-if="!c.tdComp && !c.route">{{
                            $getDisplayValue(s, c.field, defaultDisplayValue)
                        }}</span>
                        <span v-if="!c.tdComp && c.route">
                            <router-link
                                :to="getRoute(c.route, s)"
                                class="text-primary fw-bold text-hover-primary"
                                :target="c.route.target || '_self'"
                            >
                                {{
                                    $getDisplayValue(
                                        s,
                                        c.field,
                                        defaultDisplayValue
                                    )
                                }}
                            </router-link>
                        </span>
                        <component
                            v-if="c.tdComp"
                            :is="c.tdComp"
                            :row="s"
                            :value="s[c.field]"
                            :field="c.field"
                            :xprops="module"
                        />
                    </td>
                </tr>
                <tr v-if="data.length === 0 && initialLoad">
                    <td :colspan="columns.length" class="text-center">
                        {{ $t("general.fields.noTableResults") }}
                    </td>
                </tr>
                <tr v-if="data.length === 0 && !initialLoad">
                    <td :colspan="columns.length" class="text-center">
                        {{ $t("general.fields.tableLoading") }}
                    </td>
                </tr>
            </tbody>
            <tfoot v-if="hasSummary">
                <tr>
                    <th v-if="bulkSelection">
                        <span>&nbsp;</span>
                    </th>
                    <th
                        v-for="c in filteredColumns"
                        class="fw-bold"
                        :class="[
                            c.isActions ? 'action-column' : '',
                            c.align ? 'text-' + c.align : '',
                        ]"
                    >
                        <span>{{
                            summary[c.field]
                                ? $getDisplayValue(summary, c.field)
                                : ""
                        }}</span>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import emitter from "@/core/plugins/mitt";
import { $getDisplayValue } from "@/core/helpers/utility";

const props = defineProps({
    data: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => {},
    },
    columns: {
        type: Array,
        default: () => [],
    },
    selection: {
        type: Array,
        default: () => [],
    },
    module: {
        type: Object,
        default: () => {
            return {
                route: "",
                query: {},
            };
        },
    },
    query: {
        type: Object,
        default: () => {},
    },
    initialLoad: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    stickyHeader: {
        type: Boolean,
        default: true,
    },
    bulkSelection: {
        type: Boolean,
        default: false,
    },
    defaultDisplayValue: {
        type: String,
        default: "-",
    },
});

const emit = defineEmits(["bulk-selection-change", "sort"]);

const router = useRouter();

const hasRowClick = computed(() => {
    return _.get(props, "module.tableRowClick.enabled", false);
});

const hasSummary = computed(() => {
    return Object.keys(props.summary).length > 0;
});

const filteredColumns = computed(() => {
    return props.columns.filter((c) => {
        return c.visible !== false;
    });
});

const rowClick = (row, isAction) => {
    if (hasRowClick.value && !isAction) {
        if (!row.id || !props.module.route) {
            return false;
        }
        const module = props.module;
        const idField = module.tableRowClick.actionPayloadField || "id";
        if (
            module.tableRowClick.type === "event" ||
            module.tableRowClick.type === "modal"
        ) {
            emitter.emit(module.tableRowClick.action, {
                [idField]: row[idField],
                row: row,
            });
        } else {
            router.push({
                name: `${module.route}.show`,
                params: { id: row[idField] },
            });
        }
    }
};

const getRoute = (route, row) => {
    const params = {};
    const idField = route.idField || "id";
    const value = _.get(row, route.field, null);
    if (!value || !idField) {
        return {};
    }
    params[idField] = value;
    return {
        name: route.name,
        params: params,
    };
};

const selectAllToggle = (e) => {
    const checked = e.target.checked;
    const ids = props.data.map((d) => d.id);
    const selection = checked ? ids : [];
    emit("bulk-selection-change", selection);
};

const singleSelectionChange = (id) => {
    const selection = props.selection;
    const index = selection.indexOf(id);
    if (index === -1) {
        selection.push(id);
    } else {
        selection.splice(index, 1);
    }
    emit("bulk-selection-change", selection);
};

const sortColumn = (field) => {
    const currentSort = _.get(props.query, "sort", null);
    const currentOrder = _.get(props.query, "order", null);
    const isSameField = currentSort === field;
    const order = isSameField && currentOrder === "asc" ? "desc" : "asc";
    emit("sort", {
        sort: field,
        order: order,
    });
};

const getSortIcon = (column) => {
    const isCurrentSort = _.get(props.query, "sort", null) === column.field;
    if (column.sortable && isCurrentSort) {
        const order = _.get(props.query, "order", null);
        if (order) {
            return order === "asc"
                ? "fa fa-sort-up text-danger"
                : "fa fa-sort-down text-danger";
        }
    }
    return "fa fa-sort";
};
</script>

<style scoped lang="scss">
.action-column {
    width: 2%;
    text-align: right;
}
.sticky-table {
    overflow-y: auto;
    max-height: 500px;
}
.sticky-table thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    background-color: var(--kt-card-bg);
}
.sticky-table tfoot th {
    position: sticky;
    bottom: 0;
    z-index: 20;
    background-color: var(--kt-card-bg);
}
.table-overlay-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #eee;
    opacity: 0.3;
    z-index: 9998;
}
.table-loading-message {
    z-index: 9999;
}

[data-theme="light"] {
    table thead th,
    table tfoot th {
        background-color: #f8f8f8 !important;
    }
    table tfoot th {
        color: var(--kt-danger-active);
    }
}
</style>
