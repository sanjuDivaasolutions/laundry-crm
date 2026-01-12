<template>
    <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
        <div class="card-header cursor-pointer">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">Basic</h3>
            </div>
        </div>

        <div class="card-body p-9">
            <div class="mw-lg-950px mx-auto w-100">
                <div
                    class="d-flex justify-content-between flex-column flex-sm-row mb-5"
                >
                    <div class="flex-root d-flex flex-column">
                        <h4 class="fw-bolder text-gray-800 fs-1qx mb-10">
                            SALES ORDER<br />
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

                    <div class="text-sm-end">
                        <div class="text-sm-end fw-semibold fs-4 text-muted">
                            <h4 class="fw-bolder text-gray-800 fs-1qx mb-0">
                                {{
                                    $getDisplayValue(entry, "company.name", "-")
                                }}
                            </h4>
                            <div>
                                {{
                                    $getDisplayValue(
                                        entry,
                                        "company.address_1",
                                        "-"
                                    )
                                }}<br />
                                {{
                                    $getDisplayValue(
                                        entry,
                                        "company.address_2",
                                        ""
                                    )
                                }}
                            </div>
                            <div>
                                {{
                                    $getDisplayValue(
                                        entry,
                                        "company.city.name",
                                        ""
                                    )
                                }}
                                {{
                                    $getDisplayValue(
                                        entry,
                                        "company.postal_code",
                                        ""
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pb-12">
                    <div class="d-flex flex-column gap-7 gap-md-10">
                        <div class="separator"></div>

                        <div
                            class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold"
                        >
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">SO #</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "so_number", "-")
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
                                    >Estimated Shipment</span
                                >
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "shipment_date",
                                            "-"
                                        )
                                    }}</span
                                >
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Price Term</span>
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "price_term.name",
                                            "-"
                                        )
                                    }}
                                </span>
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
                                <span class="text-muted">Shipment Mode</span>
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "shipment_mode.name",
                                            "-"
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                        <div
                            class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold"
                        >
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Currency & Rate</span>
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "currency.code",
                                            "-"
                                        )
                                    }}
                                    {{
                                        entry.currency &&
                                        entry.currency.code !== "USD"
                                            ? `(${entry.currency_rate})`
                                            : ""
                                    }}</span
                                >
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Merchandiser</span>
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "merchandiser.name",
                                            "-"
                                        )
                                    }}
                                </span>
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Alias</span>
                                <span class="fs-5">
                                    {{ $getDisplayValue(entry, "alias", "-") }}
                                </span>
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Sub Client</span>
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "sub_client",
                                            "-"
                                        )
                                    }}
                                </span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted"
                                    >Variation Allowed</span
                                >
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "variation_type.name",
                                            "-"
                                        )
                                    }}
                                </span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Sample Required</span>
                                <span class="fs-5">
                                    {{
                                        $getDisplayValue(
                                            entry,
                                            "shipping_sample_required_label",
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
                                                class="min-w-70px text-end pb-2"
                                            >
                                                Optus Code
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
                                                        "item_code",
                                                        "-"
                                                    )
                                                }}
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
                                                        $getValue(i, "rate"),
                                                        $getCurrencyObj(entry)
                                                    )
                                                }}
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    $moneyfy(
                                                        $getValue(i, "amount"),
                                                        $getCurrencyObj(entry)
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end">
                                                Subtotal
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    $moneyfy(
                                                        $getValue(
                                                            entry,
                                                            "sub_total"
                                                        ),
                                                        $getCurrencyObj(entry)
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr v-if="false">
                                            <td colspan="4" class="text-end">
                                                VAT (0%)
                                            </td>
                                            <td class="text-end">$0.00</td>
                                        </tr>
                                        <tr v-if="false">
                                            <td colspan="4" class="text-end">
                                                Shipping Rate
                                            </td>
                                            <td class="text-end">$5.00</td>
                                        </tr>
                                        <tr>
                                            <td
                                                colspan="4"
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
                                                        ),
                                                        $getCurrencyObj(entry)
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
                        class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold mb-10"
                    >
                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Port of Origin</span>
                            <span class="fs-5">
                                {{
                                    $getDisplayValue(
                                        entry,
                                        "origin_port.name",
                                        "-"
                                    )
                                }}
                            </span>
                        </div>
                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Port of Destination</span>
                            <span class="fs-5">
                                {{
                                    $getDisplayValue(
                                        entry,
                                        "destination_port.name",
                                        "-"
                                    )
                                }}</span
                            >
                        </div>
                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Packing Terms</span>
                            <span class="fs-5">
                                {{
                                    $getDisplayValue(
                                        entry,
                                        "packaging_term.name",
                                        "-"
                                    )
                                }}
                            </span>
                        </div>
                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Other Terms</span>
                            <span class="fs-5">
                                {{
                                    $getDisplayValue(entry, "other_terms", "-")
                                }}
                            </span>
                        </div>
                    </div>
                    <div
                        class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold"
                    >
                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Shipping Marks</span>
                            <span class="fs-5">
                                {{
                                    $getArrayDisplayValue(
                                        entry,
                                        "shipping_mark",
                                        "name",
                                        "-"
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    $getCurrencyObj,
    $getValue,
    $getDisplayValue,
    $getArrayDisplayValue,
    $moneyfy,
} from "@/core/helpers/utility";
import ItemSubValues from "@common@/components/ItemSubValues.vue";

const props = defineProps({
    entry: {
        type: Object,
        required: true,
    },
});
</script>
