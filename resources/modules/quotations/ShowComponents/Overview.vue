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
  -  *  Last modified: 21/01/25, 6:21 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <CardContainer :hide-footer="true">
        <template #header>
            <div class="d-flex align-items-center">
                <RouterLink
                    other
                    title="Back to Quotations"
                    :to="{ name: 'quotations.index' }"
                    class="btn btn-sm btn-primary me-3"
                >
                    <FormIcon icon="feather:arrow-left" />
                </RouterLink>
            </div>
            <div class="d-flex align-items-center">
                <div v-if="isStatusNotConfirmed" class="dropdown">
                    <button
                        class="btn btn-secondary dropdown-toggle btn-sm me-3"
                        type="button"
                        id="dropdownMenuButton1"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        Mark as
                    </button>
                    <ul
                        class="dropdown-menu"
                        aria-labelledby="dropdownMenuButton1"
                    >
                        <li>
                            <a
                                class="dropdown-item"
                                href="#"
                                @click.prevent="handleMark('confirmed')"
                                >Confirmed</a
                            >
                        </li>
                    </ul>
                </div>
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
                            Quotation<br />
                        </h4>
                        <span class="text-muted">To</span>
                        <span class="fs-6">
                            <strong>
                                {{
                                    $getDisplayValue(entry, "buyer.name", "-")
                                }} </strong
                            ><br />
                            {{
                                $getDisplayValue(
                                    entry,
                                    "buyer.billing_address.address_1",
                                    "-"
                                )
                            }}
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
                                    "buyer.billing_address.state.name",
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
                                <span class="text-muted">Order #</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "order_no", "-")
                                }}</span>
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Date</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "date", "-")
                                }}</span>
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted"
                                    >Expected Delivery Date</span
                                >
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "expected_delivery_date",
                                            "-"
                                        )
                                    }}
                                </span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Status</span>
                                <span class="fs-5 text-danger">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "status.status_label",
                                            "-"
                                        )
                                    }}
                                    as on
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "status.date",
                                            ""
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
                                            v-for="i in items"
                                            v-if="items.length"
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
                                        <tr v-if="entry.tax_total > 0">
                                            <td colspan="3" class="text-end">
                                                TAX ({{
                                                    $getValue(
                                                        entry,
                                                        "tax_rate",
                                                        "5"
                                                    )
                                                }}%)
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    $moneyfy(
                                                        $getValue(
                                                            entry,
                                                            "tax_total"
                                                        )
                                                    )
                                                }}
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
                                                {{
                                                    $moneyfy(
                                                        $getValue(
                                                            entry,
                                                            "grand_total"
                                                        )
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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
import ItemSubValues from "@common@/components/ItemSubValues.vue";
import DownloadService from "@/core/services/DownloadService";
import { computed } from "vue";
import emitter from "@/core/plugins/mitt";
import ApiService from "@/core/services/ApiService";
import {
    $getDisplayValue,
    $getValue,
    $moneyfy,
    $toastSuccess,
} from "@/core/helpers/utility";

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
});

const items = computed(() => _.get(props.entry, "items", []));

const downloadPdf = async () => {
    const id = props.entry.id;
    return await DownloadService.handleDownload(`quotations-single-pdf/${id}`);
};

const handleMark = async (status) => {
    const id = props.entry.id;
    const payload = getStatusData(status);
    await ApiService.post(`quotations-mark-status/${id}`, {
        status: payload.status,
        remark: payload.remark,
    }).then((res) => {
        $toastSuccess(res.data);
    });
    emitter.emit("refresh-show-data");
};

const isStatusNotConfirmed = computed(() => {
    return _.get(props.entry, "status.status", "") !== "confirmed";
});

const getStatusData = (status) => {
    const data = {
        confirmed: {
            remark: "Quotation has been confirmed.",
            status: "confirmed",
        },
    };

    return _.get(data, status, {});
};
</script>
