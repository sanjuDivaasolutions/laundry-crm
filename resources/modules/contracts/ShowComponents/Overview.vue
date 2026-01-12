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
  -  *  Last modified: 17/10/24, 5:40 pm
  -  *  Written by Chintan Bagdawala, 2024.
  -  */
  -->

<template>
    <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
        <div class="card-header cursor-pointer d-flex align-items-center">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Contract Overview</h3>
            </div>
            <div>
                <a
                    v-if="!isActiveSubscription && isStripeSubscription"
                    class="btn btn-danger btn-sm me-2"
                    href="#"
                    @click.prevent="handleSubscribe"
                    >Subscribe</a
                >
                <a
                    v-if="!isActiveSubscription && isStripeSubscription"
                    class="btn btn-danger btn-sm"
                    href="#"
                    @click.prevent="handleNotify"
                    >Send Payment Link</a
                >
            </div>
        </div>
        <div class="card-body p-9">
            <div class="mw-lg-950px mx-auto w-100">
                <div
                    class="d-flex justify-content-between flex-column flex-sm-row mb-5"
                ></div>
                <div class="pb-12">
                    <div class="d-flex flex-column gap-7 gap-md-10">
                        <div
                            class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold"
                        >
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Client</span>
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "buyer.display_name",
                                            "-"
                                        )
                                    }}
                                </span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Contract Date</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "date", "-")
                                }}</span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Start Date</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "start_date", "-")
                                }}</span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">End Date</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "end_date", "-")
                                }}</span>
                            </div>
                        </div>
                        <div
                            v-if="false"
                            class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold"
                        >
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted"
                                    >Subscription Status</span
                                >
                                <span class="fs-5">{{
                                    $getDisplayValue(
                                        entry,
                                        "subscription_status_label",
                                        "-"
                                    )
                                }}</span>
                            </div>
                        </div>
                    </div>
                    <div
                        class="d-flex justify-content-between flex-column mt-10"
                    >
                        <div class="table-responsive border-bottom mb-9">
                            <table
                                class="table align-middle table-row-dashed fs-6 gy-5 mb-0"
                            >
                                <thead>
                                    <tr
                                        class="border-bottom fs-6 fw-bold text-muted"
                                    >
                                        <th class="min-w-175px pb-2">
                                            Service
                                        </th>
                                        <th class="min-w-175px pb-2">
                                            Description
                                        </th>
                                        <th class="min-w-70px pb-2">Remark</th>
                                        <th class="min-w-100px text-end pb-2">
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
                                                                "product.name"
                                                            )
                                                        }}
                                                    </div>
                                                    <div
                                                        class="fs-7 text-muted"
                                                    ></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{
                                                $getDisplayValue(
                                                    i,
                                                    "description"
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ $getDisplayValue(i, "remark") }}
                                        </td>
                                        <td class="text-end">
                                            {{ $getDisplayValue(i, "amount") }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end">
                                            Subtotal
                                        </td>
                                        <td class="text-end">
                                            {{
                                                $getDisplayValue(
                                                    entry,
                                                    "sub_total_text"
                                                )
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end">
                                            Tax ({{
                                                $getDisplayValue(
                                                    entry,
                                                    "tax_rate"
                                                )
                                            }}%)
                                        </td>
                                        <td class="text-end">
                                            {{
                                                $getDisplayValue(
                                                    entry,
                                                    "tax_total_text"
                                                )
                                            }}
                                        </td>
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
                                                $getDisplayValue(
                                                    entry,
                                                    "grand_total_text"
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
        </div>
    </div>
</template>

<script setup>
import {
    $catchResponse,
    $getDisplayValue,
    $toastSuccess,
} from "@/core/helpers/utility";
import { computed } from "vue";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
    fields: {
        type: Array,
        required: true,
    },
});

const isActiveSubscription = computed(() => {
    return _.get(props, "entry.subscription_status") === "active";
});

const isStripeSubscription = computed(() => {
    return _.get(props, "entry.contract_type") === "stripe";
});

const handleSubscribe = () => {
    const url = `/subscription-checkout/${props.entry.id}`;
    window.open(url, "_blank");
};

const handleNotify = () => {
    ApiService.post(`/contracts-send-payment-link/${props.entry.id}`)
        .then((response) => {
            $toastSuccess(response.data.data);
        })
        .catch((error) => {
            $catchResponse(error);
        });
};
</script>
