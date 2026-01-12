<template>
    <div>
        <CardContainer
            :hide-header="true"
            :hide-footer="true"
            class="mb-10"
        >
            <template #body>
                <div class="d-flex justify-content-end">
                    <a
                        href="#"
                        class="btn btn-danger btn-sm"
                        @click.prevent="initPaymentModal(null, 'send')"
                        ><FormIcon icon="feather:plus" />Make Payment</a
                    >
                </div>
            </template>
        </CardContainer>
        <magic-datatable
            v-if="true"
            :module="module"
            :columns="columns"
            table-title="Payments"
        />
        <FormModal
            :id="formModalData.id"
            :title="formModalTitle"
            :fields="formFields"
            :mode="formMode"
            :size="formModalData.size"
            :route="formModalData.route"
            :createSubmitEvent="paymentTableId"
        >
            <template #modal-body>
                <slot name="form-modal-body"></slot>
            </template>
        </FormModal>
    </div>
</template>

<script setup>
import { onBeforeMount, onBeforeUnmount, onMounted, ref } from "vue";
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";
import CardContainer from "@common@/components/CardContainer.vue";
import FormModal from "@common@/modals/FormModal.vue";
import emitter from "@/core/plugins/mitt";
import ApiService from "@/core/services/ApiService";
import { useRoute } from "vue-router";
import { $toastSuccess } from "@/core/helpers/utility";

const route = useRoute();

const props = defineProps({
    id: {
        type: Number,
        required: true,
    },
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
    id: "po-invoice-payment",
    slug: "payment",
    name: "Payment",
    route: "payments",
    permission: "payment",
    permission_prefix: `payment_`,
    query: { sort: "payment_date", order: "desc", limit: 100, s: "" },
    tableRowClick: {
        enabled: false,
    },
});

const paymentTableId = ref("magic-table-form-po-invoice-payment");

const formFields = ref([
    
    
    {
        name: "payment_date",
        label: `general.fields.payment_date`,
        field: "payment_date",
        placeholder: `Enter Date`,
        type: "date",
        required: true,
        column: "12",
    },
    {
        name: "payment_mode",
        label: `general.fields.payment_mode`,
        field: "payment_mode",
        placeholder: "Select Payment Mode",
        idValue: "id",
        labelValue: "name",
        type: "select-single",
        endpoint: "payment_modes",
        mode: "single",
        hideSelected: true,
        required: true,
        column: "12",
    },
   
    {
        name: "remarks",
        label: `general.fields.remarks`,
        field: "remarks",
        placeholder: `Enter Remark`,
        type: "text",
        required: false,
        column: "12",
    },
     {
        name: "amount",
        label: `general.fields.amount`,
        field: "amount",
        placeholder: `Enter Amount`,
        type: "number",
        step: "0.01",
        required: true,
        column: "12",
    },
]);

const data = ref([]);

const formModalData = ref({
    id: "payment-form-modal",
    size: "modal-lg",
    route: null,
});
const formMode = ref("create");
const formModalTitle = ref("New Payment");

const columns = [
    {
        title: `general.fields.date`,
        field: "payment_date",
        thComp: "TranslatedHeader",
        /*tdComp: "DatatableLink",*/
        sortable: true,
    },
    
    
    {
        title: `general.fields.amount`,
        field: "amount_text",
        thComp: "TranslatedHeader",
        /*tdComp: "DatatableLink",*/
        sortable: true,
    },
    {
        title: `general.fields.remarks`,
        field: "remarks",
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

const paymentEntry = ref({
    id: null,
    tran_type: null,
    sales_order_id: null,
    sales_invoice_id: null,
    purchase_order_id: null,
    purchase_invoice_id: null,
    payment_type: props.type,
    payment_date: null,
    amount: null,
    remarks: null,
});

const initPaymentModal = async (id = null, type = null) => {
    formMode.value = id ? "edit" : "create";
    let e = null;
    if (formMode.value === "edit") {
        formModalTitle.value =
            type === "send" ? "Edit sent Payment" : "Edit received Payment";
        e = await fetchEditData(id);
        //await moduleFormStore.loadEditData(id);
    } else {
        formModalTitle.value =
            type === "send" ? "Send New Payment" : "Receive New Payment";
        e = _.cloneDeep(paymentEntry.value);
        e.tran_type = type;
        setParentIds(e);
    }
    emitter.emit("show-form-modal", {
        id: formModalData.value.id,
        entry: e,
    });
};

const setParentIds = (obj) => {
    if (props.type === "pi") {
        obj.purchase_invoice_id = props.entry.id;
        obj.purchase_order_id = props.entry.purchase_order_id;
    } else if (props.type === "si") {
        obj.sales_invoice_id = props.entry.id;
        obj.sales_order_id = props.entry.sales_order_id;
    }
};

const fetchEditData = async (id) => {
    return await ApiService.get(`payments/${id}/edit`);
};

onBeforeMount(() => {
    module.value.route = `payments/${props.type}/${route.params.id}`;
    paymentEntry.value.purchase_order_id = route.params.id;
    formModalData.value.route = `payments`;
    paymentEntry.value.payment_type = props.type;
});

onMounted(() => {
    emitter.on("perform-item-delete", async (id) => {
        await ApiService.delete(`payments/${id}`);
        $toastSuccess("Payment was deleted successfully.");
        emitter.emit("refresh-magic-table-data");
    });
    emitter.on("onFirInvoiceChange", (data) => {
        emitter.emit(`update-modal-entry-field-${formModalData.value.id}`, {
            field: "payment_type",
            value: data.value.type,
        });
    });
    emitter.on("magic-table-form-po-invoice-payment",(data)=>{
        var obj = {
            id:"magic-table-"+ module.value.id,
        }
        emitter.emit("refresh-magic-table-data",obj);
    });
});

onBeforeUnmount(() => {
    emitter.off("perform-item-delete");
    emitter.off("onFirInvoiceChange");
    emitter.emit("kill-events-modal-container");
});
</script>

<style scoped>
.table th {
    font-weight: bold;
}
</style>
