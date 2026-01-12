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
  -  *  Last modified: 23/01/25, 4:42 pm
  -  *  Written by Chintan Bagdawala, 2025.
  -  */
  -->

<template>
    <card-container :hide-footer="true" :hide-header="true">
        <template #header>
            <div class="card-title">
                <h3 class="m-0 text-gray-800">{{ formModalTitle }}</h3>
            </div>
        </template>
        <template #body>
            <FormFields
                :fields="fields"
                :entry="entry"
                :mode="mode"
                @submit="onSubmit"
                @cancel="redirectIndex"
                :show-cancel="true"
                :defaults-route="defaultRoute"
                defaults-route-after-event="calculate-total"
            />
        </template>
    </card-container>
</template>

<script setup>
import FormFields from "@common@/components/FormFields.vue";
import { useModuleFormStore } from "@modules@/packages/packagesFormStore";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import ApiService from "@/core/services/ApiService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import router from "@/router";
import { useRoute } from "vue-router";
import emitter from "@/core/plugins/mitt";

const props = defineProps({
    mode: {
        type: String,
        default: null,
    },
});

const route = useRoute();

const moduleFormStore = useModuleFormStore();
const module = computed(() => moduleFormStore.module);
const fields = computed(() => moduleFormStore.formFields);
const modals = computed(() => moduleFormStore.modals);
const entry = computed(() => moduleFormStore.entry);
const formModalTitle = ref("Invoice");

const onSubmit = (formEntry) => {
    switch (props.mode) {
        case "create":
            ApiService.post(moduleFormStore.route, formEntry)
                .then((response) => {
                    $toastSuccess("Package created successfully");
                    redirectIndex();
                })
                .catch((error) => {
                    $catchResponse(error);
                });
            break;
        case "edit":
            ApiService.put(
                `${moduleFormStore.route}/${formEntry.id}`,
                formEntry
            )
                .then((response) => {
                    $toastSuccess("Package updated successfully");
                    redirectIndex();
                })
                .catch((error) => {
                    $catchResponse(error);
                });
            break;
    }
};

const calculateTotal = () => {
    let subTotal = 0;
    let taxRate = 20;
    entry.value.items.forEach((item) => {
        if (!item.quantity || !item.rate) return;
        const total = item.quantity * item.rate;
        item.amount = total;
        subTotal += total;
    });
    var taxTotal =
        subTotal -
        (Number(subTotal) * Number(100)) / (Number(100) + Number(taxRate));
    subTotal = Number(subTotal) - Number(taxTotal.toFixed(2));
    var grandTotal = Number(subTotal) + Number(taxTotal);

    entry.value.tax_total = Number(taxTotal);
    entry.value.sub_total = subTotal;
    entry.value.grand_total = grandTotal;
};

const onUpdateProducts = (payload) => {
    var productId = payload.value.id;
    var item = entry.value.items.find((i) => i.product.id === productId);
    if (item) {
        item.unit = payload.value.unit;
        item.rate = payload.value.rate;
        calculateTotal();
    }
};

const redirectIndex = () => {
    router.push({
        name: `${module.value.id}.index`,
    });
};

const defaultRoute = computed(() => {
    return `${moduleFormStore.route}/create`;
});

onBeforeUnmount(() => {
    moduleFormStore.resetEntry();
    emitter.off("calculate-total");
    emitter.off("on-update-products");
    //emitter.off("currency-update");
    //emitter.off("currency_rate-update");
    emitter.off("onSalesOrderChange");
});

onMounted(() => {
    if (props.mode === "edit") {
        const route = useRoute();
        const id = route.params.id;
        formModalTitle.value = modals.value.form.editTitle;
        moduleFormStore.loadEditData(id);
    } else {
        formModalTitle.value = modals.value.form.createTitle;
        moduleFormStore.resetEntry();
    }
    emitter.on("calculate-total", calculateTotal);
    //emitter.on("on-update-products", onUpdateProducts);
    emitter.on("onSalesInvoiceChange", moduleFormStore.onSalesInvoiceChange);
    //emitter.on("currency_rate-update", moduleFormStore.onCurrencyRateUpdate);
});
</script>
