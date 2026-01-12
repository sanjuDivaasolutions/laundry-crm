<template>
    <CardContainer :hide-footer="true">
        <template #header>
            <div class="d-flex align-items-center">
               
            </div>
            <div class="d-flex align-items-center">
                <!--  <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle btn-sm me-3" type="button" id="dropdownMenuButton1"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Actions
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li>
                            <a class="dropdown-item" href="#" @click.prevent="handleConvert('so')">Convert to SO</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" @click.prevent="handleConvert('pi')">Convert to PI</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" @click.prevent="handleConvert('clone')">Clone</a>
                        </li>
                    </ul>
                </div> -->
                <a href="#" @click.prevent="downloadPdf" class="btn btn-danger btn-sm align-self-center">
                    <FormIcon icon="feather:download" /> PDF
                </a>
            </div>
        </template>
        <template #body>
            <div class="mw-lg-950px mx-auto w-100">
                <div class="d-flex justify-content-between flex-column flex-sm-row mb-5">
                    <div class="flex-root d-flex flex-column">
                        <h4 class="fw-bolder text-gray-800 fs-1qx mb-10">
                            PURCHASE INVOICE<br />
                        </h4>
                        <span class="text-muted">To</span>
                        <span class="fs-6">
                            <strong>
                                {{
                                $getDisplayValue(
                                entry,
                                "supplier.display_name",
                                "-"
                                )
                                }} </strong><br />
                            {{
                            $getDisplayValue(
                            entry,
                            "supplier.billing_address.address_1",
                            "-"
                            )
                            }}<br />
                            {{
                            $getDisplayValue(
                            entry,
                            "supplier.billing_address.address_2",
                            ""
                            )
                            }}<br />
                            {{
                                $getDisplayValue(
                                entry,
                                    "supplier.billing_address.city.name",
                                    ""
                                )
                            }}
                            {{
                            $getDisplayValue(
                            entry,
                            "supplier.billing_address.postal_code",
                            ""
                            )
                            }}
                        </span>
                    </div>


                </div>

                <div>
                    <div class="d-flex flex-column gap-7 gap-md-10">
                        <div class="separator"></div>

                        <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold">
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">PI #</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "invoice_number", "-")
                                    }}</span>
                            </div>
                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">PO #</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "purchase_order.po_number", "-")
                                    }}</span>
                            </div>

                            <div class="flex-root d-flex flex-column">
                                <span class="text-muted">Date</span>
                                <span class="fs-5">{{
                                    $getDisplayValue(entry, "date", "-")
                                    }}</span>
                            </div>

                        </div>

                        <div class="d-flex justify-content-between flex-column">
                            <div class="table-responsive border-bottom mb-9">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="border-bottom fs-6 fw-bold text-muted">
                                            <th class="min-w-175px pb-2">
                                                Products
                                            </th>


                                            <th class="min-w-80px text-end pb-2">
                                                Qty
                                            </th>
                                            <th class="min-w-80px text-end pb-2">
                                                Rate
                                            </th>
                                            <th class="min-w-100px text-end pb-2">
                                                Total
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="fw-semibold text-gray-600">
                                        <tr v-for="i in entry.items" v-if="entry.items.length">
                                            <td>
                                                <div class="d-flex align-items-center">
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
                                        <tr v-if="false">
                                            <td colspan="3" class="text-end">
                                                VAT (0%)
                                            </td>
                                            <td class="text-end">$0.00</td>
                                        </tr>
                                        <tr v-if="false">
                                            <td colspan="3" class="text-end">
                                                Shipping Rate
                                            </td>
                                            <td class="text-end">$5.00</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="fs-3 text-dark fw-bold text-end">
                                                Grand Total
                                            </td>
                                            <td class="text-dark fs-3 fw-bolder text-end">
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

                    <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold">
                        <div class="flex-root d-flex flex-column">
                            <span class="text-muted">Remarks</span>
                            <span class="fs-5">
                                {{
                                $getDisplayValue(entry, "remark", "-")
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </CardContainer>
    
</template>

<script setup>
import CardContainer from "@common@/components/CardContainer.vue";
import DownloadService from "@/core/services/DownloadService";
import {
    $getCurrencyObj,
    $getValue,
    $getDisplayValue,
    $moneyfy,
} from "@/core/helpers/utility";

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
const downloadPdf = async () => {
    return await DownloadService.handleDownload(
        `purchase-invoices-single-pdf/${entry.value.id}`
    );
};
</script>
