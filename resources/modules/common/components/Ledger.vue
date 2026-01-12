<template>
    <MagicDatatable
        :id="magicDatatableId"
        :table-title="tableTitle"
        card-body-padding="no_padding"
        heading="Filters"
        :filters="filters"
        :columns="columns"
        :module="module"
        pagination-position="both"
        :bulk-selection="bulkSelection"
        :save-state="false"
    />
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";
import emitter from "@/core/plugins/mitt";
import UploadService from "@/core/services/UploadService";
import {
    $catchResponse,
    $toastError,
    $toastSuccess,
} from "@/core/helpers/utility";

const props = defineProps({
    id: {
        type: Number,
        required: true,
    },
    tableTitle: {
        type: String,
        default: "Ledger",
    },
    route: {
        type: String,
        required: true,
    },
    linkRoute: {
        type: String,
        default: null,
    },
    importRoute: {
        type: String,
        default: null,
    },
    bulkSelection: {
        type: Boolean,
        default: false,
    },
    bulkDestroyRoute: {
        type: String,
        default: null,
    },
});

const route = ref(`${props.route}/${props.id}`);
const csvRoute = ref(`${props.route}-csv/${props.id}`);
const magicDatatableId = ref(`magic-table-ledger-${props.id}`);

const module = ref({
    id: `ledger-${props.id}`,
    slug: "transactions",
    route: route.value,
    csvRoute: csvRoute.value,
    query: { sort: "date", order: "asc", limit: 100, s: "" },
    import: {
        enabled: !!props.importRoute,
        endpoint: props.importRoute || null,
    },
    bulkSelection: props.bulkSelection,
    bulkDestroyRoute: props.bulkDestroyRoute,
    tableRowClick: {
        enabled: false,
    },
});

const filters = ref([
    {
        outside: true,
        type: "text",
        label: "Search",
        name: "s",
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
]);
const columns = ref([
    {
        title: `Date`,
        field: "date",
        thComp: "TranslatedHeader",
        sortable: true,
    },
    {
        title: `Description`,
        field: "description",
        thComp: "TranslatedHeader",
        tdComp: "DatatableLedgerLink",
        sortable: true,
    },
    {
        title: `Type`,
        field: "type_label",
        thComp: "TranslatedHeader",
        sortable: true,
    },
    {
        title: `Debit`,
        field: "converted_debit_label",
        thComp: "TranslatedHeader",
        sortable: true,
    },
    {
        title: `Credit`,
        field: "converted_credit_label",
        thComp: "TranslatedHeader",
        sortable: true,
    },
    {
        title: `Balance`,
        field: "converted_balance_label",
        thComp: "TranslatedHeader",
        sortable: true,
    },
]);

onMounted(() => {
    if (_.get(module, "value.import.enabled") === true) {
        emitter.on("trigger-import-data", async (data) => {
            const endpoint = _.get(module, "value.import.endpoint");
            if (!endpoint) {
                $toastError("Import endpoint was not found");
                return;
            }

            const fileInput = document.createElement("input");
            fileInput.type = "file";
            fileInput.style.display = "none";
            document.body.appendChild(fileInput);
            fileInput.click();
            fileInput.addEventListener("change", function () {
                if (!this.files.length) {
                    return;
                }
                document.body.removeChild(fileInput);
                UploadService.handleUpload(endpoint, {
                    file: this.files[0],
                })
                    .then((res) => {
                        $toastSuccess(res.data.message);
                        console.log(magicDatatableId.value);
                        emitter.emit("refresh-magic-table-data", {
                            id: magicDatatableId.value,
                        });
                    })
                    .catch((err) => {
                        $catchResponse(err);
                    })
                    .finally(() => {
                        //fileInput.removeEventListener("change");
                    });
            });
        });
    }
});

onBeforeUnmount(() => {
    emitter.off(`trigger-import-data`);
});
</script>
