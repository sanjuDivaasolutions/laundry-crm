<template>
    <FormModal
        :id="`${id}-form-modal`"
        :route="paymentRoute"
        :fields="computedFormFields"
        :title="formModalData.title"
        :mode="formMode"
        :update-submit-event="`${id}-update-Payment`"
        :border="true"
        :narrow-padding="true"
        :centered="false"
        :allow-new-entry="formModalAllowNewEntry"
        @cancel="onCancel"
        @success="onSuccess"
    >
        <template #modal-after-body>
            <div class="mt-10 border border-light rounded">
                <MagicDatatable
                    v-if="componentInitialized"
                    :module="datatableModule"
                    :columns="computedColumns"
                    :disable-actions="true"
                    pagination-position="none"
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
import Swal from "sweetalert2";

const emit = defineEmits(["refresh"]);

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    listRoute: {
        type: String,
        required: true,
    },
    formRoute: {
        type: String,
        required: true,
    },
    parentField: {
        type: String,
        required: true,
    },
    formEntry: {
        type: Object,
        default: () => {},
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

const paymentRoute = ref(null);
const parentId = ref(null);

const columns = [];

const computedFormFields = computed(() => {
    if (!componentInitialized.value) return [];
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
    title: "Manage Payments",
});
const formMode = ref("create");
const formModalTitle = ref("New Payment");
const formModalAllowNewEntry = ref(true);

const paymentRemoveEvent = ref("delete-invoice_payment");

const initPaymentModal = async (id = null) => {
    formMode.value = id ? "edit" : "create";
    let e = null;
    if (formMode.value === "edit") {
        formModalTitle.value = "Edit Payment";
        //e = await fetchEditData(id);
        //await moduleFormStore.loadEditData(id);
    } else {
        formModalTitle.value = "New Payment";
        e = _.cloneDeep(props.formEntry);
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

const onSuccess = () => {
    resetValues();
    emit("refresh");
};

const onCancel = () => {
    resetValues();
};

const resetValues = () => {
    componentInitialized.value = false;
    datatableModule.value = {};
    parentId.value = null;
    paymentRoute.value = null;
    formModalData.value.route = null;
};

onBeforeUnmount(() => {
    emitter.off(`init-payment-modal-${props.id}`);
    emitter.off(`${props.id}-update-payment`);
    emitter.off(paymentRemoveEvent.value);
});

onMounted(() => {
    emitter.on(`init-payment-modal-${props.id}`, (row) => {
        parentId.value = row.id;
        paymentRoute.value = `${props.listRoute}/${parentId.value}`;
        formModalData.value.formRoute = paymentRoute.value;
        datatableModule.value = {
            id: `${props.id}-payment-datatable`,
            slug: `${props.id}-payment-datatable`,
            name: `${props.id}-payment-datatable`,
            route: paymentRoute.value,
            query: { sort: "date", order: "desc", limit: 500, s: "" },
            restrictEdit: true,
            restrictDelete: true,
            rowActions: [
                {
                    name: "Delete",
                    icon: "feather:trash-2",
                    event: paymentRemoveEvent.value,
                    permission_prefix: `invoice_`,
                },
            ],
        };
        initPaymentModal();
    });
    emitter.on(paymentRemoveEvent.value, async (row) => {
        const id = _.get(row, "id", null);
        if (!id) return;
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete",
            confirmButtonColor: "#dd4b39",
            focusCancel: true,
            reverseButtons: true,
        }).then((r) => {
            if (!r.isConfirmed) return;
            ApiService.delete(`${props.listRoute}/${id}`)
                .then((res) => {
                    $toastSuccess("Payment was deleted successfully.");
                    /*emitter.emit("refresh-magic-table-data", {
                        id: datatableModule.value.id,
                    });*/
                    emitter.emit("hide-form-modal", formModalData.value.id);
                    onSuccess();
                })
                .catch((err) => {
                    $catchResponse(err);
                });
        });
    });
});
</script>

<style scoped></style>
