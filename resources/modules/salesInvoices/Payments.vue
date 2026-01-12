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
  -  *  Last modified: 05/02/25, 7:35 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <div>
        <CardContainer :hide-header="true" :hide-footer="true" class="mb-10">
            <template #body>
                <div class="d-flex justify-content-end">
                    <a
                        href="#"
                        class="btn btn-danger btn-sm"
                        @click.prevent="initPaymentModal(null, 'receive')"
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
            :defaults-route="formModalData.defaultsRoute"
            :createSubmitEvent="paymentTableId"
        >
            <template #modal-body>
                <slot name="form-modal-body"></slot>
            </template>
        </FormModal>
    </div>
</template>

<script setup>
import { onBeforeMount, onBeforeUnmount, onMounted, ref, watch } from "vue";
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";
import CardContainer from "@common@/components/CardContainer.vue";
import FormModal from "@common@/modals/FormModal.vue";
import emitter from "@/core/plugins/mitt";
import ApiService from "@/core/services/ApiService";
import { useRoute } from "vue-router";
import { $toastSuccess } from "@/core/helpers/utility";
import FormIcon from "@common@/components/form/FormIcon.vue";
import _ from "lodash";

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
    autoOpenPayment: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["payment-modal-opened"]);

const module = ref({
    id: "si-invoice-payment",
    slug: "payment",
    name: "Payment",
    route: "payments",
    permission: "payment",
    permission_prefix: "payment_",
    formType: "modal",
    formClickAction: "perform-item-edit",
    query: { sort: "payment_date", order: "desc", limit: 100, s: "" },
    tableRowClick: {
        enabled: false,
    },
    actions: {
        edit: true,
        delete: true,
        view: false,
    },
    restrictEdit: false,
    restrictDelete: false,
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
    defaultsRoute: null,
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
        title: `general.fields.payment_mode`,
        field: "payment_mode.name",
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
    {
        title: "Actions",
        field: "title",
        thComp: "TranslatedHeader",
        tdComp: "DatatableActions",
        isActions: true,
        sortable: false,
    },
];

// paymentEntry ref removed - using inline entry object with server-side defaults

const initPaymentModal = async (id = null, type = "receive") => {
    formMode.value = id ? "edit" : "create";
    let e = null;
    let modalType = type || "receive";

    if (formMode.value === "edit") {
        e = await fetchEditData(id);
        modalType = _.get(e, "tran_type", modalType);
        formModalTitle.value =
            modalType === "send" ? "Edit Sent Payment" : "Edit Received Payment";
    } else {
        formModalTitle.value =
            modalType === "send" ? "Send New Payment" : "Receive New Payment";

        // Use minimal entry object - let server-side defaults handle pre-population
        e = {
            id: null,
            tran_type: modalType,
            sales_order_id: null,
            sales_invoice_id: null,
            purchase_order_id: null,
            purchase_invoice_id: null,
            payment_type: props.type,
            payment_date: null,
            amount: null,
            remarks: null,
        };

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
    const response = await ApiService.get(`payments/${id}/edit`);
    return response.data.data; // Extract the actual payment data
};

// calculatePendingAmount function removed - now handled server-side

onBeforeMount(() => {
    module.value.route = `payments/${props.type}/${route.params.id}`;
    formModalData.value.route = `payments`;
    
    console.log("Payments module config:", module.value);
    console.log("Payments columns config:", columns);
    
    // Set up the defaults route for server-side pre-population
    if (props.type === 'si') {
        formModalData.value.defaultsRoute = `payments/create?sales_invoice_id=${props.id}`;
    } else if (props.type === 'pi') {
        formModalData.value.defaultsRoute = `payments/create?purchase_invoice_id=${props.id}`;
    }
});

onMounted(() => {
    emitter.on("perform-item-edit", async (payload) => {
        await initPaymentModal(payload.id, 'send');
    });
    emitter.on("perform-item-delete", async (id) => {
        await ApiService.delete(`payments/${id}`);
        $toastSuccess("Payment was deleted successfully.");
        emitter.emit("refresh-magic-table-data", {
            id: "magic-table-" + module.value.id,
        });
    });
    emitter.on("onFirInvoiceChange", (data) => {
        emitter.emit(`update-modal-entry-field-${formModalData.value.id}`, {
            field: "payment_type",
            value: data.value.type,
        });
    });
    emitter.on("magic-table-form-po-invoice-payment", (data) => {
        var obj = {
            id: "magic-table-" + module.value.id,
        };
        emitter.emit("refresh-magic-table-data", obj);
    });
    emitter.on("form-modal-refresh-magic-table-data", () => {
        emitter.emit("refresh-magic-table-data", {
            id: "magic-table-" + module.value.id,
        });
    });

    if (props.autoOpenPayment) {
        triggerAutoOpen();
    }
});

const triggerAutoOpen = () => {
    initPaymentModal(null, "receive");
    emit("payment-modal-opened");
};

watch(
    () => props.autoOpenPayment,
    (value) => {
        if (value) {
            triggerAutoOpen();
        }
    }
);

onBeforeUnmount(() => {
    emitter.off("perform-item-edit");
    emitter.off("perform-item-delete");
    emitter.off("onFirInvoiceChange");
    emitter.off("magic-table-form-po-invoice-payment");
    emitter.off("form-modal-refresh-magic-table-data");
    emitter.emit("kill-events-modal-container");
});
</script>

<style scoped>
.table th {
    font-weight: bold;
}
</style>
