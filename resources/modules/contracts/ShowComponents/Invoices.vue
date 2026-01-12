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
  -  *  Last modified: 17/10/24, 6:42 pm
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div>
        <CardContainer :hide-header="true" :hide-footer="true" class="mb-3">
            <template #body>
                <a
                    v-if="isManualSubscription"
                    class="btn btn-danger btn-sm me-2"
                    href="#"
                    @click.prevent="handleGenerateInvoice"
                    >Generate Invoice</a
                >
            </template>
        </CardContainer>
        <MagicDatatable
            :module="invoicesModule"
            table-title="Invoices"
            :disable-actions="true"
            :show-table-title="true"
        />
        <PaymentFormModal
            :id="paymentModule.id"
            :list-route="paymentModule.listRoute"
            :form-route="paymentModule.formRoute"
            :parent-field="paymentModule.parentField"
            :form-entry="paymentModule.formEntry"
            :form-fields="paymentModule.formFields"
            :list-fields="paymentModule.listFields"
            @refresh="refreshData"
        />
    </div>
</template>

<script setup>
import { computed, onBeforeMount, onBeforeUnmount, onMounted, ref } from "vue";
import emitter from "@/core/plugins/mitt";
import { useRoute } from "vue-router";
import MagicDatatable from "@/components/magic-datatable/MagicDatatable.vue";
import CardContainer from "@common@/components/CardContainer.vue";
import ApiService from "@/core/services/ApiService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import PaymentFormModal from "@common@/components/PaymentFormModal.vue";
import Swal from "sweetalert2";

const route = useRoute();

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
});

const paymentModule = ref({
    id: "invoice-payments",
    listRoute: "invoice-payments",
    formRoute: "invoice-payments",
    parentField: "invoice_id",
    formEntry: {
        id: null,
        invoice_id: null,
        date: null,
        amount: null,
        remark: null,
        payment_mode_id: null,
    },
    formFields: [
        {
            name: "date",
            label: `general.fields.date`,
            field: "date",
            placeholder: `Enter Date`,
            type: "date",
            required: true,
            column: "6",
        },
        {
            name: "amount",
            label: `general.fields.amount`,
            field: "amount",
            placeholder: `Enter Amount`,
            type: "number",
            step: "0.01",
            required: true,
            column: "6",
        },
    ],
    listFields: [
        {
            name: "date",
            title: `general.fields.date`,
            field: "date",
            type: "date",
        },
        {
            name: "amount",
            title: `general.fields.amount`,
            field: "amount_text",
            type: "number",
            align: "right",
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
});

const invoicesModule = ref({
    id: "contract-invoices",
    slug: "contract-invoices",
    name: "Contract Invoices",
    route: "contract-invoices",
    routeOriginal: "contract-invoices",
    permission: "contract",
    permission_prefix: `sales_invoice1_`,
    query: { sort: "date", order: "asc", limit: 200, s: "" },
    formType: "modal",
    formClickAction: `init-installments-form-modal`,
    tableRowClick: {
        enabled: false,
    },
    restrictEdit: true,
    restrictDelete: true,
    rowActions: [
        /*{
            name: "Payments",
            icon: "feather:dollar-sign",
            event: "manage-contract-invoice-payments",
            permission: "invoice_payment_access",
            permission_prefix: `invoice_payment_`,
        },*/
        {
            name: "Delete",
            icon: "feather:trash-2",
            event: "delete-contract-invoice",
            permission: "sales_invoice_delete",
            permission_prefix: `sales_invoice_`,
        },
    ],
});

const moduleActive = ref(false);

const isManualSubscription = computed(() => {
    return _.get(props, "entry.contract_type.value") === "default";
});

const handleGenerateInvoice = () => {
    ApiService.get(`contract-invoice-generate/${props.entry.id}`)
        .then((response) => {
            $toastSuccess("Invoice generated successfully");
            refreshData();
        })
        .catch((error) => {
            console.log("Catch Generate");
            $catchResponse(error);
        });
};

const handleRemoveInvoice = (id) => {
    ApiService.delete(`contract-invoices/${id}`)
        .then((response) => {
            $toastSuccess("Invoice deleted successfully");
            refreshData();
        })
        .catch((error) => {
            console.log("Catch Delete");
            $catchResponse(error);
        });
};

const handlePaymentModule = (payload) => {
    emitter.emit(`init-payment-modal-${paymentModule.value.id}`, payload);
};

const moduleId = computed(() => {
    return _.get(invoicesModule.value, "id", null);
});

const refreshData = () => {
    const id = moduleId.value;
    if (!id) {
        console.error("Module ID not found");
        return;
    }
    emitter.emit("refresh-magic-table-data", {
        id: `magic-table-${id}`,
    });
};

onBeforeMount(() => {
    const parentId = route.params.id;
    invoicesModule.value.route = `contract-invoices/${parentId}`;
});

onMounted(() => {
    moduleActive.value = true;
    emitter.on("delete-contract-invoice", (payload) => {
        const id = _.get(payload, "id", null);
        if (!id) {
            console.error("Invoice ID not found");
            return;
        }
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
            handleRemoveInvoice(id);
        });
    });
    emitter.on("manage-contract-invoice-payments", (payload) => {
        const row = _.get(payload, "row", null);
        if (!row) {
            console.error("Invoice row not found");
            return;
        }
        if (row.payment_status !== "paid") {
            paymentModule.value.formEntry.amount = row.grand_total;
            paymentModule.value.formEntry.date = moment().format(
                $headMeta("moment_date_format")
            );
        }
        handlePaymentModule(row);
    });
});

onBeforeUnmount(() => {
    moduleActive.value = false;
    emitter.off("delete-contract-invoice");
});
</script>

<style scoped>
.table td {
    border-bottom-width: 0;
}
</style>
