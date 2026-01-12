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
  -  *  Last modified: 17/10/24, 5:04 pm
  -  *  Written by Chintan Bagdawala, 2024.
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
            />
        </template>
    </card-container>
</template>

<script setup>
import FormFields from "@common@/components/FormFields.vue";
import { useModuleFormStore } from "@modules@/contracts/contractsFormStore";
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
                    $toastSuccess("Contract was created successfully");
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
                    $toastSuccess("Contract was updated successfully");
                    redirectIndex();
                })
                .catch((error) => {
                    $catchResponse(error);
                });
            break;
    }
};

const defaultRoute = computed(() => {
    if (props.mode === "edit") {
        return null;
    }
    let r = `${moduleFormStore.route}/create`;
    //const type = route.params.type;
    return r;
});

const calculateTotal = () => {
    let subTotal = 0;
    entry.value.items.forEach((item) => {
        const itemRate =
            item.amount || _.get(item, "product.price.sale_price", 0);
        const total = Number(itemRate);
        item.amount = total;
        item.description = item.description || item.product.description;
        subTotal += total;
    });
    const taxTotal = subTotal * (entry.value.revision.tax_rate / 100);

    const grandTotal = subTotal + taxTotal;

    entry.value.revision.sub_total = isNaN(subTotal) ? 0 : subTotal;
    entry.value.revision.tax_total = isNaN(taxTotal) ? 0 : taxTotal;
    entry.value.revision.grand_total = isNaN(grandTotal) ? 0 : grandTotal;
};

const redirectIndex = () => {
    router.push({
        name: `${module.value.id}.index`,
    });
};

onBeforeUnmount(() => {
    moduleFormStore.resetEntry();
    emitter.off("calculate-total");
    //emitter.off("currency-update");
    emitter.off("currency_rate-update");
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
    //emitter.on("currency-update", moduleFormStore.onCurrencyUpdate);
    emitter.on("currency_rate-update", moduleFormStore.onCurrencyRateUpdate);
});
</script>
