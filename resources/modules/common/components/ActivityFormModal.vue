<template>
    <FormModal
        :id="`${id}-form-modal`"
        :route="activityRoute"
        :fields="computedFormFields"
        :title="formModalData.title"
        :mode="formMode"
        :border="true"
        :narrow-padding="true"
        @cancel="onCancel"
    >
        <template #modal-after-body>
            <div class="mt-10 border border-light rounded">
                <MagicDatatable
                    v-if="componentInitialized"
                    :module="datatableModule"
                    :columns="computedColumns"
                    :disable-actions="true"
                />
            </div>
        </template>
    </FormModal>
</template>

<script setup>
import FormModal from "@common@/modals/FormModal.vue";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import emitter from "@/core/plugins/mitt";
import ApiService from "@/core/services/ApiService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    route: {
        type: String,
        required: true,
    },
    statusIdField: {
        type: String,
        required: "value",
    },
    statusLabelField: {
        type: String,
        required: "label",
    },
    statusEndpoint: {
        type: String,
        required: true,
    },
    statusEndpointFilterParams: {
        type: Object,
        default: () => [],
    },
    parentField: {
        type: String,
        required: true,
    },
    formFields: {
        type: Array,
        default: () => [],
    },
    listFields: {
        type: Array,
        default: () => [],
    },
});

const componentInitialized = ref(false);

const activityRoute = ref(null);
const parentId = ref(null);

const fields = ref([
    {
        name: "status",
        label: `general.fields.status`,
        field: "status",
        placeholder: "Select Status",
        idValue: props.statusIdField,
        labelValue: props.statusLabelField,
        type: "select-single",
        endpoint: props.statusEndpoint,
        endpointFilterParams: props.statusEndpointFilterParams,
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
const columns = [
    {
        title: `general.fields.date`,
        field: "date",
        thComp: "TranslatedHeader",
        sortable: true,
    },
    {
        title: `general.fields.status`,
        field: "status.fir_activity_master.name",
        thComp: "TranslatedHeader",
        sortable: true,
    },
    {
        title: `general.fields.user`,
        field: "user.name",
        thComp: "TranslatedHeader",
        sortable: true,
    },
    {
        title: `general.fields.remark`,
        field: "remark",
        thComp: "TranslatedHeader",
        /*tdComp: "DatatableLink",*/
        sortable: true,
    },
    /*{
        title: "Actions",
        field: "title",
        thComp: "TranslatedHeader",
        tdComp: "DatatableActions",
        isActions: true,
        sortable: false,
    },*/
];

const computedFormFields = computed(() => {
    return props.formFields.length ? props.formFields : fields.value;
});

const computedColumns = computed(() => {
    return props.listFields.length ? props.listFields : columns;
});

const datatableModule = ref({});

const formModalData = ref({
    id: `${props.id}-form-modal`,
    size: "modal-lg",
    route: null,
    title: "Manage Activity",
});
const formMode = ref("create");
const formModalTitle = ref("New Activity");

const formEntry = ref({
    id: null,
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
        //e = await fetchEditData(id);
        //await moduleFormStore.loadEditData(id);
    } else {
        formModalTitle.value = "New Activity";
        e = _.cloneDeep(formEntry.value);
        setParentIds(e);
    }
    componentInitialized.value = true;
    emitter.emit("show-form-modal", {
        id: formModalData.value.id,
        entry: e,
    });
};

const setParentIds = (obj) => {
    obj[props.parentField] = parentId.value;
};

const onCancel = () => {
    componentInitialized.value = false;
    datatableModule.value = {};
};

onBeforeUnmount(() => {
    emitter.off(`init-activity-modal-${props.id}`);
    emitter.off("perform-item-delete");
});

onMounted(() => {
    emitter.on(`init-activity-modal-${props.id}`, (row) => {
        parentId.value = row.id;
        activityRoute.value = `${props.route}/${parentId.value}`;
        formModalData.value.route = activityRoute.value;
        datatableModule.value = {
            id: `${props.id}-datatable`,
            slug: `${props.id}-datatable`,
            name: `${props.id}-datatable`,
            route: activityRoute.value,
            query: { sort: "date", order: "desc", limit: 100, s: "" },
        };
        initActivityModal();
    });
    emitter.on("perform-item-delete", async (id) => {
        ApiService.delete(`${props.route}/${id}`)
            .then((res) => {
                $toastSuccess("Activity was deleted successfully.");
                emitter.emit("refresh-magic-table-data", {
                    id: datatableModule.value.id,
                });
            })
            .catch((err) => {
                $catchResponse(err);
            });
    });
});
</script>

<style scoped></style>
