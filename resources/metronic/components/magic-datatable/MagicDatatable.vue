<!--
  - /*
  -  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
  -  *
  -  *  This software is the confidential and proprietary information of Divaa Solutions
  -  *  ("Confidential Information"). You shall not disclose such Confidential Information and
  -  *  shall use it only in accordance with the terms of the license agreement you entered into
  -  *  with Divaa Solutions.
  -  *
  -  *  Unauthorized copying of this file, via any medium is strictly prohibited.
  -  *  Proprietary and confidential.
  -  *
  -  *  Last modified: 07/01/25, 4:21 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <CardContainer
        :body-padding="cardBodyPadding"
        :footer-padding="cardFooterPadding"
        :header-padding="cardHeaderPadding"
        :hide-header="!tableTitle && filters.length === 0"
    >
        <template #header>
            <div class="card-title">&nbsp;</div>
            <div class="card-toolbar m-0">
                <div v-if="bulkActions.length">
                    <button
                        :disabled="!selected.length"
                        class="btn btn-danger me-2 d-flex align-items-center"
                        data-kt-menu-flip="top-end"
                        data-kt-menu-placement="bottom-end"
                        data-kt-menu-trigger="hover"
                    >
                        <FormIcon class="me-2" icon="feather:check-square" />
                        Bulk Actions
                    </button>
                    <ConfigDropdown :actions="bulkActions" />
                </div>
                <GlobalFilter
                    v-if="filters.length > 0"
                    :id="module.id"
                    :defaultQuery="defaultQuery"
                    :filters="globalFilters"
                    :query="query"
                    loading="loading"
                    @filter-submit="onFilterSubmit"
                />
                <button
                    v-if="configActions.length"
                    class="btn btn-sm btn-icon btn-primary ms-2"
                    data-kt-menu-flip="top-end"
                    data-kt-menu-placement="bottom-end"
                    data-kt-menu-trigger="hover"
                >
                    <FormIcon :noMargin="true" icon="feather:settings" />
                </button>
                <ConfigDropdown :actions="configActions" />
                <ColumnChooser
                    :columns="tableColumns"
                    @save="handleUpdateColumns"
                />
            </div>
        </template>
        <template #body>
            <div
                v-if="
                    paginationPosition === 'top' ||
                    paginationPosition === 'both'
                "
                class="py-3 px-8"
            >
                <pagination
                    :data="data"
                    :loading="loading"
                    :meta="meta"
                    :query="query"
                    @update="paginationUpdate"
                    @on-page-change="
                        () => {
                            console.log('page changed');
                        }
                    "
                ></pagination>
            </div>
            <Datatable
                :bulk-selection="bulkSelection"
                :columns="tableColumns"
                :data="data"
                :initial-load="initialLoad"
                :loading="loading"
                :module="module"
                :query="query"
                :selection="selected"
                :summary="summary"
                @sort="updateSort"
                @bulk-selection-change="updateBulkSelection"
            ></Datatable>
        </template>
        <template #footer>
            <pagination
                v-if="
                    paginationPosition === 'bottom' ||
                    paginationPosition === 'both'
                "
                :data="data"
                :meta="meta"
                :query="query"
                @update="paginationUpdate"
                @on-page-change="
                    () => {
                        console.log('page changed');
                    }
                "
            ></pagination>
        </template>
    </CardContainer>
</template>

<script setup>
import {
    computed,
    onBeforeMount,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";
import fetchData from "@utility@/store/indexStoreHelper";
import CardContainer from "@modules@/common/components/CardContainer.vue";
import Datatable from "@/components/magic-datatable/Datatable/Datatable.vue";
import Pagination from "@/components/magic-datatable/Datatable/Pagination.vue";
import GlobalFilter from "@/components/magic-datatable/filter-plugin/GlobalFilter.vue";
import ConfigDropdown from "@/components/magic-datatable/components/ConfigDropdown.vue";
import emitter from "@/core/plugins/mitt";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import FormIcon from "@common@/components/form/FormIcon.vue";
import ColumnChooser from "@/components/magic-datatable/components/ColumnChooser.vue";
import DownloadService from "@/core/services/DownloadService";
import i18n from "@/core/plugins/i18n";
import ApiService from "@/core/services/ApiService";
import Swal from "sweetalert2";
import { LS_APP_NAME_KEY } from "@/stores/config";

const props = defineProps({
    tableTitle: {
        type: String,
        required: true,
        default: "",
    },
    columns: {
        type: Array,
        required: true,
        default: () => [],
    },
    module: {
        type: Object,
        required: true,
        default: () => {
            return {
                route: "",
                query: {},
            };
        },
    },
    filters: {
        type: Array,
        default: () => [],
    },
    cardBodyPadding: {
        type: String,
        default: "normal",
        validator: (value) => {
            const match = ["normal", "no_padding"];
            if (match.indexOf(value) === -1) {
                console.warn(
                    `Body Padding: invalid "${value}" not allowed. Allowed values are: ${match.join(
                        ", "
                    )}`
                );
            }

            return true;
        },
    },
    cardHeaderPadding: {
        type: String,
        default: "normal",
        validator: (value) => {
            const match = ["normal", "no_padding"];
            if (match.indexOf(value) === -1) {
                console.warn(
                    `Header Padding: invalid "${value}" not allowed. Allowed values are: ${match.join(
                        ", "
                    )}`
                );
            }

            return true;
        },
    },
    cardFooterPadding: {
        type: String,
        default: "normal",
        validator: (value) => {
            const match = ["normal", "no_padding"];
            if (match.indexOf(value) === -1) {
                console.warn(
                    `Footer Padding: invalid "${value}" not allowed. Allowed values are: ${match.join(
                        ", "
                    )}`
                );
            }

            return true;
        },
    },
    paginationPosition: {
        type: String,
        default: "bottom",
        validator: (value) => {
            const match = ["top", "bottom", "both"];
            if (match.indexOf(value) === -1) {
                console.warn(
                    `Pagination Position: invalid "${value}" not allowed. Allowed values are: ${match.join(
                        ", "
                    )}`
                );
            }

            return true;
        },
    },
    saveState: {
        type: Boolean,
        default: false,
    },
    disableServerColumns: {
        type: Boolean,
        default: false,
    },
    disableActions: {
        type: Boolean,
        default: false,
    },
    bulkSelection: {
        type: Boolean,
        default: false,
    },
    disableBulkRemove: {
        type: Boolean,
        default: false,
    },
    bulkActions: {
        type: Array,
        default: () => [],
    },
});

const initialLoad = ref(false);
const loading = ref(false);
const data = ref([]);
const summary = ref([]);
const meta = ref({
    current_page: null,
    from: null,
    last_page: null,
    path: null,
    links: [],
    per_page: null,
    to: null,
    total: null,
});
const query = ref({});

const tableColumns = ref([]);
const serverColumns = ref([]);
const selected = ref([]);

const globalFilters = computed(() => {
    return props.filters.map((filter) => {
        return {
            ...filter,
            object: false,
        };
    });
});

const updateBulkSelection = (selection) => {
    if (!props.bulkSelection) return false;
    selected.value = selection;
};

const bulkRemove = () => {
    const route = _.get(
        props.module,
        "bulkDestroyRoute",
        `${props.module.route}-bulk-destroy`
    );

    Swal.fire({
        title: `Are you sure to remove ${selected.value.length} items?`,
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        confirmButtonColor: "#dd4b39",
        focusCancel: true,
        reverseButtons: true,
    }).then((r) => {
        if (!r.isConfirmed) return;

        ApiService.post(route, {
            ids: selected.value,
        })
            .then((res) => {
                fetchTableData(query.value);
                selected.value = [];
                $toastSuccess(res.data.data);
            })
            .catch((err) => {
                $catchResponse(err);
            });
    });
};

watch(query, (newQuery) => {
    fetchTableData(newQuery);
});

onBeforeMount(() => {
    keys.value.query = `gf-query-${props.module.id}`;
    keys.value.columns = `mdt-columns-${props.module.id}`;
    setQuery(props.module.query);
});

const fetchTableData = (q) => {
    let query = prepQuery(_.cloneDeep(q));
    if (!query) query = {};
    loading.value = true;
    const { total, from, to } = meta.value;
    fetchData(props.module.route, {
        loading,
        data,
        query,
        total,
        from,
        to,
    })
        .then((res) => {
            data.value = res.data.data;
            meta.value = res.data.meta;
            summary.value = res.data.summary || {};
            serverColumns.value = res.data.columns || [];
            if (!props.disableServerColumns) {
                tableColumns.value = getTableColumns();
            }
        })
        .catch((err) => {
            $catchResponse(err);
        })
        .finally(() => {
            initialLoad.value = true;
            loading.value = false;
        });
};

const keys = ref({ query: null, columns: null });
const event = ref({
    openAdvanceFilterModal: "open-advance-filter-modal",
    openColumnChooserModal: "open-column-chooser-modal",
    downloadCsv: `${props.module.id}-download-csv`,
    downloadPdf: `${props.module.id}-download-pdf`,
});

const getProjectKey = (key) => {
    const appName = LS_APP_NAME_KEY;
    return `${appName}-${key}`;
};

const setLocalStorage = (obj, key, force = false) => {
    const projectKey = getProjectKey(key);
    if (localStorage.getItem(projectKey) === null || force) {
        localStorage.setItem(projectKey, JSON.stringify(obj));
    }
};

const getLocalStorage = (key) => {
    const projectKey = getProjectKey(key);
    return JSON.parse(localStorage.getItem(projectKey));
};
const unsetLocalStorage = (key) => {
    const projectKey = getProjectKey(key);
    localStorage.removeItem(projectKey);
};

const paginationUpdate = (q) => {
    updateQuery(q);
};

const onFilterSubmit = (q) => {
    updateQuery(q);
};

const refreshData = (e, hard = false) => {
    if (!e) {
        console.error("MagicDatatable: invalid refresh event");
        return false;
    }
    if (e.id !== `magic-table-${props.module.id}`) {
        return false;
    }
    const q = hard ? defaultQuery.value : query.value;
    updateQuery(q, true);
};

const updateQuery = (q, clone = false) => {
    query.value = clone ? _.cloneDeep(q) : q;
    if (props.filters.length) {
        props.filters.forEach((f) => {
            if (!query.value.hasOwnProperty(f.name)) {
                query.value[f.name] = f.value;
            }
        });
    }
    if (props.saveState) {
        const key = keys.value.query;
        setLocalStorage(query.value, key, true);
    }
};

const setQuery = (obj) => {
    const key = keys.value.query;
    if (props.saveState) {
        let updateObj = _.cloneDeep(obj);
        const q = getLocalStorage(key);
        if (q) {
            updateObj = _.cloneDeep(q);
        }
        updateQuery(updateObj, true);
    } else {
        updateQuery(obj, true);
        unsetLocalStorage(key);
    }
};

const bulkActions = computed(() => {
    const propActions = _.get(props, "bulkActions", []);
    const actions = [];
    if (!props.bulkSelection) return actions;
    if (propActions.length) {
        propActions.forEach((a) => {
            const action = _.cloneDeep(a);
            action.childEvent = a.link;
            action.link = "bulk-action-event";
            actions.push(action);
        });
    }
    if (!props.disableBulkRemove) {
        actions.push({
            label: "Remove",
            icon: "feather:trash",
            type: "event",
            displayType: "link",
            link: "bulk-remove",
            childEvent: "bulk-remove",
        });
    }
    return actions;
});

const configActions = computed(() => {
    const actions = [];
    if (props.disableActions) return actions;
    const globalFiltersInside = globalFilters.value.filter(
        (f) => f.outside === false
    );
    if (globalFiltersInside.length > 0) {
        actions.push({
            label: "Advance Filters",
            icon: "feather:filter",
            type: "event",
            displayType: "link",
            link: event.value.openAdvanceFilterModal,
        });
    }
    if (props.module.disableColumnChooser !== true) {
        actions.push({
            label: "Column Chooser",
            icon: "feather:columns",
            type: "event",
            displayType: "link",
            link: event.value.openColumnChooserModal,
        });
    }
    if (props.module.csvRoute) {
        actions.push({
            label: "CSV Export",
            icon: "feather:download",
            type: "event",
            displayType: "link",
            link: event.value.downloadCsv,
        });
    }
    if (props.module.pdfRoute) {
        actions.push({
            label: "PDF Export",
            icon: "feather:download",
            type: "event",
            displayType: "link",
            link: event.value.downloadPdf,
        });
    }
    if (props.module.import) {
        if (props.module.import.enabled) {
            actions.push({
                label: props.module.import.label || "Import",
                icon: "feather:upload",
                type: "event",
                displayType: "link",
                link: "trigger-import-data",
            });
        }
    }
    if (props.module.saveState) {
        actions.push({
            label: "Clear Cache",
            icon: "feather:x-circle",
            type: "event",
            displayType: "link",
            link: "clear-magic-table-cache",
        });
    }
    if (props.module.actions && props.module.actions.length > 0) {
        if (props.module.ignoreActionSeperator !== true) {
            actions.push({
                label: "",
                icon: "",
                type: "event",
                displayType: "separator",
                link: "",
            });
        }
        props.module.actions.forEach((a) => {
            actions.push(a);
        });
    }
    return actions;
});

const clearCache = () => {
    const key = keys.value.columns;
    const key2 = keys.value.query;
    unsetLocalStorage(key);
    unsetLocalStorage(key2);
    window.location.reload();
};

const handleUpdateColumns = (columns) => {
    tableColumns.value = columns;
    if (!props.saveState) return false;
    const key = keys.value.columns;
    setLocalStorage(columns, key, true);
};

const updateSort = (payload) => {
    const q = _.cloneDeep(query.value);
    q.sort = payload.sort;
    q.order = payload.order;
    updateQuery(q);
};

const getTableColumns = () => {
    const key = keys.value.columns;
    const existing = getLocalStorage(key);
    if (!props.disableServerColumns && serverColumns.value.length > 0) {
        return existing && existing.length > 0
            ? getVisibleColumns(existing, serverColumns.value)
            : serverColumns.value;
    }
    if (existing) {
        return existing;
    }
    const columns = props.columns.map((c) => {
        return {
            ...c,
            visible: c.visible !== false,
        };
    });
    if (props.saveState) {
        setLocalStorage(columns, key);
    }
    return columns;
};

const getVisibleColumns = (existing, columns) => {
    const visibleColumns = [];
    existing.forEach((c) => {
        const col = columns.find((col) => col.field === c.field);
        if (col) {
            visibleColumns.push({
                ...col,
                visible: c.visible,
            });
        }
    });
    return visibleColumns;
};

const defaultQuery = ref({});

const handleDownloadCsv = () => {
    if (!props.module.csvRoute) {
        return false;
    }
    loading.value = true;
    const q = prepQuery(_.cloneDeep(query.value));
    const columns = [];
    tableColumns.value.forEach((c) => {
        if (c.isActions || c.visible === false) return;
        columns.push({
            f: c.downloadField || c.field,
            l: i18n.global.t(c.title),
            a: c.align || "left",
        });
    });
    q.page = 1;
    q.offset = 0;
    q.limit = 100000;
    q.columns = JSON.stringify(columns);
    DownloadService.handleDownload(props.module.csvRoute, { params: q })
        .catch((err) => {
            $catchResponse(err);
        })
        .finally(() => {
            loading.value = false;
        });
};

const prepQuery = (q) => {
    const query = _.cloneDeep(q);
    Object.keys(query).forEach((key) => {
        if (Array.isArray(query[key])) {
            query[key] = query[key].join(",");
        }
    });
    return query;
};

const handleDownloadPdf = () => {
    if (!props.module.pdfRoute) {
        return false;
    }
    loading.value = true;
    const q = prepQuery(_.cloneDeep(query.value));
    const columns = [];
    tableColumns.value.forEach((c) => {
        if (c.isActions || c.visible === false) return;
        columns.push({
            f: c.downloadField || c.field,
            l: i18n.global.t(c.title),
            a: c.align || "left",
        });
    });
    q.page = 1;
    q.offset = 0;
    q.limit = 100000;
    q.columns = JSON.stringify(columns);
    DownloadService.handleDownload(props.module.pdfRoute, { params: q })
        .catch((err) => {
            $catchResponse(err);
        })
        .finally(() => {
            loading.value = false;
        });
};

onMounted(() => {
    if (props.module.id === null) {
        console.error("MagicDatatable: id is required");
        props.module.id =
            "magic-datatable-" + Math.floor(Math.random() * 1000000);
    }
    tableColumns.value = getTableColumns();
    defaultQuery.value = _.cloneDeep(props.module.query);
    emitter.on("refresh-magic-table-data", refreshData);
    emitter.on("clear-magic-table-cache", clearCache);
    emitter.on("bulk-remove", bulkRemove);
    emitter.on("bulk-action-event", (payload) => {
        emitter.emit(payload.childEvent, {
            action: payload,
            selected: selected.value,
        });
    });
    emitter.on(event.value.downloadCsv, handleDownloadCsv);
    emitter.on(event.value.downloadPdf, handleDownloadPdf);

    emitter.on("user-company-changed", () => {
        fetchTableData(query.value);
    });
});
onBeforeUnmount(() => {
    emitter.off("refresh-magic-table-data");
    emitter.off("clear-magic-table-cache");
    emitter.off("bulk-action-event");
    emitter.off("bulk-remove");
    emitter.off(event.value.downloadCsv);
    emitter.off(event.value.downloadPdf);
    emitter.off("user-company-changed");
});
</script>

<style scoped></style>
