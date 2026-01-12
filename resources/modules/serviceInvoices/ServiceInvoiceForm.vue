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
  -  *  Last modified: 19/11/24, 6:30 pm
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
import { useModuleFormStore } from "@modules@/serviceInvoices/serviceInvoicesFormStore";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import ApiService from "@/core/services/ApiService";
import { $catchResponse, $toastSuccess } from "@/core/helpers/utility";
import router from "@/router";
import { useRoute } from "vue-router";
import emitter from "@/core/plugins/mitt";
import { useTaxStore } from "@common@/components/taxStore";
import { useTotals } from "@common@/composables/useTotals";

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

const taxStore = useTaxStore();

const { calculateTotal } = useTotals({
    entry,
    taxStore,
});

const onSubmit = (formEntry) => {
    switch (props.mode) {
        case "create":
            ApiService.post(moduleFormStore.route, formEntry)
                .then((response) => {
                    $toastSuccess("Service invoice created successfully");
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
                    $toastSuccess("Service invoice updated successfully");
                    redirectIndex();
                })
                .catch((error) => {
                    $catchResponse(error);
                });
            break;
    }
};

const redirectIndex = () => {
    router.push({
        name: `${module.value.id}.index`,
    });
};

const defaultRoute = computed(() => {
    let r = `${moduleFormStore.route}/create`;
    const type = route.params.type;
    if (type === "po") {
        r += `?poId=${route.params.id}`;
    }
    if (type === "clone") {
        r += `?cloneId=${route.params.id}`;
    }
    return r;
});

onBeforeUnmount(() => {
    moduleFormStore.resetEntry();
    emitter.off("calculate-total");
    emitter.off("onSalesOrderChange");
    //emitter.off("currency-update");
    // emitter.off("currency_rate-update");
    emitter.off("onStateChange");
    emitter.off("onTaxableChange");
});

onMounted(async () => {
    await taxStore.setupTaxes();
    if (props.mode === "edit") {
        const id = route.params.id;
        formModalTitle.value = modals.value.form.editTitle;
        await moduleFormStore.loadEditData(id);
        taxStore.setTaxable(entry.value.is_taxable);
        calculateTotal();
    } else {
        formModalTitle.value = modals.value.form.createTitle;
        moduleFormStore.resetEntry();
        taxStore.setTaxable(entry.value.is_taxable);
    }
    emitter.on("onTaxableChange", (data) => {
        taxStore.setTaxable(data.value);
        calculateTotal();
    });
    emitter.on("calculate-total", calculateTotal);
    emitter.on("onSalesOrderChange", moduleFormStore.onSalesOrderChange);
    emitter.on("onStateChange", calculateTotal);
    //emitter.on("currency-update", moduleFormStore.onCurrencyUpdate);
    //emitter.on("currency_rate-update", moduleFormStore.onCurrencyRateUpdate);
});
</script>
