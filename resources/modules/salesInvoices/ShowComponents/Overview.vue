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
  -  *  Last modified: 22/01/25, 10:54 am
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <CardContainer :hide-footer="true">
        <template #header>
            <div class="d-flex align-items-center"></div>
            <div class="d-flex align-items-center">
                <a
                    href="#"
                    @click.prevent="downloadPdf"
                    class="btn btn-danger btn-sm align-self-center"
                >
                    <FormIcon icon="feather:download" /> PDF
                </a>
            </div>
        </template>
        <template #body>
            <div class="mw-lg-950px mx-auto w-100">
                <div
                    class="d-flex justify-content-between flex-column flex-sm-row mb-5"
                >
                    <div class="flex-root d-flex flex-column">
                        <h4 class="fw-bolder text-gray-800 fs-1qx mb-10">
                            SALES INVOICES<br />
                        </h4>
                        <span class="text-muted">To</span>
                        <span class="fs-6">
                            <strong>
                                {{
                                    $getDisplayValue(
                                        entry,
                                        "buyer.display_name",
                                        "-"
                                    )
                                }} </strong
                            ><br />
                            {{
                                $getDisplayValue(
                                    entry,
                                    "buyer.billing_address.address_1",
                                    "-"
                                )
                            }}<br />
                            {{
                                $getDisplayValue(
                                    entry,
                                    "buyer.billing_address.address_2",
                                    ""
                                )
                            }}<br />
                            {{
                                $getDisplayValue(
                                    entry,
                                    "buyer.billing_address.city.name",
                                    ""
                                )
                            }}
                            {{
                                $getDisplayValue(
                                    entry,
                                    "buyer.billing_address.postal_code",
                                    ""
                                )
                            }}
                        </span>
                    </div>
                </div>

                <div>
                    <div class="d-flex flex-column gap-7 gap-md-10">
                        <div class="separator"></div>

                        <div
                            class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold"
                        >
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">SO #</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(
                                        entry,
                                        "invoice_number",
                                        "-"
                                    )
                                }}</span>
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">PO #</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "reference_no", "-")
                                }}</span>
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Date</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "date", "-")
                                }}</span>
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Payment Term</span>
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "payment_term.name",
                                            "-"
                                        )
                                    }}
                                </span>
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Payment Status</span>
                                <div
                                    class="fs-5"
                                    v-html="
                                        $getDisplayValue(
                                            entry,
                                            'payment_status_badge',
                                            '-'
                                        )
                                    "
                                ></div>
                            </div>

                            <div
                                class="flex-root d-flex flex-column"
                                v-if="
                                    $getDisplayValue(
                                        entry,
                                        'pending_amount',
                                        0
                                    ) > 0
                                "
                            >
                                <span class="text-muted">Pending Amount</span>
                                <span class="fs-5 text-danger fw-bold">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "pending_amount",
                                            0
                                        )
                                    }}
                                </span>
                            </div>

                            <div
                                class="flex-root d-flex flex-column"
                                v-if="
                                    $getDisplayValue(
                                        entry,
                                        'buyer.agent_name',
                                        null
                                    )
                                "
                            >
                                <span class="text-muted">Agent</span>
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "buyer.agent_name",
                                            "-"
                                        )
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between flex-column">
                            <div class="table-responsive border-bottom mb-9">
                                <table
                                    class="table align-middle table-row-dashed fs-6 gy-5 mb-0"
                                >
                                    <thead>
                                        <tr
                                            class="border-bottom fs-6 fw-bold text-muted"
                                        >
                                            <th class="min-w-175px pb-2">
                                                Products
                                            </th>

                                            <th
                                                class="min-w-80px text-end pb-2"
                                            >
                                                Qty
                                            </th>
                                            <th
                                                class="min-w-80px text-end pb-2"
                                            >
                                                Rate
                                            </th>
                                            <th
                                                class="min-w-100px text-end pb-2"
                                            >
                                                Total
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="fw-semibold text-gray-600">
                                        <tr
                                            v-for="i in entry.items"
                                            v-if="entry.items.length"
                                        >
                                            <td>
                                                <div
                                                    class="d-flex align-items-center"
                                                >
                                                    <div class="ms-0">
                                                        <div class="fw-bold">
                                                            {{
                                                                $getDisplayValue(
                                                                    i,
                                                                    "product.name",
                                                                    "-"
                                                                )
                                                            }}
                                                        </div>
                                                        <div
                                                            class="fs-7 text-muted"
                                                        >
                                                            <ItemSubValues
                                                                :item="i"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="text-end">
                                                {{
                                                    $getDisplayValue(
                                                        i,
                                                        "quantity",
                                                        "-"
                                                    )
                                                }}
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    $moneyfy(
                                                        $getValue(i, "rate")
                                                    )
                                                }}
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    $moneyfy(
                                                        $getValue(i, "amount")
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end">
                                                Subtotal
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    $moneyfy(
                                                        $getValue(
                                                            entry,
                                                            "sub_total"
                                                        )
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr
                                            v-if="
                                                $getValue(
                                                    entry,
                                                    'commission_total'
                                                ) > 0
                                            "
                                        >
                                            <td colspan="3" class="text-end">
                                                Commission ({{
                                                    $getValue(
                                                        entry,
                                                        "commission"
                                                    )
                                                }}%)
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    $moneyfy(
                                                        $getValue(
                                                            entry,
                                                            "commission_total"
                                                        )
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr v-for="tax in taxes" :key="tax.id">
                                            <td colspan="3" class="text-end">
                                                {{
                                                    $getValue(
                                                        tax,
                                                        "tax_rate.name"
                                                    )
                                                }}
                                                ({{
                                                    $getValue(
                                                        tax,
                                                        "tax_rate.rate"
                                                    )
                                                }}%)
                                            </td>
                                            <td class="text-end">
                                                {{ $moneyfy(tax.amount) }}
                                            </td>
                                        </tr>
                                        <tr v-if="false">
                                            <td colspan="3" class="text-end">
                                                Shipping Rate
                                            </td>
                                            <td class="text-end">$5.00</td>
                                        </tr>
                                        <tr>
                                            <td
                                                colspan="3"
                                                class="fs-3 text-dark fw-bold text-end"
                                            >
                                                Grand Total
                                            </td>
                                            <td
                                                class="text-dark fs-3 fw-bolder text-end"
                                            >
                                                {{ $moneyfy(grandTotal) }}
                                            </td>
                                        </tr>
                                        <tr v-if="hasPayments">
                                            <td
                                                colspan="3"
                                                class="text-end text-muted"
                                            >
                                                Less: Payments Received
                                            </td>
                                            <td class="text-end text-muted">
                                                {{ $moneyfy(paymentsReceived) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end">
                                                Current Invoice Balance
                                            </td>
                                            <td class="text-end">
                                                {{ $moneyfy(pendingAmount) }}
                                            </td>
                                        </tr>
                                        <tr v-if="carryForwardTotal">
                                            <td
                                                colspan="3"
                                                class="text-end text-muted"
                                            >
                                                Previous Balance
                                            </td>
                                            <td class="text-end text-muted">
                                                {{ $moneyfy(carryForwardTotal) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                colspan="3"
                                                class="fs-3 text-dark fw-bold text-end"
                                            >
                                                Total Payment Due
                                            </td>
                                            <td
                                                class="text-dark fs-3 fw-bolder text-end"
                                            >
                                                {{ $moneyfy(totalPaymentDue) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                </div>
            </div>
                <div
                    v-if="carryForwardInvoices.length"
                    class="mt-8"
                >
                    <h6 class="fw-bold text-gray-800 mb-3">
                        Previous Outstanding Invoices
                    </h6>
                    <div class="border border-dashed rounded px-4 py-3">
                        <div
                            v-for="invoice in carryForwardInvoices"
                            :key="invoice.id || invoice.invoice_number"
                            class="fs-7 text-dark mb-2"
                        >
                            {{ invoice.date }} - {{ invoice.invoice_number }} - {{
                                $moneyfy(invoice.pending_amount)
                            }} - {{ invoice.status_label }}
                        </div>
                    </div>
                </div>
                <div class="pb-12">
                    <div
                        class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold"
                    >
                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Remarks</span>
                            <span class="fs-5">
                                {{ $getDisplayValue(entry, "remark", "-") }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </CardContainer>
</template>

<script setup>
import { $getDisplayValue, $getValue, $moneyfy } from "@/core/helpers/utility";
import ItemSubValues from "@common@/components/ItemSubValues.vue";
import DownloadService from "@/core/services/DownloadService";
import { computed } from "vue";

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
});

const taxes = computed(() => _.get(props.entry, "taxes", []));
const carryForwardInvoices = computed(() =>
    _.get(props.entry, "carry_forward_invoices", [])
);
const carryForwardTotal = computed(() => {
    const value = Number(_.get(props.entry, "carry_forward_total", 0));
    return Number.isFinite(value) ? value : 0;
});
const pendingAmount = computed(() => {
    const value = Number(_.get(props.entry, "pending_amount", 0));
    return Number.isFinite(value) ? value : 0;
});
const grandTotal = computed(() => {
    const value = Number(_.get(props.entry, "grand_total", 0));
    return Number.isFinite(value) ? value : 0;
});
const paymentsReceived = computed(() => {
    const received = grandTotal.value - pendingAmount.value;
    return received > 0 ? received : 0;
});
const hasPayments = computed(() => paymentsReceived.value > 0);
const totalPaymentDue = computed(() => {
    const backendTotal = _.get(props.entry, "total_payment_due", null);
    const numericBackend = Number(backendTotal);
    if (backendTotal !== null && !Number.isNaN(numericBackend)) {
        return numericBackend;
    }
    return pendingAmount.value + carryForwardTotal.value;
});

const downloadPdf = async () => {
    const id = props.entry.id;
    return await DownloadService.handleDownload(
        `sales-invoices-single-pdf/${id}`
    );
};
</script>
