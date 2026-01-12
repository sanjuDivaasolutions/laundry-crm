<template>
    <div>
        <CardContainer :hide-header="true" :hide-footer="true" class="mb-10">
            <template #body>
                <div class="d-flex justify-content-end">
                    <a
                        href="#"
                        class="btn btn-primary btn-sm"
                        @click.prevent="initActivityModal(null)"
                        ><FormIcon icon="feather:plus" />New Activity</a
                    >
                </div>
            </template>
        </CardContainer>
        <magic-datatable
            v-if="true"
            :module="module"
            :columns="columns"
            table-title="Activities"
        />
        <FormModal
            :id="formModalData.id"
            :title="formModalTitle"
            :fields="formFields"
            :mode="formMode"
            :size="formModalData.size"
            :route="formModalData.route"
        >
            <template #modal-body>
                <slot name="form-modal-body"></slot>
            </template>
        </FormModal>
    </div>
</template>

<script setup>
import { onBeforeMount, onMounted, ref } from "vue";
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";
import CardContainer from "@common@/components/CardContainer.vue";
import FormModal from "@common@/modals/FormModal.vue";
import emitter from "@/core/plugins/mitt";
import ApiService from "@/core/services/ApiService";
import { useRoute } from "vue-router";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";

const route = useRoute();

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
    type: {
        type: String,
        required: true,
    },
});

const module = ref({
    slug: "salesOrderActivity",
    name: "Activity",
    route: "sales-orders-activities",
    routeOriginal: "sales-orders-activities",
    permission: "activity",
    permission_prefix: `sales_order_activity_`,
    query: { sort: "date", order: "desc", limit: 100, s: "" },
    tableRowClick: {
        enabled: false,
    },
});

const formFields = ref([
    {
        name: "status",
        label: `general.fields.status`,
        field: "status",
        placeholder: "Select Status",
        idValue: "value",
        labelValue: "label",
        type: "select-single",
        endpoint: "sales-order-statuses",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "12",
    },
    {
        name: "remark",
        label: `general.fields.remark`,
        field: "remark",
        placeholder: `Enter Remark`,
        type: "text",
        required: false,
        column: "12",
    },
]);

const data = ref([]);
const meta = ref({});

const formModalData = ref({
    id: "so-activity-form-modal",
    size: "modal-lg",
    route: null,
});
const formMode = ref("create");
const formModalTitle = ref("New Activity");

const columns = [
    {
        title: `general.fields.date`,
        field: "date",
        thComp: "TranslatedHeader",
        /*tdComp: "DatatableLink",*/
        sortable: true,
    },
    {
        title: `general.fields.status`,
        field: "status_label",
        thComp: "TranslatedHeader",
        /*tdComp: "DatatableLink",*/
        sortable: true,
    },
    {
        title: `general.fields.user`,
        field: "user",
        thComp: "TranslatedHeader",
        /*tdComp: "DatatableLink",*/
        sortable: true,
    },
    {
        title: `general.fields.remark`,
        field: "remark",
        thComp: "TranslatedHeader",
        /*tdComp: "DatatableLink",*/
        sortable: true,
    },
    {
        title: "Actions",
        field: "title",
        thComp: "TranslatedHeader",
        tdComp: "DatatableActions",
        isActions: true,
        sortable: false,
    },
];

const activityEntry = ref({
    id: null,
    sales_order_id: null,
    date: null,
    status: null,
    remark: null,
    user_id: null,
});

const initActivityModal = async (id = null) => {
    formMode.value = id ? "edit" : "create";
    let e = null;
    if (formMode.value === "edit") {
        formModalTitle.value = "Edit Activity";
        e = await fetchEditData(id);
        //await moduleFormStore.loadEditData(id);
    } else {
        formModalTitle.value = "New Activity";
        e = _.cloneDeep(activityEntry.value);
        setParentIds(e);
    }
    emitter.emit("show-form-modal", {
        id: formModalData.value.id,
        entry: e,
    });
};

const setParentIds = (obj) => {
    obj.sales_order_id = props.entry.id;
};

const fetchEditData = async (id) => {
    return await ApiService.get(`${module.value.route}/${id}/edit`);
};

onBeforeMount(() => {
    module.value.route = `${module.value.route}/${route.params.id}`;
    formModalData.value.route = `${module.value.route}`;
});

onMounted(() => {
    emitter.on("perform-item-delete", async (id) => {
        ApiService.delete(`${module.value.routeOriginal}/${id}`)
            .then((res) => {
                $toastSuccess("Activity was deleted successfully.");
                emitter.emit("refresh-magic-table-data");
            })
            .catch((err) => {
                $catchResponse(err);
            });
    });
});
</script>

<style scoped>
.table th {
    font-weight: bold;
}
</style>
